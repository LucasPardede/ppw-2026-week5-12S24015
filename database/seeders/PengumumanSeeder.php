<?php

namespace Database\Seeders;

use App\Models\Pengumuman;
use App\Models\User;
use Illuminate\Database\Seeder;

class PengumumanSeeder extends Seeder
{
    /**
     * Membuat data pengumuman perpustakaan secara idempoten.
     * Teks sudah disesuaikan dengan konfigurasi perpustakaan (7 hari pinjam, 3 buku maks, Rp1.000/hari).
     */
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();
        if (!$admin) return;

        $pengumumans = [
            [
                'slug' => 'perpanjangan-jam-layanan-malam-uts',
                'judul' => 'Perpanjangan Jam Layanan Malam & Akses Ruang Baca Menjelang UTS',
                'konten' => "Diberitahukan kepada seluruh mahasiswa Institut Teknologi Del, dalam rangka mendukung persiapan Ujian Tengah Semester (UTS), Perpustakaan Institut Teknologi Del memperpanjang jam layanan ruang baca hingga pukul 22.00 WIB setiap hari Senin - Jumat. \n\nLayanan sirkulasi peminjaman dan pengembalian tetap dilayani hingga pukul 17.00 WIB. Harap menjaga ketenangan dan kebersihan area perpustakaan.",
                'kategori' => 'penting',
                'is_pinned' => true,
                'is_aktif' => true,
            ],
            [
                'slug' => 'ketentuan-kuota-peminjaman-buku-online',
                'judul' => 'Ketentuan Kuota Peminjaman Buku & Perpanjangan Mandiri Online',
                'konten' => "Mahasiswa dapat meminjam maksimal 3 (tiga) eksemplar buku secara bersamaan dengan masa pinjam standar 7 hari kalender. \n\nPerpanjangan masa pinjam kini dapat dilakukan secara mandiri sebanyak 1 (satu) kali (+7 hari) melalui Portal Mahasiswa SIPUS-Del sebelum tanggal jatuh tempo terlewati. Keterlambatan pengembalian dikenakan denda sebesar Rp1.000/hari per buku.",
                'kategori' => 'layanan',
                'is_pinned' => true,
                'is_aktif' => true,
            ],
            [
                'slug' => 'akses-langganan-database-jurnal-ieee',
                'judul' => 'Akses Langganan Database Jurnal IEEE Xplore & ScienceDirect Tahun Akademik Ini',
                'konten' => "Perpustakaan IT Del telah memperbarui langganan akses jurnal ilmiah internasional IEEE Xplore dan ScienceDirect. Akses dapat dinikmati melalui jaringan intranet kampus IT Del atau menggunakan VPN Kampus Del dengan kredensial CIS masing-masing mahasiswa.",
                'kategori' => 'info',
                'is_pinned' => false,
                'is_aktif' => true,
            ],
            [
                'slug' => 'bedah-buku-software-craftsmanship',
                'judul' => 'Bedah Buku & Diskusi Literasi: Software Craftsmanship bersama Pustakawan',
                'konten' => "Sambut kegiatan literasi bulanan IT Del! Diskusi bedah buku Clean Architecture & Software Engineering Practices akan diselenggarakan pada hari Jumat, 24 Oktober 2026 di Auditorium Perpustakaan Lantai 2. Disediakan sertifikat dan e-voucher bagi 50 pendaftar pertama.",
                'kategori' => 'kegiatan',
                'is_pinned' => false,
                'is_aktif' => true,
            ],
        ];

        foreach ($pengumumans as $p) {
            Pengumuman::firstOrCreate(
                ['slug' => $p['slug']],
                array_merge($p, ['user_id' => $admin->id])
            );
        }
    }
}
