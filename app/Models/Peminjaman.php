<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * Model Peminjaman
 *
 * @property int $id
 * @property string $kode_pinjam
 * @property int|null $user_id
 * @property int $buku_id
 * @property string $nim_peminjam
 * @property string $nama_peminjam
 * @property Carbon $tanggal_pinjam
 * @property Carbon $tanggal_jatuh_tempo
 * @property Carbon|null $tanggal_kembali
 * @property int $perpanjangan
 * @property string $status
 * @property int $denda
 * @property bool $denda_lunas
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read bool $is_terlambat
 * @property-read int $hari_terlambat
 * @property-read int $denda_efektif
 * @property-read User|null $user
 * @property-read Buku|null $buku
 */
class Peminjaman extends Model
{
    use HasFactory;

    protected $table = 'peminjamans';

    public const STATUS_DIPINJAM = 'dipinjam';
    public const STATUS_DIKEMBALIKAN = 'dikembalikan';

    /**
     * Atribut yang dapat diisi secara massal (Mass Assignment Protection)
     *
     * @var list<string>
     */
    protected $fillable = [
        'kode_pinjam',
        'user_id',
        'buku_id',
        'nim_peminjam',
        'nama_peminjam',
        'tanggal_pinjam',
        'tanggal_jatuh_tempo',
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
            'tanggal_jatuh_tempo' => 'date',
            'tanggal_kembali' => 'date',
            'denda_lunas' => 'boolean',
            'perpanjangan' => 'integer',
            'denda' => 'integer',
        ];
    }

    /**
     * Hook model booted untuk mengisi kode_pinjam dan snapshot identitas peminjam
     */
    protected static function booted(): void
    {
        static::creating(function (Peminjaman $peminjaman) {
            if (empty($peminjaman->kode_pinjam)) {
                $peminjaman->kode_pinjam = static::buatKode();
            }

            // Snapshot identitas peminjam jika belum terisi
            if (empty($peminjaman->nim_peminjam) || empty($peminjaman->nama_peminjam)) {
                $user = $peminjaman->user_id ? User::find($peminjaman->user_id) : Auth::user();
                if ($user) {
                    $peminjaman->nim_peminjam = $peminjaman->nim_peminjam ?: ($user->nim ?: '-');
                    $peminjaman->nama_peminjam = $peminjaman->nama_peminjam ?: $user->name;
                }
            }
        });
    }

    /**
     * Generator kode peminjaman unik: PJM-YYYYMMDD-XXXXXX
     */
    public static function buatKode(): string
    {
        $prefix = 'PJM-' . date('Ymd') . '-';
        do {
            $randomStr = strtoupper(Str::random(6));
            $kode = $prefix . $randomStr;
        } while (static::where('kode_pinjam', $kode)->exists());

        return $kode;
    }

    /**
     * Scope peminjaman aktif (status dipinjam)
     */
    public function scopeDipinjam(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_DIPINJAM);
    }

    /**
     * Scope peminjaman selesai (status dikembalikan)
     */
    public function scopeDikembalikan(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_DIKEMBALIKAN);
    }

    /**
     * Scope peminjaman terlambat (masih dipinjam dan melewati tanggal_jatuh_tempo)
     */
    public function scopeTerlambat(Builder $query): Builder
    {
        $today = now()->startOfDay()->toDateString();
        return $query->where('status', self::STATUS_DIPINJAM)
                     ->where('tanggal_jatuh_tempo', '<', $today);
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
        if ($this->status === self::STATUS_DIPINJAM) {
            return now()->startOfDay()->gt($this->tanggal_jatuh_tempo->startOfDay());
        }

        if ($this->tanggal_kembali) {
            return $this->tanggal_kembali->startOfDay()->gt($this->tanggal_jatuh_tempo->startOfDay());
        }

        return false;
    }

    /**
     * Menghitung selisih hari keterlambatan kalender (minimal 0 hari).
     */
    public function getHariTerlambatAttribute(): int
    {
        $jatuhTempo = $this->tanggal_jatuh_tempo->startOfDay();

        if ($this->status === self::STATUS_DIPINJAM) {
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
        if ($this->status !== self::STATUS_DIPINJAM) {
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
        if ($this->status === self::STATUS_DIKEMBALIKAN) {
            return $this->denda;
        }

        return $this->hitungDendaBerjalan();
    }
}
