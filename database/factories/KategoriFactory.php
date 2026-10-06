<?php

namespace Database\Factories;

use App\Models\Kategori;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Kategori>
 */
class KategoriFactory extends Factory
{
    protected $model = Kategori::class;

    public function definition(): array
    {
        return [
            'kode_kategori' => strtoupper(fake()->unique()->lexify('KAT-???')),
            'nama_kategori' => fake()->unique()->words(2, true),
        ];
    }
}
