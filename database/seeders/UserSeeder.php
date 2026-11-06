<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Membuat akun pengguna (admin dan mahasiswa) secara idempoten.
     */
    public function run(): void
    {
        // Akun admin pustakawan (WAJIB modul: pustakawan@del.ac.id / password123)
        User::updateOrCreate(
            ['email' => 'pustakawan@del.ac.id'],
            [
                'name' => 'Pustakawan Del',
                'role' => 'admin',
                'password' => Hash::make('password123'),
            ]
        );

        // Akun admin lama
        User::updateOrCreate(
            ['email' => 'admin@del.ac.id'],
            [
                'name' => 'Administrator Perpustakaan Del',
                'role' => 'admin',
                'password' => Hash::make('password123'),
            ]
        );

        // Mahasiswa Lucas Pardede (NIM 12S24015)
        User::updateOrCreate(
            ['email' => 'lucas.pardede@del.ac.id'],
            [
                'name' => 'Lucas Pardede',
                'nim' => '12S24015',
                'role' => 'mahasiswa',
                'password' => Hash::make('password123'),
            ]
        );

        // Mahasiswa lainnya
        $mahasiswas = [
            ['email' => 'budi.santoso@del.ac.id', 'name' => 'Budi Santoso', 'nim' => '12S24001'],
            ['email' => 'maria.simanjuntak@del.ac.id', 'name' => 'Maria Simanjuntak', 'nim' => '12S24002'],
            ['email' => 'johannes.hutapea@del.ac.id', 'name' => 'Johannes Hutapea', 'nim' => '12S24003'],
            ['email' => 'sarah.manurung@del.ac.id', 'name' => 'Sarah Manurung', 'nim' => '12S24004'],
            ['email' => 'daniel.siahaan@del.ac.id', 'name' => 'Daniel Siahaan', 'nim' => '12S24005'],
        ];

        foreach ($mahasiswas as $mhs) {
            User::updateOrCreate(
                ['email' => $mhs['email']],
                [
                    'name' => $mhs['name'],
                    'nim' => $mhs['nim'],
                    'role' => 'mahasiswa',
                    'password' => Hash::make('password123'),
                ]
            );
        }
    }
}
