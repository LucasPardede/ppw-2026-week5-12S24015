<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use App\Models\Peminjaman;
use App\Models\Pengumuman;
use App\Models\RiwayatTransaksi;
use App\Services\StatistikService;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected StatistikService $statistikService
    ) {}

    /**
     * Menampilkan dashboard analitik administrator menggunakan DB::table() untuk metrik & buku populer.
     */
    public function index(): View
    {
        $today = now()->startOfDay()->toDateString();

        $stats = $this->statistikService->getStatistikDashboard();

        $totalBuku = $stats['total_judul'];
        $totalKategori = $stats['total_kategori'];
        $totalMahasiswa = $stats['total_mahasiswa'];
        $pinjamanAktif = $stats['pinjaman_aktif'];
        $pinjamanTerlambat = $stats['pinjaman_terlambat'];

        // Total denda tersimpan yang belum lunas (dikembalikan denda_lunas = false)
        $dendaBelumLunas = (float) (DB::table('peminjamans')
            ->where('status', 'dikembalikan')
            ->where('denda_lunas', false)
            ->sum('denda') ?? 0);

        // Ditambah denda berjalan dari buku aktif yang terlambat
        $pinjamanTerlambatList = Peminjaman::where('status', 'dipinjam')
            ->where('tanggal_jatuh_tempo', '<', $today)
            ->get();

        foreach ($pinjamanTerlambatList as $p) {
            $dendaBelumLunas += $p->hitungDendaBerjalan();
        }

        // FITUR 4: 5 Buku terpopuler berdasarkan frekuensi peminjaman via DB::table()
        $bukuPopuler = $this->statistikService->getBukuPopuler(5);

        // Peringatan buku dengan stok menipis (<= 3) dengan Eager Loading
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
        $totalPengumuman = $stats['total_pengumuman'];
        $pengumumanTerbaru = Pengumuman::with('author')->latest()->take(4)->get();

        // 8 Riwayat Transaksi Audit Log terbaru (ACID Logging)
        $riwayatTransaksi = RiwayatTransaksi::with(['peminjaman.buku', 'aktor'])
            ->latest()
            ->take(8)
            ->get();

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
            'pengumumanTerbaru',
            'riwayatTransaksi'
        ));
    }
}
