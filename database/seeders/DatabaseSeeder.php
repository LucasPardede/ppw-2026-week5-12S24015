<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * Memanggil seeder terpisah secara berurutan (idempoten).
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            KategoriSeeder::class,
            BukuSeeder::class,
            PeminjamanSeeder::class,
            PengumumanSeeder::class,
        ]);
    }
}
