<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use App\Models\Kategori;
use App\Models\Peminjaman;
use App\Models\Pengumuman;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Menampilkan dashboard analitik administrator.
     */
    public function index(): View
    {
        $today = now()->startOfDay()->toDateString();

        $totalBuku = Buku::count();
        $totalKategori = Kategori::count();
        $totalMahasiswa = User::where('role', 'mahasiswa')->count();
        $pinjamanAktif = Peminjaman::where('status', 'dipinjam')->count();
        $pinjamanTerlambat = Peminjaman::where('status', 'dipinjam')
            ->where('tanggal_jatuh_tempo', '<', $today)
            ->count();

        // Total denda tersimpan yang belum lunas
        $dendaBelumLunas = Peminjaman::where('status', 'dikembalikan')
            ->where('denda_lunas', false)
            ->sum('denda');

        // Ditambah denda berjalan dari buku aktif yang terlambat
        $pinjamanTerlambatList = Peminjaman::where('status', 'dipinjam')
            ->where('tanggal_jatuh_tempo', '<', $today)
            ->get();

        foreach ($pinjamanTerlambatList as $p) {
            $dendaBelumLunas += $p->hitungDendaBerjalan();
        }

        // 5 Buku terpopuler berdasarkan frekuensi peminjaman
        $bukuPopuler = Buku::with('kategori')
            ->withCount('peminjamans')
            ->orderByDesc('peminjamans_count')
            ->take(5)
            ->get();

        // Peringatan buku dengan stok menipis (<= 3)
        $stokMenipis = Buku::with('kategori')
            ->where('stok', '<=', 3)
            ->orderBy('stok', 'asc')
            ->take(6)
            ->get();

        // 5 Transaksi peminjaman terbaru dengan Eager Loading
        $peminjamanTerbaru = Peminjaman::with(['buku', 'user'])
            ->latest()
            ->take(5)
            ->get();

        // 4 Pengumuman terbaru
        $totalPengumuman = Pengumuman::count();
        $pengumumanTerbaru = Pengumuman::with('author')->latest()->take(4)->get();

        return view('admin.dashboard', compact(
            'totalBuku',
            'totalKategori',
            'totalMahasiswa',
            'pinjamanAktif',
            'pinjamanTerlambat',
            'dendaBelumLunas',
            'bukuPopuler',
            'stokMenipis',
            'peminjamanTerbaru',
            'totalPengumuman',
            'pengumumanTerbaru'
        ));
    }
}
