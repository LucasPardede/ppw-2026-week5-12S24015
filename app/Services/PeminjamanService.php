<?php

namespace App\Services;

use App\Exceptions\BatasPinjamTercapaiException;
use App\Exceptions\DendaBelumLunasException;
use App\Exceptions\NimTidakTersediaException;
use App\Exceptions\PeminjamanGandaException;
use App\Exceptions\PeminjamanSudahKembaliException;
use App\Exceptions\PinjamanTerlambatException;
use App\Exceptions\StokHabisException;
use App\Models\Buku;
use App\Models\Peminjaman;
use App\Models\RiwayatTransaksi;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PeminjamanService
{
    /**
     * Memproses peminjaman buku oleh mahasiswa dengan transaksi ACID dan locking ketat.
     *
     * @param User $user Akun peminjam
     * @param int $bukuId ID buku yang dipinjam
     * @param callable|null $dalamKunci Callback pengujian balapan yang dipanggil saat row lock aktif
     * @return Peminjaman
     */
    public function pinjam(User $user, int $bukuId, ?callable $dalamKunci = null): Peminjaman
    {
        return DB::transaction(function () use ($user, $bukuId, $dalamKunci) {
            // 1. Kunci baris buku menggunakan SELECT ... FOR UPDATE
            $buku = Buku::where('id', $bukuId)->lockForUpdate()->firstOrFail();

            // Callback untuk simulasi uji balapan (race condition testing)
            if ($dalamKunci !== null) {
                $dalamKunci($buku);
            }

            // 2. Cek ketersediaan stok
            if ($buku->stok < 1) {
                Log::info("[Peminjaman Ditolak] Stok buku habis: {$buku->judul} (ID: {$bukuId}) oleh user {$user->id}");
                throw new StokHabisException("Buku '{$buku->judul}' saat ini sedang tidak tersedia (stok habis).");
            }

            // 3. Pastikan NIM tersedia
            $nim = trim($user->nim ?? '');
            if (empty($nim) || $nim === '-') {
                Log::info("[Peminjaman Ditolak] NIM tidak tersedia untuk user {$user->id}");
                throw new NimTidakTersediaException();
            }

            // 4. Cegah NIM yang sama meminjam buku yang sama jika belum dikembalikan
            $sedangMeminjam = Peminjaman::where('buku_id', $bukuId)
                ->where('nim_peminjam', $nim)
                ->where('status', Peminjaman::STATUS_DIPINJAM)
                ->exists();

            if ($sedangMeminjam) {
                Log::info("[Peminjaman Ditolak] Peminjaman ganda oleh NIM {$nim} untuk buku {$bukuId}");
                throw new PeminjamanGandaException("Anda sedang meminjam buku '{$buku->judul}'. Tidak dapat meminjam buku yang sama secara bersamaan.");
            }

            // 5. Cek kuota maksimal peminjaman aktif per NIM
            $maxPinjam = (int) config('perpustakaan.maksimal_pinjam_buku', 3);
            $totalAktif = Peminjaman::where('nim_peminjam', $nim)
                ->where('status', Peminjaman::STATUS_DIPINJAM)
                ->count();

            if ($totalAktif >= $maxPinjam) {
                Log::info("[Peminjaman Ditolak] Kuota pinjam penuh untuk NIM {$nim}: {$totalAktif}/{$maxPinjam}");
                throw new BatasPinjamTercapaiException("Anda telah mencapai batas maksimal peminjaman ({$maxPinjam} buku aktif). Harap kembalikan buku terlebih dahulu.");
            }

            // 6. Cek keterlambatan pengembalian buku lain
            if ($user->memilikiPinjamanTerlambat()) {
                Log::info("[Peminjaman Ditolak] User {$user->id} memiliki pinjaman terlambat");
                throw new PinjamanTerlambatException('Anda memiliki peminjaman buku yang telah melewati jatuh tempo. Harap selesaikan pengembalian sebelum meminjam buku baru.');
            }

            // 7. Cek denda belum lunas
            if ($user->totalDendaBelumLunas() > 0) {
                $totalDenda = number_format($user->totalDendaBelumLunas(), 0, ',', '.');
                Log::info("[Peminjaman Ditolak] User {$user->id} memiliki denda belum lunas Rp{$totalDenda}");
                throw new DendaBelumLunasException("Anda memiliki tunggakan denda sebesar Rp{$totalDenda}. Harap lunasi denda ke pihak perpustakaan terlebih dahulu.");
            }

            // 8. Kurangi stok buku
            $buku->decrement('stok');

            // 9. Buat catatan peminjaman
            $lamaPinjam = (int) config('perpustakaan.lama_pinjam_hari', 7);
            $tglPinjam = Carbon::now();
            $tglJatuhTempo = Carbon::now()->addDays($lamaPinjam);

            $peminjaman = Peminjaman::create([
                'kode_pinjam' => Peminjaman::buatKode(),
                'user_id' => $user->id,
                'buku_id' => $buku->id,
                'nim_peminjam' => $nim,
                'nama_peminjam' => $user->name,
                'tanggal_pinjam' => $tglPinjam->toDateString(),
                'tanggal_jatuh_tempo' => $tglJatuhTempo->toDateString(),
                'tanggal_kembali' => null,
                'status' => Peminjaman::STATUS_DIPINJAM,
                'perpanjangan' => 0,
                'denda' => 0,
                'denda_lunas' => false,
            ]);

            // 10. Catat log audit transaksi sukses
            RiwayatTransaksi::create([
                'peminjaman_id' => $peminjaman->id,
                'aktor_user_id' => $user->id,
                'aksi' => 'pinjam',
                'keterangan' => "Peminjaman buku '{$buku->judul}' (Kode: {$peminjaman->kode_pinjam}) oleh {$user->name} ({$nim})",
            ]);

            return $peminjaman;
        }, 3);
    }

    /**
     * Memproses pengembalian buku dengan transaksi ACID dan penguncian konsisten.
     *
     * Urutan penguncian KONSISTEN:
     * 1. Baca buku_id dari peminjaman tanpa kunci.
     * 2. Kunci baris BUKU terlebih dahulu (Buku::lockForUpdate).
     * 3. Baru kunci baris PEMINJAMAN (Peminjaman::lockForUpdate) dan verifikasi ulang statusnya.
     * Hal ini mencegah DEADLOCK antara dua transaksi yang saling menunggu kunci tabel bukus dan peminjamans.
     *
     * @param int $peminjamanId ID peminjaman yang akan dikembalikan
     * @param User|null $petugas Akun pustakawan/admin yang memproses pengembalian
     * @return Peminjaman
     */
    public function kembali(int $peminjamanId, ?User $petugas = null): Peminjaman
    {
        // 1. Dapatkan buku_id tanpa penguncian untuk menentukan urutan kunci
        $peminjamanAwal = Peminjaman::findOrFail($peminjamanId);
        $bukuId = $peminjamanAwal->buku_id;

        return DB::transaction(function () use ($peminjamanId, $bukuId, $petugas) {
            // 2. Kunci baris BUKU terlebih dahulu
            $buku = Buku::where('id', $bukuId)->lockForUpdate()->firstOrFail();

            // 3. Kunci baris PEMINJAMAN dan periksa ulang statusnya di dalam kunci
            $peminjaman = Peminjaman::where('id', $peminjamanId)->lockForUpdate()->firstOrFail();

            if ($peminjaman->status !== Peminjaman::STATUS_DIPINJAM) {
                Log::info("[Pengembalian Ditolak] Peminjaman {$peminjamanId} sudah berstatus '{$peminjaman->status}'");
                throw new PeminjamanSudahKembaliException("Peminjaman ini sudah tercatat dikembalikan sebelumnya.");
            }

            // 4. Hitung denda keterlambatan
            $denda = $peminjaman->hitungDendaBerjalan();
            $dendaLunas = ($denda === 0);

            // 5. Update status dan data pengembalian
            $peminjaman->update([
                'status' => Peminjaman::STATUS_DIKEMBALIKAN,
                'tanggal_kembali' => Carbon::now()->toDateString(),
                'denda' => $denda,
                'denda_lunas' => $dendaLunas,
            ]);

            // 6. Tambah kembali stok buku
            $buku->increment('stok');

            // 7. Catat riwayat audit
            $dendaText = $denda > 0 ? " dengan denda Rp" . number_format($denda, 0, ',', '.') : " tanpa denda";
            $aktorText = $petugas ? " oleh {$petugas->name}" : "";

            RiwayatTransaksi::create([
                'peminjaman_id' => $peminjaman->id,
                'aktor_user_id' => $petugas?->id,
                'aksi' => 'kembali',
                'keterangan' => "Pengembalian buku '{$buku->judul}' (Kode: {$peminjaman->kode_pinjam}) dicatat{$dendaText}{$aktorText}",
            ]);

            return $peminjaman;
        }, 3);
    }

    /**
     * Memperpanjang masa peminjaman buku dengan transaksi dan row lock.
     *
     * @param int $peminjamanId ID peminjaman
     * @param User $user Pengguna yang meminta perpanjangan
     * @return Peminjaman
     */
    public function perpanjang(int $peminjamanId, User $user): Peminjaman
    {
        return DB::transaction(function () use ($peminjamanId, $user) {
            $peminjaman = Peminjaman::where('id', $peminjamanId)->lockForUpdate()->firstOrFail();

            // Hak akses: hanya peminjam atau admin yang boleh memperpanjang
            if ($user->role !== 'admin' && $peminjaman->user_id !== $user->id) {
                throw new \RuntimeException('Anda tidak berhak memperpanjang transaksi ini.');
            }

            if ($peminjaman->status !== Peminjaman::STATUS_DIPINJAM) {
                throw new \RuntimeException('Buku ini sudah dikembalikan.');
            }

            $maxPerpanjang = (int) config('perpustakaan.maksimal_perpanjangan', 1);
            if ($peminjaman->perpanjangan >= $maxPerpanjang) {
                throw new \RuntimeException("Peminjaman ini sudah pernah diperpanjang sebelumnya (maksimal {$maxPerpanjang} kali).");
            }

            if ($peminjaman->is_terlambat) {
                throw new PinjamanTerlambatException('Peminjaman yang sudah terlambat tidak dapat diperpanjang. Harap segera kembalikan ke perpustakaan.');
            }

            $lamaPerpanjang = (int) config('perpustakaan.lama_perpanjang_hari', 7);
            $jatuhTempoBaru = Carbon::parse($peminjaman->tanggal_jatuh_tempo)->addDays($lamaPerpanjang);

            $peminjaman->update([
                'tanggal_jatuh_tempo' => $jatuhTempoBaru->toDateString(),
                'perpanjangan' => $peminjaman->perpanjangan + 1,
            ]);

            RiwayatTransaksi::create([
                'peminjaman_id' => $peminjaman->id,
                'aktor_user_id' => $user->id,
                'aksi' => 'perpanjang',
                'keterangan' => "Perpanjangan peminjaman buku (Kode: {$peminjaman->kode_pinjam}) hingga " . $jatuhTempoBaru->format('d M Y') . " oleh {$user->name}",
            ]);

            return $peminjaman;
        }, 3);
    }

    /**
     * Menandai denda peminjaman telah lunas dengan transaksi dan row lock.
     *
     * @param int $peminjamanId ID peminjaman
     * @param User|null $petugas Petugas yang menandai lunas
     * @return Peminjaman
     */
    public function tandaiLunas(int $peminjamanId, ?User $petugas = null): Peminjaman
    {
        return DB::transaction(function () use ($peminjamanId, $petugas) {
            $peminjaman = Peminjaman::where('id', $peminjamanId)->lockForUpdate()->firstOrFail();

            if ($peminjaman->denda_lunas) {
                throw new \RuntimeException('Denda pada peminjaman ini sudah berstatus lunas.');
            }

            $peminjaman->update(['denda_lunas' => true]);

            RiwayatTransaksi::create([
                'peminjaman_id' => $peminjaman->id,
                'aktor_user_id' => $petugas?->id,
                'aksi' => 'lunas',
                'keterangan' => "Pelunasan denda sebesar Rp" . number_format($peminjaman->denda, 0, ',', '.') . " untuk peminjaman {$peminjaman->kode_pinjam}" . ($petugas ? " oleh {$petugas->name}" : ""),
            ]);

            return $peminjaman;
        }, 3);
    }
}
