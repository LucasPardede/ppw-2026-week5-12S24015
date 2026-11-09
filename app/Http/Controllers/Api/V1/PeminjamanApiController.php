<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\ApiProblemException;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\PeminjamanResource;
use App\Models\Buku;
use App\Models\Peminjaman;
use App\Models\RiwayatTransaksi;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PeminjamanApiController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/v1/peminjamans (Wajib auth:sanctum + api.admin)
     * Mengambil daftar transaksi peminjaman terpaginasi bebas N+1.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Peminjaman::query()
            ->with(['buku.kategori', 'user']);

        // Filter status: hanya menerima 'dipinjam' atau 'dikembalikan'
        if ($request->has('status') && $request->query('status') !== null && $request->query('status') !== '') {
            $status = $request->query('status');
            if (!in_array($status, [Peminjaman::STATUS_DIPINJAM, Peminjaman::STATUS_DIKEMBALIKAN], true)) {
                return $this->problemResponse(
                    title: 'Unprocessable Content',
                    detail: "Nilai parameter filter status tidak valid. Gunakan 'dipinjam' atau 'dikembalikan'.",
                    status: 422,
                    errorSlug: 'invalid-status-filter'
                );
            }
            $query->where('status', $status);
        }

        // Filter NIM mahasiswa
        if ($nim = $request->query('nim')) {
            $nim = trim($nim);
            $query->where(function ($q) use ($nim) {
                $q->where('nim_peminjam', $nim)
                    ->orWhereHas('user', fn ($uq) => $uq->where('nim', $nim));
            });
        }

        $perPage = min(max((int) $request->query('per_page', 10), 1), 50);

        $paginator = $query->latest('id')
            ->paginate($perPage)
            ->withQueryString();

        return $this->successResponse(
            data: PeminjamanResource::collection($paginator->items()),
            message: 'Daftar transaksi peminjaman berhasil diambil.',
            meta: [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total_items' => $paginator->total(),
                'total_pages' => $paginator->lastPage(),
                'links' => [
                    'first' => $paginator->url(1),
                    'prev' => $paginator->previousPageUrl(),
                    'next' => $paginator->nextPageUrl(),
                    'last' => $paginator->url($paginator->lastPage()),
                ],
            ]
        );
    }

    /**
     * GET /api/v1/peminjamans/{id} (Wajib auth:sanctum + api.admin)
     * Menampilkan detail satu peminjaman (validasi target Location header).
     */
    public function show(string $id): JsonResponse
    {
        $peminjaman = Peminjaman::with(['buku.kategori', 'user'])->find($id);

        if (!$peminjaman) {
            return $this->problemResponse(
                title: 'Resource Not Found',
                detail: "Data peminjaman dengan ID '{$id}' tidak ditemukan.",
                status: 404,
                errorSlug: 'loan-not-found'
            );
        }

        return $this->successResponse(
            data: new PeminjamanResource($peminjaman),
            message: 'Detail data peminjaman berhasil ditemukan.'
        );
    }

    /**
     * POST /api/v1/peminjamans (Wajib auth:sanctum + api.admin)
     * Mencatat peminjaman baru bergaransi ACID + pessimistic locking (lockForUpdate).
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'buku_id' => ['required', 'integer', 'exists:bukus,id'],
            'nim_peminjam' => ['required', 'string', 'regex:/^[0-9]{2}[A-Za-z]{1,3}[0-9]{3,5}$/'],
            'nama_peminjam' => ['required', 'string', 'max:150'],
            'durasi_hari' => ['required', 'integer', 'between:1,30'],
        ], [
            'buku_id.required' => 'Buku yang dipinjam wajib dipilih.',
            'buku_id.exists' => 'Buku yang dipilih tidak ditemukan.',
            'nim_peminjam.required' => 'NIM peminjam wajib diisi.',
            'nim_peminjam.regex' => 'Format NIM peminjam tidak valid.',
            'nama_peminjam.required' => 'Nama peminjam wajib diisi.',
            'durasi_hari.required' => 'Durasi hari pinjam wajib ditentukan.',
            'durasi_hari.between' => 'Durasi pinjam harus antara 1 hingga 30 hari.',
        ]);

        $nim = strtoupper(trim($validated['nim_peminjam']));

        $peminjaman = DB::transaction(function () use ($validated, $nim, $request) {
            // 1. Kunci baris buku secara eksklusif (SELECT ... FOR UPDATE)
            $buku = Buku::where('id', $validated['buku_id'])->lockForUpdate()->first();

            if (!$buku) {
                throw new ApiProblemException(
                    status: 404,
                    title: 'Resource Not Found',
                    detail: 'Buku yang diminta tidak ditemukan.',
                    slug: 'book-not-found'
                );
            }

            // 2. Cek ketersediaan stok buku -> 422 jika habis
            if ($buku->stok < 1) {
                throw new ApiProblemException(
                    status: 422,
                    title: 'Unprocessable Content',
                    detail: 'Stok buku sedang habis.',
                    slug: 'book-stock-exhausted'
                );
            }

            // 3. Cek apakah mahasiswa sedang meminjam buku yang sama -> 409 Conflict
            $alreadyBorrowing = Peminjaman::where('buku_id', $buku->id)
                ->where('status', Peminjaman::STATUS_DIPINJAM)
                ->where(function ($q) use ($nim) {
                    $q->where('nim_peminjam', $nim)
                        ->orWhereHas('user', fn ($uq) => $uq->where('nim', $nim));
                })
                ->exists();

            if ($alreadyBorrowing) {
                throw new ApiProblemException(
                    status: 409,
                    title: 'Duplicate Loan Conflict',
                    detail: "Mahasiswa dengan NIM '{$nim}' sedang meminjam buku yang sama dan belum mengembalikannya.",
                    slug: 'duplicate-active-loan'
                );
            }

            // 4. Cari user di basis data untuk mengecek aturan bisnis lanjutan
            $user = User::where('nim', $nim)->first();

            // Cek batas maksimal pinjaman aktif (default: 3 buku)
            $activeCount = $user
                ? $user->peminjamans()->where('status', Peminjaman::STATUS_DIPINJAM)->count()
                : Peminjaman::where('nim_peminjam', $nim)->where('status', Peminjaman::STATUS_DIPINJAM)->count();

            $maxPinjam = (int) config('perpustakaan.maksimal_pinjam_buku', 3);
            if ($activeCount >= $maxPinjam) {
                throw new ApiProblemException(
                    status: 422,
                    title: 'Max Loan Limit Reached',
                    detail: "Batas maksimal peminjaman aktif ({$maxPinjam} buku) telah tercapai untuk mahasiswa ini.",
                    slug: 'max-loan-limit-reached'
                );
            }

            // Cek tanggungan pinjaman terlambat
            $today = now()->startOfDay()->toDateString();
            $hasOverdue = false;
            if ($user) {
                $hasOverdue = $user->memilikiPinjamanTerlambat();
            } else {
                $hasOverdue = Peminjaman::where('nim_peminjam', $nim)
                    ->where('status', Peminjaman::STATUS_DIPINJAM)
                    ->where('tanggal_jatuh_tempo', '<', $today)
                    ->exists();
            }

            if ($hasOverdue) {
                throw new ApiProblemException(
                    status: 409,
                    title: 'Conflict — Overdue Loan Exists',
                    detail: 'Mahasiswa masih memiliki tanggungan buku pinjaman yang telah melewati batas jatuh tempo (terlambat).',
                    slug: 'overdue-loan-conflict'
                );
            }

            // Cek tanggungan denda belum lunas
            if ($user && $user->totalDendaBelumLunas() > 0) {
                $dendaFmt = number_format($user->totalDendaBelumLunas(), 0, ',', '.');
                throw new ApiProblemException(
                    status: 409,
                    title: 'Conflict — Unpaid Fine Exists',
                    detail: "Mahasiswa masih memiliki tanggungan denda yang belum dilunasi sebesar Rp {$dendaFmt}.",
                    slug: 'unpaid-fine-conflict'
                );
            }

            // Keputusan Desain #4: Jika user belum terdaftar, buat HANYA setelah seluruh pengecekan lolos
            if (!$user) {
                $email = strtolower($nim) . '@students.del.ac.id';
                $user = User::create([
                    'name' => $validated['nama_peminjam'],
                    'email' => $email,
                    'nim' => $nim,
                    'role' => 'mahasiswa',
                    'password' => null,
                ]);
            }

            // Kurangi stok buku secara atomik
            $buku->decrement('stok');

            $tanggalPinjam = now()->startOfDay();
            $durasi = (int) $validated['durasi_hari'];
            $jatuhTempo = $tanggalPinjam->copy()->addDays($durasi);

            $peminjamanBaru = Peminjaman::create([
                'user_id' => $user->id,
                'buku_id' => $buku->id,
                'nim_peminjam' => $nim,
                'nama_peminjam' => $user->name, // Jangan menimpa nama user yang sudah ada
                'tanggal_pinjam' => $tanggalPinjam->toDateString(),
                'tanggal_jatuh_tempo' => $jatuhTempo->toDateString(),
                'tanggal_kembali' => null,
                'status' => Peminjaman::STATUS_DIPINJAM,
                'perpanjangan' => 0,
                'denda' => 0,
                'denda_lunas' => false,
            ]);

            // Audit log transaksi jika tersedia
            if (class_exists(RiwayatTransaksi::class)) {
                RiwayatTransaksi::create([
                    'peminjaman_id' => $peminjamanBaru->id,
                    'aktor_user_id' => $request->user()->id,
                    'aksi' => 'pinjam',
                    'keterangan' => "Peminjaman buku '{$buku->judul}' via REST API oleh {$user->name} ({$nim}).",
                ]);
            }

            return $peminjamanBaru;
        });

        $peminjaman->load(['buku.kategori', 'user']);

        return $this->createdResponse(
            data: new PeminjamanResource($peminjaman),
            locationUrl: url("/api/v1/peminjamans/{$peminjaman->id}"),
            message: "Peminjaman buku '{$peminjaman->buku->judul}' berhasil dicatat. Jatuh tempo: {$peminjaman->tanggal_jatuh_tempo->format('d M Y')}."
        );
    }

    /**
     * PATCH /api/v1/peminjamans/{id}/kembalikan (Wajib auth:sanctum + api.admin)
     * Mengembalikan buku pinjaman dengan transaksi ACID + lockForUpdate konsisten.
     */
    public function kembalikan(Request $request, string $id): JsonResponse
    {
        $peminjaman = DB::transaction(function () use ($id, $request) {
            // 1. Kunci baris peminjaman terlebih dahulu
            $loan = Peminjaman::where('id', $id)->lockForUpdate()->first();

            if (!$loan) {
                throw new ApiProblemException(
                    status: 404,
                    title: 'Resource Not Found',
                    detail: "Data peminjaman dengan ID '{$id}' tidak ditemukan.",
                    slug: 'loan-not-found'
                );
            }

            // 2. Kunci baris buku yang bersangkutan (urutan konsisten: peminjaman -> buku)
            $buku = Buku::where('id', $loan->buku_id)->lockForUpdate()->first();

            // Cek apakah sudah berstatus dikembalikan -> 409 Conflict
            if ($loan->status === Peminjaman::STATUS_DIKEMBALIKAN) {
                throw new ApiProblemException(
                    status: 409,
                    title: 'Conflict — Already Returned',
                    detail: 'Peminjaman ini sudah tercatat dikembalikan sebelumnya.',
                    slug: 'already-returned'
                );
            }

            // Hitung denda keterlambatan berjalan
            $denda = $loan->hitungDendaBerjalan();
            $tanggalKembali = now()->startOfDay();

            $loan->update([
                'status' => Peminjaman::STATUS_DIKEMBALIKAN,
                'tanggal_kembali' => $tanggalKembali->toDateString(),
                'denda' => $denda,
                'denda_lunas' => ($denda === 0),
            ]);

            // Tambah stok buku (+1)
            if ($buku) {
                $buku->increment('stok');
            }

            // Audit log transaksi
            if (class_exists(RiwayatTransaksi::class)) {
                RiwayatTransaksi::create([
                    'peminjaman_id' => $loan->id,
                    'aktor_user_id' => $request->user()->id,
                    'aksi' => 'kembali',
                    'keterangan' => "Pengembalian buku '{$buku?->judul}' via REST API. Denda: Rp " . number_format($denda, 0, ',', '.') . '.',
                ]);
            }

            return $loan;
        });

        $peminjaman->load(['buku.kategori', 'user']);

        $pesan = "Pengembalian buku '{$peminjaman->buku->judul}' berhasil diproses.";
        if ($peminjaman->denda > 0) {
            $pesan .= ' Denda keterlambatan: Rp ' . number_format($peminjaman->denda, 0, ',', '.') . '.';
        }

        return $this->successResponse(
            data: new PeminjamanResource($peminjaman),
            message: $pesan
        );
    }
}

