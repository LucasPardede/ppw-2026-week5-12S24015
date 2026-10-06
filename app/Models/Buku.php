<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Buku extends Model
{
    use HasFactory;

    protected $table = 'bukus';

    /**
     * Proteksi Mass Assignment dengan $fillable
     *
     * @var list<string>
     */
    protected $fillable = [
        'isbn',
        'judul',
        'penulis',
        'penerbit',
        'tahun_terbit',
        'kategori_id',
        'stok',
        'sinopsis',
    ];

    /**
     * Relasi Many-to-One: Setiap buku dimiliki oleh satu kategori.
     */
    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class, 'kategori_id');
    }

    /**
     * Relasi One-to-Many: Setiap buku memiliki banyak riwayat peminjaman.
     */
    public function peminjamans(): HasMany
    {
        return $this->hasMany(Peminjaman::class, 'buku_id');
    }

    /**
     * Helper status stok untuk lencana UI (Tersedia, Menipis, Habis).
     */
    public function getStatusStokAttribute(): string
    {
        if ($this->stok > 3) {
            return 'tersedia';
        }
        if ($this->stok >= 1) {
            return 'menipis';
        }
        return 'habis';
    }
}
