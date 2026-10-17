<?php

namespace Database\Seeders;

use App\Models\Kategori;
use Illuminate\Database\Seeder;

class KategoriSeeder extends Seeder
{
    /**
     * Membuat kategori koleksi perpustakaan secara idempoten.
     */
    public function run(): void
    {
        $kategoriData = [
            ['kode_kategori' => 'IF-WEB', 'nama_kategori' => 'Pemrograman Web & Arsitektur Perangkat Lunak'],
            ['kode_kategori' => 'IF-DB', 'nama_kategori' => 'Basis Data & Rekayasa Sistem Data'],
            ['kode_kategori' => 'SI-BIS', 'nama_kategori' => 'Sistem Informasi & Manajemen TI'],
            ['kode_kategori' => 'CS-SEC', 'nama_kategori' => 'Keamanan Siber & Jaringan Komputer'],
            ['kode_kategori' => 'AI-ML', 'nama_kategori' => 'Kecerdasan Buatan & Pembelajaran Mesin'],
            ['kode_kategori' => 'LIT-FIK', 'nama_kategori' => 'Sains Terapan & Pengembangan Diri'],
        ];

        foreach ($kategoriData as $k) {
            Kategori::firstOrCreate(
                ['kode_kategori' => $k['kode_kategori']],
                ['nama_kategori' => $k['nama_kategori']]
            );
        }
    }
}
