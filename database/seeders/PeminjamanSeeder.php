<?php

namespace Database\Seeders;

use App\Models\Buku;
use App\Models\Peminjaman;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class PeminjamanSeeder extends Seeder
{
    /**
     * Membuat data peminjaman contoh secara idempoten.
     * Stok buku dikurangi sesuai jumlah pinjaman aktif yang dibuat.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $lucas = User::where('email', 'lucas.pardede@del.ac.id')->first();
        $budi = User::where('email', 'budi.santoso@del.ac.id')->first();
        $maria = User::where('email', 'maria.simanjuntak@del.ac.id')->first();
        $johannes = User::where('email', 'johannes.hutapea@del.ac.id')->first();
        $sarah = User::where('email', 'sarah.manurung@del.ac.id')->first();
        $daniel = User::where('email', 'daniel.siahaan@del.ac.id')->first();

        $bukus = Buku::orderBy('id')->get();

        if ($bukus->count() < 20 || !$lucas || !$budi || !$maria || !$johannes || !$sarah || !$daniel) {
            Log::warning('[PeminjamanSeeder] Data prasyarat belum lengkap. Pastikan UserSeeder & BukuSeeder sudah jalan.');
            return;
        }

        // Definisi kasus peminjaman dengan kode_pinjam tetap (untuk idempoten)
        $kasus = [
            // Kasus 1: Lucas meminjam Clean Code — aktif, tepat waktu
            [
                'kode_pinjam' => 'PJM-SEED-000001',
                'user' => $lucas,
                'buku_index' => 0,
                'tanggal_pinjam' => $now->copy()->subDays(2)->toDateString(),
                'tanggal_jatuh_tempo' => $now->copy()->addDays(5)->toDateString(),
                'tanggal_kembali' => null,
                'status' => 'dipinjam',
                'denda' => 0,
                'denda_lunas' => false,
            ],
            // Kasus 2: Budi meminjam Database System Concepts — aktif, terlambat 3 hari
            [
                'kode_pinjam' => 'PJM-SEED-000002',
                'user' => $budi,
                'buku_index' => 4,
                'tanggal_pinjam' => $now->copy()->subDays(10)->toDateString(),
                'tanggal_jatuh_tempo' => $now->copy()->subDays(3)->toDateString(),
                'tanggal_kembali' => null,
                'status' => 'dipinjam',
                'denda' => 0,
                'denda_lunas' => false,
            ],
            // Kasus 3: Maria mengembalikan Laravel Up & Running — tepat waktu
            [
                'kode_pinjam' => 'PJM-SEED-000003',
                'user' => $maria,
                'buku_index' => 3,
                'tanggal_pinjam' => $now->copy()->subDays(14)->toDateString(),
                'tanggal_jatuh_tempo' => $now->copy()->subDays(7)->toDateString(),
                'tanggal_kembali' => $now->copy()->subDays(8)->toDateString(),
                'status' => 'dikembalikan',
                'denda' => 0,
                'denda_lunas' => true,
            ],
            // Kasus 4: Johannes mengembalikan Cryptography — terlambat 2 hari, denda belum lunas
            [
                'kode_pinjam' => 'PJM-SEED-000004',
                'user' => $johannes,
                'buku_index' => 13,
                'tanggal_pinjam' => $now->copy()->subDays(15)->toDateString(),
                'tanggal_jatuh_tempo' => $now->copy()->subDays(8)->toDateString(),
                'tanggal_kembali' => $now->copy()->subDays(6)->toDateString(),
                'status' => 'dikembalikan',
                'denda' => 2000,
                'denda_lunas' => false,
            ],
            // Kasus 5: Sarah mengembalikan Atomic Habits — terlambat 1 hari, denda LUNAS
            [
                'kode_pinjam' => 'PJM-SEED-000005',
                'user' => $sarah,
                'buku_index' => 18,
                'tanggal_pinjam' => $now->copy()->subDays(12)->toDateString(),
                'tanggal_jatuh_tempo' => $now->copy()->subDays(5)->toDateString(),
                'tanggal_kembali' => $now->copy()->subDays(4)->toDateString(),
                'status' => 'dikembalikan',
                'denda' => 1000,
                'denda_lunas' => true,
            ],
            // Kasus 6: Daniel meminjam The Pragmatic Programmer (stok 1) — aktif
            // Ini membuat stok buku tersebut menjadi 0
            [
                'kode_pinjam' => 'PJM-SEED-000006',
                'user' => $daniel,
                'buku_index' => 20, // The Pragmatic Programmer (stok awal = 1)
                'tanggal_pinjam' => $now->copy()->subDays(3)->toDateString(),
                'tanggal_jatuh_tempo' => $now->copy()->addDays(4)->toDateString(),
                'tanggal_kembali' => null,
                'status' => 'dipinjam',
                'denda' => 0,
                'denda_lunas' => false,
            ],
            // Kasus 7: Lucas meminjam Sapiens — aktif (untuk menguji kuota > 1)
            [
                'kode_pinjam' => 'PJM-SEED-000007',
                'user' => $lucas,
                'buku_index' => 19,
                'tanggal_pinjam' => $now->copy()->subDays(1)->toDateString(),
                'tanggal_jatuh_tempo' => $now->copy()->addDays(6)->toDateString(),
                'tanggal_kembali' => null,
                'status' => 'dipinjam',
                'denda' => 0,
                'denda_lunas' => false,
            ],
        ];

        // Buat peminjaman secara idempoten (berdasar kode_pinjam tetap)
        foreach ($kasus as $k) {
            $buku = $bukus[$k['buku_index']] ?? null;
            if (!$buku) continue;

            Peminjaman::firstOrCreate(
                ['kode_pinjam' => $k['kode_pinjam']],
                [
                    'user_id' => $k['user']->id,
                    'buku_id' => $buku->id,
                    'nim_peminjam' => $k['user']->nim ?? '-',
                    'nama_peminjam' => $k['user']->name,
                    'tanggal_pinjam' => $k['tanggal_pinjam'],
                    'tanggal_jatuh_tempo' => $k['tanggal_jatuh_tempo'],
                    'tanggal_kembali' => $k['tanggal_kembali'],
                    'status' => $k['status'],
                    'perpanjangan' => 0,
                    'denda' => $k['denda'],
                    'denda_lunas' => $k['denda_lunas'],
                ]
            );
        }

        // KONSISTENSI STOK: hitung ulang stok setiap buku berdasarkan pinjaman aktif
        $allBukus = Buku::all();
        foreach ($allBukus as $buku) {
            $pinjamanAktif = Peminjaman::where('buku_id', $buku->id)
                ->where('status', 'dipinjam')
                ->count();

            // Ambil stok awal dari BukuSeeder (stok di database saat ini)
            // Stok yang benar = stok saat ini di DB setelah BukuSeeder - pinjaman aktif
            // Karena BukuSeeder menyimpan stok penuh, kita perlu set ulang
            $stokAwal = $this->getStokAwal($buku->isbn);
            $stokBaru = max(0, $stokAwal - $pinjamanAktif);

            if ($buku->stok !== $stokBaru) {
                $buku->update(['stok' => $stokBaru]);
            }
        }

        // VERIFIKASI: pastikan tidak ada stok negatif
        $stokNegatif = Buku::where('stok', '<', 0)->count();
        if ($stokNegatif > 0) {
            Log::error("[PeminjamanSeeder] PERINGATAN: Ditemukan {$stokNegatif} buku dengan stok negatif!");
        } else {
            Log::info('[PeminjamanSeeder] Verifikasi stok: semua buku memiliki stok >= 0.');
        }
    }

    /**
     * Stok awal buku berdasarkan ISBN (dari BukuSeeder).
     */
    private function getStokAwal(string $isbn): int
    {
        $stokMap = [
            '978-602-03-8811-1' => 5,
            '978-013-235088-4' => 4,
            '978-149-191866-1' => 6,
            '978-149-207721-3' => 8,
            '978-007-352332-3' => 7,
            '978-144-937332-0' => 3,
            '978-149-195424-9' => 2,
            '978-161-729452-5' => 5,
            '978-013-463976-5' => 4,
            '978-111-908029-9' => 6,
            '978-013-444421-5' => 3,
            '978-013-359414-0' => 5,
            '978-111-884394-9' => 4,
            '978-159-327992-9' => 1,
            '978-013-604259-4' => 5,
            '978-149-203264-9' => 6,
            '978-026-203561-3' => 3,
            '978-073-521129-2' => 7,
            '978-006-231609-7' => 5,
            '978-150-112402-0' => 4,
            '978-020-161622-4' => 1,
        ];

        return $stokMap[$isbn] ?? 0;
    }
}
