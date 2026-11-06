<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StatistikService
{
    /**
     * Mengambil 4 kartu statistik utama sirkulasi perpustakaan menggunakan DB::table().
     *
     * @return array{
     *   total_judul: int,
     *   total_stok_tersedia: int,
     *   total_sedang_dipinjam: int,
     *   total_dikembalikan: int
     * }
     */
    public function getStatistikKoleksi(): array
    {
        return [
            'total_judul' => (int) DB::table('bukus')->count(),
            'total_stok_tersedia' => (int) (DB::table('bukus')->sum('stok') ?? 0),
            'total_sedang_dipinjam' => (int) DB::table('peminjamans')->where('status', 'dipinjam')->count(),
            'total_dikembalikan' => (int) DB::table('peminjamans')->where('status', 'dikembalikan')->count(),
        ];
    }

    /**
     * Mengambil ringkasan data analitik untuk dashboard administrator menggunakan DB::table().
     */
    public function getStatistikDashboard(): array
    {
        $today = now()->startOfDay()->toDateString();

        $koleksi = $this->getStatistikKoleksi();

        return array_merge($koleksi, [
            'total_kategori' => (int) DB::table('kategoris')->count(),
            'total_mahasiswa' => (int) DB::table('users')->where('role', 'mahasiswa')->count(),
            'pinjaman_aktif' => $koleksi['total_sedang_dipinjam'],
            'pinjaman_terlambat' => (int) DB::table('peminjamans')
                ->where('status', 'dipinjam')
                ->where('tanggal_jatuh_tempo', '<', $today)
                ->count(),
            'total_pengumuman' => (int) DB::table('pengumumans')->count(),
        ]);
    }

    /**
     * FITUR 4: Mengambil daftar buku terpopuler berdasarkan frekuensi peminjaman
     * menggunakan Query Builder DB::table() dengan JOIN, GROUP BY, dan COUNT.
     *
     * @param int $limit
     * @return Collection
     */
    public function getBukuPopuler(int $limit = 5): Collection
    {
        return DB::table('bukus')
            ->join('kategoris', 'bukus.kategori_id', '=', 'kategoris.id')
            ->leftJoin('peminjamans', 'bukus.id', '=', 'peminjamans.buku_id')
            ->select(
                'bukus.id',
                'bukus.judul',
                'bukus.penulis',
                'bukus.isbn',
                'bukus.stok',
                'kategoris.nama_kategori',
                DB::raw('COUNT(peminjamans.id) as total_peminjaman'),
                DB::raw('COUNT(peminjamans.id) as peminjamans_count')
            )
            ->groupBy(
                'bukus.id',
                'bukus.judul',
                'bukus.penulis',
                'bukus.isbn',
                'bukus.stok',
                'kategoris.nama_kategori'
            )
            ->orderByDesc('total_peminjaman')
            ->limit($limit)
            ->get();
    }
}
