<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Peminjaman extends Model
{
    use HasFactory;

    protected $table = 'peminjamans';

    /**
     * Proteksi Mass Assignment dengan $fillable
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'buku_id',
        'tanggal_pinjam',
        'jatuh_tempo',
        'tanggal_kembali',
        'perpanjangan',
        'status',
        'denda',
        'denda_lunas',
    ];

    /**
     * Konversi tipe data atribut
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal_pinjam' => 'date',
            'jatuh_tempo' => 'date',
            'tanggal_kembali' => 'date',
            'denda_lunas' => 'boolean',
            'perpanjangan' => 'integer',
            'denda' => 'integer',
        ];
    }

    /**
     * Relasi Many-to-One: Setiap peminjaman dilakukan oleh satu user (mahasiswa).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relasi Many-to-One: Setiap peminjaman terkait dengan satu buku.
     */
    public function buku(): BelongsTo
    {
        return $this->belongsTo(Buku::class, 'buku_id');
    }

    /**
     * Menentukan apakah pinjaman ini telah atau pernah mengalami keterlambatan.
     */
    public function getIsTerlambatAttribute(): bool
    {
        if ($this->status === 'dipinjam') {
            return now()->startOfDay()->gt($this->jatuh_tempo->startOfDay());
        }

        if ($this->tanggal_kembali) {
            return $this->tanggal_kembali->startOfDay()->gt($this->jatuh_tempo->startOfDay());
        }

        return false;
    }

    /**
     * Menghitung selisih hari keterlambatan kalender (minimal 0 hari).
     */
    public function getHariTerlambatAttribute(): int
    {
        $jatuhTempo = $this->jatuh_tempo->startOfDay();

        if ($this->status === 'dipinjam') {
            $today = now()->startOfDay();
            if ($today->gt($jatuhTempo)) {
                return (int) $jatuhTempo->diffInDays($today);
            }
            return 0;
        }

        if ($this->tanggal_kembali) {
            $kembali = $this->tanggal_kembali->startOfDay();
            if ($kembali->gt($jatuhTempo)) {
                return (int) $jatuhTempo->diffInDays($kembali);
            }
        }

        return 0;
    }

    /**
     * Menghitung denda berjalan secara dinamis untuk pinjaman yang masih aktif.
     */
    public function hitungDendaBerjalan(): int
    {
        if ($this->status !== 'dipinjam') {
            return $this->denda;
        }

        $hari = $this->hari_terlambat;
        $tarif = (int) config('perpustakaan.tarif_denda_per_hari', 1000);

        return $hari * $tarif;
    }

    /**
     * Nilai denda efektif (tersimpan jika selesai, dinamis jika masih aktif).
     */
    public function getDendaEfektifAttribute(): int
    {
        if ($this->status === 'dikembalikan') {
            return $this->denda;
        }

        return $this->hitungDendaBerjalan();
    }
}
