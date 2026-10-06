<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use App\Models\Peminjaman;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PeminjamanController extends Controller
{
    /**
     * Mahasiswa: Menampilkan daftar pinjaman aktif dan riwayat pinjaman milik sendiri.
     */
    public function indexMahasiswa(): View
    {
        $user = Auth::user();

        // Pinjaman aktif mahasiswa dengan Eager Loading buku & kategorinya
        $pinjamanAktif = Peminjaman::with(['buku.kategori'])
            ->where('user_id', $user->id)
            ->where('status', 'dipinjam')
            ->orderBy('jatuh_tempo', 'asc')
            ->get();

        // Riwayat pinjaman yang sudah dikembalikan dengan Eager Loading
        $riwayat = Peminjaman::with(['buku.kategori'])
            ->where('user_id', $user->id)
            ->where('status', 'dikembalikan')
            ->latest('tanggal_kembali')
            ->paginate(5);

        return view('mahasiswa.pinjaman', compact('pinjamanAktif', 'riwayat'));
    }

    /**
     * Mahasiswa: Melakukan peminjaman buku (dengan verifikasi stok, batas kuota, dan transaksi).
     */
    public function pinjam(Request $request, Buku $buku): RedirectResponse
    {
        $user = Auth::user();

        if ($user->role !== 'mahasiswa') {
            return back()->with('error', 'Hanya mahasiswa yang dapat melakukan peminjaman buku.');
        }

        // Cek stok buku
        if ($buku->stok <= 0) {
            return back()->with('error', "Buku '{$buku->judul}' saat ini sedang tidak tersedia (stok habis).");
        }

        // Cek apakah mahasiswa sedang meminjam buku yang sama
        $sedangMeminjam = Peminjaman::where('user_id', $user->id)
            ->where('buku_id', $buku->id)
            ->where('status', 'dipinjam')
            ->exists();

        if ($sedangMeminjam) {
            return back()->with('error', "Anda sedang meminjam buku '{$buku->judul}'. Tidak dapat meminjam buku yang sama secara bersamaan.");
        }

        // Cek kuota maksimal peminjaman
        $maxPinjam = (int) config('perpustakaan.maksimal_pinjam_buku', 3);
        $totalAktif = Peminjaman::where('user_id', $user->id)
            ->where('status', 'dipinjam')
            ->count();

        if ($totalAktif >= $maxPinjam) {
            return back()->with('error', "Anda telah mencapai batas maksimal peminjaman ({$maxPinjam} buku aktif). Harap kembalikan buku terlebih dahulu.");
        }

        // Cek apakah ada pinjaman terlambat
        if ($user->memilikiPinjamanTerlambat()) {
            return back()->with('error', 'Anda memiliki peminjaman buku yang telah melewati jatuh tempo. Harap selesaikan pengembalian sebelum meminjam buku baru.');
        }

        // Cek apakah ada denda yang belum lunas
        if ($user->totalDendaBelumLunas() > 0) {
            $totalDenda = number_format($user->totalDendaBelumLunas(), 0, ',', '.');
            return back()->with('error', "Anda memiliki tunggakan denda sebesar Rp{$totalDenda}. Harap lunasi denda ke pihak perpustakaan terlebih dahulu.");
        }

        $lamaPinjam = (int) config('perpustakaan.lama_pinjam_hari', 7);
        $tanggalPinjam = now();
        $jatuhTempo = now()->addDays($lamaPinjam);

        DB::transaction(function () use ($buku, $user, $tanggalPinjam, $jatuhTempo) {
            // Penguncian baris (row lock) untuk mencegah race condition pada stok
            $lockedBuku = Buku::where('id', $buku->id)->lockForUpdate()->first();

            if ($lockedBuku->stok <= 0) {
                throw new \RuntimeException('Stok buku habis saat proses peminjaman.');
            }

            $lockedBuku->decrement('stok');

            Peminjaman::create([
                'user_id' => $user->id,
                'buku_id' => $buku->id,
                'tanggal_pinjam' => $tanggalPinjam->toDateString(),
                'jatuh_tempo' => $jatuhTempo->toDateString(),
                'status' => 'dipinjam',
                'perpanjangan' => 0,
                'denda' => 0,
                'denda_lunas' => false,
            ]);
        });

        return redirect()->route('pinjaman.index')
            ->with('success', "Buku '{$buku->judul}' berhasil dipinjam! Batas jatuh tempo pengembalian: " . $jatuhTempo->format('d M Y') . ".");
    }

    /**
     * Mahasiswa: Memperpanjang masa peminjaman buku (maksimal 1 kali, sebelum lewat jatuh tempo).
     */
    public function perpanjang(Peminjaman $peminjaman): RedirectResponse
    {
        $user = Auth::user();

        if ($peminjaman->user_id !== $user->id) {
            abort(403, 'Anda tidak berhak mengakses transaksi ini.');
        }

        if ($peminjaman->status !== 'dipinjam') {
            return back()->with('error', 'Buku ini sudah dikembalikan.');
        }

        if ($peminjaman->perpanjangan >= 1) {
            return back()->with('error', 'Peminjaman ini sudah pernah diperpanjang sebelumnya (maksimal 1 kali).');
        }

        if ($peminjaman->is_terlambat) {
            return back()->with('error', 'Peminjaman yang sudah terlambat tidak dapat diperpanjang. Harap segera kembalikan ke perpustakaan.');
        }

        $lamaPerpanjang = (int) config('perpustakaan.lama_perpanjang_hari', 7);
        $jatuhTempoBaru = $peminjaman->jatuh_tempo->copy()->addDays($lamaPerpanjang);

        $peminjaman->update([
            'jatuh_tempo' => $jatuhTempoBaru->toDateString(),
            'perpanjangan' => $peminjaman->perpanjangan + 1,
        ]);

        return back()->with('success', "Peminjaman berhasil diperpanjang hingga {$jatuhTempoBaru->format('d M Y')}.");
    }

    /**
     * Admin: Menampilkan seluruh transaksi peminjaman dengan Eager Loading (with(['buku', 'user'])).
     */
    public function indexAdmin(Request $request): View
    {
        $query = Peminjaman::with(['buku', 'user']);

        $status = $request->input('status');
        if ($status === 'dipinjam') {
            $query->where('status', 'dipinjam');
        } elseif ($status === 'terlambat') {
            $today = now()->startOfDay()->toDateString();
            $query->where('status', 'dipinjam')->where('jatuh_tempo', '<', $today);
        } elseif ($status === 'dikembalikan') {
            $query->where('status', 'dikembalikan');
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
                });
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
        if ($peminjaman->status === 'dikembalikan') {
            return back()->with('error', 'Peminjaman ini sudah tercatat dikembalikan sebelumnya.');
        }

        $tanggalKembali = now();
        $denda = $peminjaman->hitungDendaBerjalan();
        $dendaLunas = ($denda === 0);

        DB::transaction(function () use ($peminjaman, $tanggalKembali, $denda, $dendaLunas) {
            // Kembalikan stok buku
            Buku::where('id', $peminjaman->buku_id)->increment('stok');

            $peminjaman->update([
                'status' => 'dikembalikan',
                'tanggal_kembali' => $tanggalKembali->toDateString(),
                'denda' => $denda,
                'denda_lunas' => $dendaLunas,
            ]);
        });

        $pesan = "Pengembalian buku '{$peminjaman->buku->judul}' berhasil dicatat.";
        if ($denda > 0) {
            $dendaFmt = number_format($denda, 0, ',', '.');
            $pesan .= " Terdapat denda keterlambatan sebesar Rp{$dendaFmt}.";
        }

        return back()->with('success', $pesan);
    }

    /**
     * Admin: Menandai denda peminjaman telah dilunasi oleh mahasiswa.
     */
    public function tandaiLunas(Peminjaman $peminjaman): RedirectResponse
    {
        if ($peminjaman->denda_lunas) {
            return back()->with('error', 'Denda pada peminjaman ini sudah berstatus lunas.');
        }

        $peminjaman->update(['denda_lunas' => true]);

        return back()->with('success', 'Denda berhasil ditandai LUNAS.');
    }
}
