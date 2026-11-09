<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\BukuResource;
use App\Models\Buku;
use App\Models\Peminjaman;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BukuApiController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/v1/bukus (Publik)
     * Mengambil koleksi buku terpaginasi lengkap dengan filter, search, sort, dan HATEOAS.
     * Bebas dari N+1 query problem via eager loading with('kategori') + withCount('peminjamans').
     */
    public function index(Request $request): JsonResponse
    {
        $allowedSorts = ['judul', 'penulis', 'tahun_terbit', 'stok', 'created_at'];
        $sortBy = in_array($request->query('sort'), $allowedSorts, true) ? $request->query('sort') : 'judul';
        $sortDir = strtolower($request->query('dir', 'asc')) === 'desc' ? 'desc' : 'asc';
        $perPage = min(max((int) $request->query('per_page', 10), 1), 50);

        $query = Buku::query()
            ->with('kategori')
            ->withCount('peminjamans');

        // Filter pencarian teks
        if ($search = $request->query('search')) {
            $search = trim($search);
            $query->where(function ($sub) use ($search) {
                $sub->where('judul', 'like', "%{$search}%")
                    ->orWhere('penulis', 'like', "%{$search}%")
                    ->orWhere('isbn', 'like', "%{$search}%");
            });
        }

        // Filter kategori_id
        if ($kategoriId = $request->query('kategori_id')) {
            $query->where('kategori_id', $kategoriId);
        }

        // Filter ketersediaan (tersedia=1 => stok > 0)
        if ($request->boolean('tersedia')) {
            $query->where('stok', '>', 0);
        }

        $paginator = $query->orderBy($sortBy, $sortDir)
            ->paginate($perPage)
            ->withQueryString();

        return $this->successResponse(
            data: BukuResource::collection($paginator->items()),
            message: 'Daftar koleksi buku berhasil diambil.',
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
     * GET /api/v1/bukus/{id} (Publik)
     * Mengambil detail satu buku beserta objek kategori dan riwayat peminjaman (NIM disamarkan).
     */
    public function show(string $id): JsonResponse
    {
        $buku = Buku::with(['kategori', 'peminjamans.user'])
            ->withCount('peminjamans')
            ->find($id);

        if (!$buku) {
            return $this->problemResponse(
                title: 'Resource Not Found',
                detail: "Buku dengan ID '{$id}' tidak ditemukan di katalog perpustakaan.",
                status: 404,
                errorSlug: 'book-not-found'
            );
        }

        return $this->successResponse(
            data: new BukuResource($buku),
            message: 'Detail buku berhasil ditemukan.'
        );
    }

    /**
     * POST /api/v1/bukus (Wajib auth:sanctum + api.admin)
     * Menambahkan buku baru ke katalog perpustakaan -> 201 Created + Header Location.
     */
    public function store(Request $request): JsonResponse
    {
        $currentYear = (int) date('Y');

        $validated = $request->validate([
            'isbn' => ['required', 'string', 'max:20', 'unique:bukus,isbn'],
            'judul' => ['required', 'string', 'min:5', 'max:200'],
            'penulis' => ['required', 'string', 'max:150'],
            'penerbit' => ['required', 'string', 'max:100'],
            'tahun_terbit' => ['required', 'integer', "between:1900,{$currentYear}"],
            'kategori_id' => ['required', 'exists:kategoris,id'],
            'stok' => ['required', 'integer', 'min:0'],
            'sinopsis' => ['nullable', 'string', 'max:2000'],
        ], [
            'isbn.required' => 'ISBN wajib diisi.',
            'isbn.unique' => 'Nomor ISBN ini sudah terdaftar di sistem.',
            'judul.required' => 'Judul buku wajib diisi.',
            'judul.min' => 'Judul buku minimal harus 5 karakter.',
            'penulis.required' => 'Nama penulis wajib diisi.',
            'penerbit.required' => 'Nama penerbit wajib diisi.',
            'tahun_terbit.required' => 'Tahun terbit wajib diisi.',
            'kategori_id.required' => 'Kategori buku wajib dipilih.',
            'kategori_id.exists' => 'Kategori yang dipilih tidak valid.',
            'stok.required' => 'Jumlah stok buku wajib diisi.',
            'stok.min' => 'Stok buku tidak boleh kurang dari 0.',
        ]);

        $buku = Buku::create($validated);
        $buku->load('kategori')->loadCount('peminjamans');

        return $this->createdResponse(
            data: new BukuResource($buku),
            locationUrl: url("/api/v1/bukus/{$buku->id}"),
            message: 'Buku baru berhasil ditambahkan.'
        );
    }

    /**
     * PUT / PATCH /api/v1/bukus/{id} (Wajib auth:sanctum + api.admin)
     * Menangani penggantian penuh (PUT) vs modifikasi parsial (PATCH).
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $buku = Buku::with('kategori')->withCount('peminjamans')->find($id);

        if (!$buku) {
            return $this->problemResponse(
                title: 'Resource Not Found',
                detail: "Buku dengan ID '{$id}' tidak ditemukan.",
                status: 404,
                errorSlug: 'book-not-found'
            );
        }

        $currentYear = (int) date('Y');
        $isPut = $request->isMethod('put');

        if ($isPut) {
            // Semantik PUT: Seluruh atribut representasi wajib dikirimkan
            $validated = $request->validate([
                'isbn' => ['required', 'string', 'max:20', Rule::unique('bukus', 'isbn')->ignore($buku->id)],
                'judul' => ['required', 'string', 'min:5', 'max:200'],
                'penulis' => ['required', 'string', 'max:150'],
                'penerbit' => ['required', 'string', 'max:100'],
                'tahun_terbit' => ['required', 'integer', "between:1900,{$currentYear}"],
                'kategori_id' => ['required', 'exists:kategoris,id'],
                'stok' => ['required', 'integer', 'min:0'],
                'sinopsis' => ['nullable', 'string', 'max:2000'],
            ]);

            // Jika atribut nullable (sinopsis) tidak dikirimkan pada semantik PUT, set ke null
            $validated['sinopsis'] = $request->input('sinopsis', null);
        } else {
            // Semantik PATCH: Hanya memvalidasi atribut yang dikirim
            $validated = $request->validate([
                'isbn' => ['sometimes', 'required', 'string', 'max:20', Rule::unique('bukus', 'isbn')->ignore($buku->id)],
                'judul' => ['sometimes', 'required', 'string', 'min:5', 'max:200'],
                'penulis' => ['sometimes', 'required', 'string', 'max:150'],
                'penerbit' => ['sometimes', 'required', 'string', 'max:100'],
                'tahun_terbit' => ['sometimes', 'required', 'integer', "between:1900,{$currentYear}"],
                'kategori_id' => ['sometimes', 'required', 'exists:kategoris,id'],
                'stok' => ['sometimes', 'required', 'integer', 'min:0'],
                'sinopsis' => ['sometimes', 'nullable', 'string', 'max:2000'],
            ]);

            if (empty($validated)) {
                return $this->problemResponse(
                    title: 'Bad Request',
                    detail: 'Permintaan PATCH wajib menyertakan minimal satu atribut yang ingin diperbarui.',
                    status: 400,
                    errorSlug: 'empty-patch-payload'
                );
            }
        }

        $buku->update($validated);
        $buku->load('kategori')->loadCount('peminjamans');

        return $this->successResponse(
            data: new BukuResource($buku),
            message: $isPut
                ? 'Seluruh representasi buku berhasil diganti (PUT).'
                : 'Atribut buku berhasil diperbarui sebagian (PATCH).'
        );
    }

    /**
     * DELETE /api/v1/bukus/{id} (Wajib auth:sanctum + api.admin)
     * Menghapus buku: 409 jika punya pinjaman aktif atau riwayat, 204 jika bersih.
     */
    public function destroy(string $id): JsonResponse
    {
        $buku = Buku::withCount('peminjamans')->find($id);

        if (!$buku) {
            return $this->problemResponse(
                title: 'Resource Not Found',
                detail: "Buku dengan ID '{$id}' tidak ditemukan.",
                status: 404,
                errorSlug: 'book-not-found'
            );
        }

        // Keputusan Desain #6: Cek pinjaman aktif terlebih dahulu
        $activeLoans = $buku->peminjamans()
            ->where('status', Peminjaman::STATUS_DIPINJAM)
            ->count();

        if ($activeLoans > 0) {
            return $this->problemResponse(
                title: 'Conflict — Book Has Active Loans',
                detail: "Buku '{$buku->judul}' tidak dapat dihapus karena masih sedang dipinjam secara aktif oleh {$activeLoans} mahasiswa.",
                status: 409,
                errorSlug: 'book-has-active-loans'
            );
        }

        // Cek apakah memiliki riwayat peminjaman selesai
        if ($buku->peminjamans_count > 0) {
            return $this->problemResponse(
                title: 'Conflict — Book Has Loan History',
                detail: "Buku '{$buku->judul}' tidak dapat dihapus karena memiliki riwayat data peminjaman di sistem perpustakaan.",
                status: 409,
                errorSlug: 'book-has-loan-history'
            );
        }

        $buku->delete();

        return $this->noContentResponse();
    }

    /**
     * GET /api/v1/bukus/{id}/referensi-eksternal (Publik)
     * Mengambil metadata buku tambahan dari OpenLibrary REST API via Laravel Http Client.
     */
    public function referensiEksternal(string $id): JsonResponse
    {
        $buku = Buku::find($id);

        if (!$buku) {
            return $this->problemResponse(
                title: 'Resource Not Found',
                detail: "Buku lokal dengan ID '{$id}' tidak ditemukan.",
                status: 404,
                errorSlug: 'book-not-found'
            );
        }

        $cleanIsbn = preg_replace('/[^0-9X]/i', '', (string) $buku->isbn);
        $cacheKey = "buku:referensi:{$cleanIsbn}";

        if ($cached = \Illuminate\Support\Facades\Cache::get($cacheKey)) {
            return $this->successResponse(
                data: $cached,
                message: 'Data referensi buku eksternal berhasil diambil dari OpenLibrary (cache).'
            );
        }

        $baseUrl = config('services.openlibrary.base_url', 'https://openlibrary.org');
        $timeout = (int) config('services.openlibrary.timeout', 8);

        try {
            $response = \Illuminate\Support\Facades\Http::timeout($timeout)
                ->acceptJson()
                ->withUserAgent('SIPUS-Del/1.0 (tugas PPW Institut Teknologi Del)')
                ->get("{$baseUrl}/api/books", [
                    'bibkeys' => "ISBN:{$cleanIsbn}",
                    'format' => 'json',
                    'jscmd' => 'data',
                ]);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return $this->problemResponse(
                title: 'Gateway Timeout',
                detail: 'Batas waktu komunikasi dengan layanan OpenLibrary telah habis (timeout).',
                status: 504,
                errorSlug: 'external-service-timeout'
            );
        } catch (\Throwable $e) {
            return $this->problemResponse(
                title: 'Bad Gateway',
                detail: 'Gagal terhubung ke layanan eksternal OpenLibrary.',
                status: 502,
                errorSlug: 'external-service-failed'
            );
        }

        // Tangani jika upstream mengembalikan kode status gagal (5xx, 4xx)
        if ($response->status() === 404) {
            return $this->problemResponse(
                title: 'Resource Not Found',
                detail: "Data referensi eksternal untuk ISBN '{$buku->isbn}' tidak ditemukan pada layanan OpenLibrary.",
                status: 404,
                errorSlug: 'external-book-not-found'
            );
        }

        if ($response->failed()) {
            return $this->problemResponse(
                title: 'Bad Gateway',
                detail: 'Layanan eksternal OpenLibrary merespons dengan kesalahan.',
                status: 502,
                errorSlug: 'external-service-error'
            );
        }

        $body = $response->json();
        $bibKey = "ISBN:{$cleanIsbn}";

        if (empty($body) || !isset($body[$bibKey])) {
            return $this->problemResponse(
                title: 'Resource Not Found',
                detail: "Data referensi eksternal untuk ISBN '{$buku->isbn}' tidak ditemukan pada katalog OpenLibrary.",
                status: 404,
                errorSlug: 'external-book-not-found'
            );
        }

        $ext = $body[$bibKey];

        // Pemetaan defensif atribut
        $penerbit = null;
        if (!empty($ext['publishers'])) {
            $penerbit = is_array($ext['publishers'][0])
                ? ($ext['publishers'][0]['name'] ?? null)
                : (string) $ext['publishers'][0];
        }

        $urlBuku = $ext['url'] ?? null;
        if (!$urlBuku && !empty($ext['ebooks'][0]['preview_url'])) {
            $urlBuku = $ext['ebooks'][0]['preview_url'];
        }

        $referensiEksternal = [
            'judul_eksternal' => $ext['title'] ?? null,
            'penerbit' => $penerbit,
            'jumlah_halaman' => isset($ext['number_of_pages']) ? (int) $ext['number_of_pages'] : null,
            'tanggal_terbit' => $ext['publish_date'] ?? null,
            'url_pratinjau' => $urlBuku,
            'sumber' => 'OpenLibrary',
        ];

        $payload = [
            'buku_lokal' => [
                'id' => $buku->id,
                'isbn' => $buku->isbn,
                'judul' => $buku->judul,
                'penulis' => $buku->penulis,
                'stok' => (int) $buku->stok,
            ],
            'referensi_eksternal' => $referensiEksternal,
        ];

        // Simpan hanya hasil sukses ke cache selama 1 jam
        \Illuminate\Support\Facades\Cache::put($cacheKey, $payload, 3600);

        return $this->successResponse(
            data: $payload,
            message: 'Data referensi buku eksternal berhasil diambil dari OpenLibrary.'
        );
    }
}
