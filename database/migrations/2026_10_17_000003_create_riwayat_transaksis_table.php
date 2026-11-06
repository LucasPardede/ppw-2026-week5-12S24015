<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Membuat tabel riwayat_transaksis untuk audit log peminjaman, pengembalian, perpanjangan, dan pelunasan.
     */
    public function up(): void
    {
        Schema::create('riwayat_transaksis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('peminjaman_id')->nullable()->constrained('peminjamans')->nullOnDelete();
            $table->foreignId('aktor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('aksi', 30);
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->index(['aksi', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riwayat_transaksis');
    }
};
