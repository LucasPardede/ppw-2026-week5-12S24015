<?php

namespace Database\Factories;

use App\Models\Buku;
use App\Models\Peminjaman;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Peminjaman>
 */
class PeminjamanFactory extends Factory
{
    protected $model = Peminjaman::class;

    public function definition(): array
    {
        $tanggalPinjam = fake()->dateTimeBetween('-30 days', '-5 days');
        $jatuhTempo = (clone $tanggalPinjam)->modify('+7 days');

        return [
            'user_id' => User::factory(),
            'buku_id' => Buku::factory(),
            'tanggal_pinjam' => $tanggalPinjam->format('Y-m-d'),
            'jatuh_tempo' => $jatuhTempo->format('Y-m-d'),
            'tanggal_kembali' => null,
            'perpanjangan' => 0,
            'status' => 'dipinjam',
            'denda' => 0,
            'denda_lunas' => false,
        ];
    }
}
