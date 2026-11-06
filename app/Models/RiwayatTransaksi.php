<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiwayatTransaksi extends Model
{
    use HasFactory;

    protected $table = 'riwayat_transaksis';

    protected $fillable = [
        'peminjaman_id',
        'aktor_user_id',
        'aksi',
        'keterangan',
    ];

    /**
     * Relasi ke transaksi peminjaman terkait.
     */
    public function peminjaman(): BelongsTo
    {
        return $this->belongsTo(Peminjaman::class, 'peminjaman_id');
    }

    /**
     * Relasi ke pengguna/aktor yang melakukan aksi.
     */
    public function aktor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'aktor_user_id');
    }
}
