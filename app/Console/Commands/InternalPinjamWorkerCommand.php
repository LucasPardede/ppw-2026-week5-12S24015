<?php

namespace App\Console\Commands;

use App\Exceptions\StokHabisException;
use App\Models\User;
use App\Services\PeminjamanService;
use Illuminate\Console\Command;

class InternalPinjamWorkerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sipus:internal-pinjam-worker {userId} {bukuId} {--tahan=0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Worker internal untuk eksekusi satu transaksi peminjaman pada simulasi race condition.';

    /**
     * Execute the console command.
     */
    public function handle(PeminjamanService $service): int
    {
        $userId = (int) $this->argument('userId');
        $bukuId = (int) $this->argument('bukuId');
        $tahan = (int) $this->option('tahan');

        $user = User::find($userId);
        if (!$user) {
            $this->line(json_encode(['success' => false, 'error' => 'User tidak ditemukan', 'type' => 'UserNotFound']));
            return 1;
        }

        $dalamKunci = $tahan > 0 ? function ($buku) use ($tahan) {
            sleep($tahan);
        } : null;

        try {
            $peminjaman = $service->pinjam($user, $bukuId, $dalamKunci);

            $this->line(json_encode([
                'success' => true,
                'peminjaman_id' => $peminjaman->id,
                'kode_pinjam' => $peminjaman->kode_pinjam,
                'message' => 'Berhasil meminjam buku',
            ]));

            return 0;
        } catch (StokHabisException $e) {
            $this->line(json_encode([
                'success' => false,
                'error' => $e->getMessage(),
                'type' => 'StokHabisException',
            ]));

            return 0; // Tetap return 0 agar output JSON tertangkap rapi
        } catch (\Throwable $e) {
            $this->line(json_encode([
                'success' => false,
                'error' => $e->getMessage(),
                'type' => class_basename($e),
            ]));

            return 0;
        }
    }
}
