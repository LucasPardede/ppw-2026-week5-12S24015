<?php

namespace App\Http\Controllers;

use App\Exceptions\BatasPinjamTercapaiException;
use App\Exceptions\DendaBelumLunasException;
use App\Exceptions\NimTidakTersediaException;
use App\Exceptions\PeminjamanGandaException;
use App\Exceptions\PeminjamanSudahKembaliException;
use App\Exceptions\PinjamanTerlambatException;
use App\Exceptions\StokHabisException;
use App\Models\Buku;
use App\Models\Peminjaman;
use App\Services\PeminjamanService;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class PeminjamanController extends Controller
{
    public function __construct(
        protected PeminjamanService $peminjamanService
    ) {}

    /**
     * Mahasiswa: Menampilkan daftar pinjaman aktif dan riwayat pinjaman milik sendiri.
     */
    public function indexMahasiswa(): View
    {
        $user = Auth::user();

        // Pinjaman aktif mahasiswa dengan Eager Loading buku & kategorinya
        $pinjamanAktif = Peminjaman::with(['buku.kategori'])
            ->where('user_id', $user->id)
            ->where('status', Peminjaman::STATUS_DIPINJAM)
            ->orderBy('tanggal_jatuh_tempo', 'asc')
            ->get();

        // Riwayat pinjaman yang sudah dikembalikan dengan Eager Loading
        $riwayat = Peminjaman::with(['buku.kategori'])
            ->where('user_id', $user->id)
            ->where('status', Peminjaman::STATUS_DIKEMBALIKAN)
            ->latest('tanggal_kembali')
            ->paginate(5);

        // FITUR 5: Peringatan buku yang jatuh tempo besok
        $besok = \Carbon\Carbon::now()->addDay()->toDateString();
        $jatuhTempoBesok = Peminjaman::with('buku')
            ->where('user_id', $user->id)
            ->where('status', Peminjaman::STATUS_DIPINJAM)
            ->where('tanggal_jatuh_tempo', $besok)
            ->get();

        return view('mahasiswa.pinjaman', compact('pinjamanAktif', 'riwayat', 'jatuhTempoBesok'));
    }

    /**
     * Mahasiswa: Melakukan peminjaman buku (melalui PeminjamanService dengan ACID transaction & lockForUpdate).
     */
    public function pinjam(Request $request, Buku $buku): RedirectResponse
    {
        $user = Auth::user();

        if ($user->role !== 'mahasiswa') {
            return back()->with('error', 'Hanya mahasiswa yang dapat melakukan peminjaman buku.');
        }

        try {
            $peminjaman = $this->peminjamanService->pinjam($user, $buku->id);

            return redirect()->route('pinjaman.index')
                ->with('success', "Buku '{$buku->judul}' berhasil dipinjam! Batas jatuh tempo pengembalian: " . $peminjaman->tanggal_jatuh_tempo->format('d M Y') . " (Kode Pinjam: {$peminjaman->kode_pinjam}).");
        } catch (StokHabisException | NimTidakTersediaException | PeminjamanGandaException | BatasPinjamTercapaiException | PinjamanTerlambatException | DendaBelumLunasException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        } catch (QueryException $e) {
            Log::error('[DB EXCEPTION] Gagal memproses peminjaman buku', [
                'action' => 'pinjam',
                'user_id' => $user->id,
                'buku_id' => $buku->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withInput()->with('error', 'Terjadi gangguan pada sistem basis data saat memproses peminjaman. Silakan coba beberapa saat lagi.');
        } catch (\Throwable $e) {
            Log::error('[ERROR] Peminjaman gagal', [
                'user_id' => $user->id,
                'buku_id' => $buku->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withInput()->with('error', $e->getMessage() ?: 'Gagal memproses peminjaman.');
        }
    }

    /**
     * Mahasiswa: Memperpanjang masa peminjaman buku (maksimal 1 kali, sebelum lewat jatuh tempo).
     */
    public function perpanjang(Peminjaman $peminjaman): RedirectResponse
    {
        $user = Auth::user();

        try {
            $peminjaman = $this->peminjamanService->perpanjang($peminjaman->id, $user);

            return back()->with('success', "Peminjaman berhasil diperpanjang hingga {$peminjaman->tanggal_jatuh_tempo->format('d M Y')}.");
        } catch (PinjamanTerlambatException | \RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        } catch (QueryException $e) {
            Log::error('[DB EXCEPTION] Gagal memperpanjang peminjaman', [
                'action' => 'perpanjang',
                'peminjaman_id' => $peminjaman->id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Terjadi gangguan sistem saat memperpanjang peminjaman.');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage() ?: 'Gagal memperpanjang peminjaman.');
        }
    }

    /**
     * Admin: Menampilkan seluruh transaksi peminjaman dengan Eager Loading (with(['buku', 'user'])).
     */
    public function indexAdmin(Request $request): View
    {
        $query = Peminjaman::with(['buku', 'user']);

        $status = $request->input('status');
        if ($status === 'dipinjam') {
            $query->where('status', Peminjaman::STATUS_DIPINJAM);
        } elseif ($status === 'terlambat') {
            $today = now()->startOfDay()->toDateString();
            $query->where('status', Peminjaman::STATUS_DIPINJAM)->where('tanggal_jatuh_tempo', '<', $today);
        } elseif ($status === 'dikembalikan') {
            $query->where('status', Peminjaman::STATUS_DIKEMBALIKAN);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('buku', function ($bq) use ($search) {
                    $bq->where('judul', 'like', "%{$search}%")
                       ->orWhere('isbn', 'like', "%{$search}%");
                })->orWhereHas('user', function ($uq) use ($search) {
                    $uq->where('name', 'like', "%{$search}%")
                       ->orWhere('email', 'like', "%{$search}%")
                       ->orWhere('nim', 'like', "%{$search}%");
                })->orWhere('kode_pinjam', 'like', "%{$search}%")
                  ->orWhere('nim_peminjam', 'like', "%{$search}%")
                  ->orWhere('nama_peminjam', 'like', "%{$search}%");
            });
        }

        $peminjamans = $query->latest()->paginate(10)->withQueryString();

        return view('admin.peminjaman.index', compact('peminjamans'));
    }

    /**
     * Admin: Menandai buku telah dikembalikan dan mencatat denda jika terlambat.
     */
    public function kembalikan(Peminjaman $peminjaman): RedirectResponse
    {
        $petugas = Auth::user();

        try {
            $peminjaman = $this->peminjamanService->kembali($peminjaman->id, $petugas);

            $pesan = "Pengembalian buku '{$peminjaman->buku->judul}' (Kode: {$peminjaman->kode_pinjam}) berhasil dicatat.";
            if ($peminjaman->denda > 0) {
                $dendaFmt = number_format($peminjaman->denda, 0, ',', '.');
                $pesan .= " Terdapat denda keterlambatan sebesar Rp{$dendaFmt}.";
            }

            return back()->with('success', $pesan);
        } catch (PeminjamanSudahKembaliException $e) {
            return back()->with('error', $e->getMessage());
        } catch (QueryException $e) {
            Log::error('[DB EXCEPTION] Gagal memproses pengembalian buku', [
                'action' => 'kembali',
                'peminjaman_id' => $peminjaman->id,
                'petugas_id' => $petugas?->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Terjadi gangguan sistem saat mencatat pengembalian buku.');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage() ?: 'Gagal memproses pengembalian buku.');
        }
    }

    /**
     * Admin: Menandai denda peminjaman telah dilunasi oleh mahasiswa.
     */
    public function tandaiLunas(Peminjaman $peminjaman): RedirectResponse
    {
        $petugas = Auth::user();

        try {
            $this->peminjamanService->tandaiLunas($peminjaman->id, $petugas);

            return back()->with('success', "Denda pada peminjaman {$peminjaman->kode_pinjam} berhasil ditandai LUNAS.");
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        } catch (QueryException $e) {
            Log::error('[DB EXCEPTION] Gagal menandai lunas denda', [
                'action' => 'tandaiLunas',
                'peminjaman_id' => $peminjaman->id,
                'petugas_id' => $petugas?->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Terjadi gangguan sistem saat pelunasan denda.');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage() ?: 'Gagal menandai lunas denda.');
        }
    }
}
