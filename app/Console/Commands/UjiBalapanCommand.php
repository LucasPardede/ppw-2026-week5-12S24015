<?php

namespace App\Console\Commands;

use App\Models\Buku;
use App\Models\Kategori;
use App\Models\Peminjaman;
use App\Models\RiwayatTransaksi;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Process;

class UjiBalapanCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sipus:uji-balapan {--proses=5 : Jumlah proses concurrent} {--tahan=0 : Detik penahanan kunci transaksi pertama}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Simulasi race condition peminjaman buku stok 1 dengan locking lockForUpdate dan transaksi ACID.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $jumlahProses = max(2, (int) $this->option('proses'));
        $detikTahan = max(0, (int) $this->option('tahan'));

        $this->info("================================================================================");
        $this->info("  SIPUS-DEL: PENGUJIAN RACE CONDITION & ACID ROW LOCKING (lockForUpdate)        ");
        $this->info("================================================================================");
        $this->line("Konfigurasi Pengujian:");
        $this->line("- Jumlah Proses Konkuren : <fg=cyan>{$jumlahProses} proses</>");
        $this->line("- Penahanan Row Lock     : <fg=cyan>{$detikTahan} detik</>");
        $this->newLine();

        // 1. Siapkan Kategori & Buku Uji (Stok = 1)
        $kategori = Kategori::firstOrCreate(
            ['kode_kategori' => 'TEST-RACE'],
            ['nama_kategori' => 'Kategori Khusus Uji Balapan']
        );

        $isbnUji = '978-RC-' . substr((string) time(), -6) . '-' . rand(10, 99);
        $buku = Buku::create([
            'isbn' => $isbnUji,
            'judul' => '[TEST ACID] Clean Architecture Under Concurrency',
            'penulis' => 'Robert C. Martin Test',
            'penerbit' => 'Del Press Test',
            'tahun_terbit' => 2026,
            'kategori_id' => $kategori->id,
            'stok' => 1,
            'sinopsis' => 'Buku stok 1 untuk pembuktian mutual exclusion lockForUpdate.',
        ]);

        $this->info("✓ Buku uji disiapkan: '{$buku->judul}' (ID: {$buku->id}, Stok Awal: 1)");

        // 2. Siapkan N Mahasiswa Uji
        $users = [];
        for ($i = 1; $i <= $jumlahProses; $i++) {
            $nim = sprintf("12S99%03d", $i);
            $user = User::create([
                'name' => "Tester Balapan {$i}",
                'email' => "tester.balapan{$i}@test.del.ac.id",
                'nim' => $nim,
                'role' => 'mahasiswa',
                'password' => Hash::make('password123'),
            ]);
            $users[] = $user;
        }

        $this->info("✓ Disiapkan {$jumlahProses} akun mahasiswa uji unik (NIM berbeda).");
        $this->newLine();
        $this->comment("Memulai {$jumlahProses} proses paralel secara serentak...");

        // 3. Jalankan Process Pool
        $phpBinary = PHP_BINARY;
        $artisanPath = base_path('artisan');

        $pool = Process::pool(function ($p) use ($phpBinary, $artisanPath, $users, $buku, $detikTahan) {
            foreach ($users as $index => $user) {
                // Proses pertama dapat menahan transaksi jika --tahan > 0
                $tahan = ($index === 0) ? $detikTahan : 0;
                $cmd = sprintf(
                    '"%s" "%s" sipus:internal-pinjam-worker %d %d --tahan=%d',
                    $phpBinary,
                    $artisanPath,
                    $user->id,
                    $buku->id,
                    $tahan
                );
                $p->command($cmd);
            }
        });

        $start = microtime(true);
        $results = $pool->run();
        $duration = round(microtime(true) - $start, 2);

        // 4. Analisis Hasil Output
        $tableData = [];
        $suksesCount = 0;
        $gagalCount = 0;

        foreach ($results as $index => $res) {
            $user = $users[$index];
            $output = trim($res->output());
            $json = json_decode($output, true);

            if ($json && !empty($json['success'])) {
                $suksesCount++;
                $tableData[] = [
                    'Proses #' . ($index + 1),
                    $user->name,
                    $user->nim,
                    '<fg=green;options=bold>BERHASIL (200)</>',
                    'Kode: ' . ($json['kode_pinjam'] ?? '-'),
                ];
            } else {
                $gagalCount++;
                $errType = $json['type'] ?? 'Error';
                $errMsg = $json['error'] ?? 'Ditolak';
                $tableData[] = [
                    'Proses #' . ($index + 1),
                    $user->name,
                    $user->nim,
                    '<fg=red>DITOLAK (422)</>',
                    "{$errType}: {$errMsg}",
                ];
            }
        }

        $this->newLine();
        $this->table(['Worker', 'Nama Pengguna', 'NIM', 'Status Transaksi', 'Keterangan Eksekusi'], $tableData);

        // 5. Verifikasi Integritas Data di Database (ACID Verification)
        $bukuAkhir = Buku::find($buku->id);
        $pinjamanAktifCount = Peminjaman::where('buku_id', $buku->id)
            ->where('status', Peminjaman::STATUS_DIPINJAM)
            ->count();

        $this->newLine();
        $this->info("--------------------------------------------------------------------------------");
        $this->info("  HASIL VERIFIKASI KEANDALAN TRANSAKSI (ACID ATOMICITY & ISOLATION)             ");
        $this->info("--------------------------------------------------------------------------------");
        $this->line("- Waktu Eksekusi Total   : {$duration} detik");
        $this->line("- Transaksi Sukses       : {$suksesCount} peminjaman");
        $this->line("- Transaksi Ditolak      : {$gagalCount} peminjaman (StokHabisException)");
        $this->line("- Stok Akhir Buku        : {$bukuAkhir->stok} (Harus tepat 0, TIDAK BOLEH MINUS)");
        $this->line("- Total Pinjaman Tercatat: {$pinjamanAktifCount} baris di database (Harus tepat 1)");
        $this->newLine();

        $isPassed = ($suksesCount === 1) && ($gagalCount === ($jumlahProses - 1)) && ($bukuAkhir->stok === 0) && ($pinjamanAktifCount === 1);

        if ($isPassed) {
            $this->info("  [VERIFIKASI LOLOS]: Transaksi ACID aman dari Race Condition!");
            $this->info("  Hanya 1 proses yang berhasil mendapatkan kunci, sisanya ditolak dengan konsisten.");
        } else {
            $this->error("  [VERIFIKASI GAGAL]: Terjadi anomali konkurensi atau overselling!");
        }
        $this->info("--------------------------------------------------------------------------------");

        // 6. Pembersihan Data Uji
        Peminjaman::where('buku_id', $buku->id)->delete();
        RiwayatTransaksi::whereNull('peminjaman_id')->orWhereHas('aktor', fn($q) => $q->whereIn('id', collect($users)->pluck('id')))->delete();
        $buku->delete();
        User::whereIn('id', collect($users)->pluck('id'))->delete();

        $this->line("✓ Data uji telah dibersihkan kembali secara otomatis.");
        $this->newLine();

        return $isPassed ? 0 : 1;
    }
}
