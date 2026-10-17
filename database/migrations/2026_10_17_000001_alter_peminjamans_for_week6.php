<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Rename jatuh_tempo -> tanggal_jatuh_tempo dan tambah kolom kode_pinjam, nim_peminjam, nama_peminjam (nullable sementara)
        Schema::table('peminjamans', function (Blueprint $table) {
            $table->renameColumn('jatuh_tempo', 'tanggal_jatuh_tempo');
            $table->string('kode_pinjam', 30)->nullable()->unique()->after('id');
            $table->string('nim_peminjam', 20)->nullable()->after('buku_id');
            $table->string('nama_peminjam', 150)->nullable()->after('nim_peminjam');
        });

        // 2. Backfill data baris lama jika ada
        $rows = DB::table('peminjamans')->get();
        foreach ($rows as $row) {
            $user = DB::table('users')->where('id', $row->user_id)->first();
            $tanggal = !empty($row->tanggal_pinjam) ? date('Ymd', strtotime($row->tanggal_pinjam)) : date('Ymd');
            $kodePinjam = sprintf('PJM-%s-%06d', $tanggal, $row->id);
            $nim = ($user && !empty($user->nim)) ? $user->nim : '-';
            $nama = $user ? $user->name : 'Peminjam ' . $row->id;

            DB::table('peminjamans')->where('id', $row->id)->update([
                'kode_pinjam' => $kodePinjam,
                'nim_peminjam' => $nim,
                'nama_peminjam' => $nama,
            ]);
        }

        // 3. Ubah kolom menjadi NOT NULL
        Schema::table('peminjamans', function (Blueprint $table) {
            $table->string('kode_pinjam', 30)->nullable(false)->change();
            $table->string('nim_peminjam', 20)->nullable(false)->change();
            $table->string('nama_peminjam', 150)->nullable(false)->change();
        });

        // 4. Ubah foreign key buku_id menjadi cascadeOnDelete dan tambahkan indeks komposit
        Schema::table('peminjamans', function (Blueprint $table) {
            $table->dropForeign(['buku_id']);
            $table->foreign('buku_id')->references('id')->on('bukus')->cascadeOnDelete();
            $table->index(['nim_peminjam', 'status'], 'peminjamans_nim_status_idx');
            $table->index(['buku_id', 'nim_peminjam', 'status'], 'peminjamans_buku_nim_status_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('peminjamans', function (Blueprint $table) {
            $table->dropIndex('peminjamans_buku_nim_status_idx');
            $table->dropIndex('peminjamans_nim_status_idx');
            $table->dropForeign(['buku_id']);
            $table->foreign('buku_id')->references('id')->on('bukus')->restrictOnDelete();
            $table->dropUnique(['kode_pinjam']);
            $table->dropColumn(['kode_pinjam', 'nim_peminjam', 'nama_peminjam']);
            $table->renameColumn('tanggal_jatuh_tempo', 'jatuh_tempo');
        });
    }
};
