<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'nim',
        'role',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Relasi One-to-Many dengan Peminjaman.
     */
    public function peminjamans(): HasMany
    {
        return $this->hasMany(Peminjaman::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isMahasiswa(): bool
    {
        return $this->role === 'mahasiswa';
    }

    /**
     * Menghitung total denda yang belum lunas (baik denda tersimpan maupun denda berjalan).
     */
    public function totalDendaBelumLunas(): int
    {
        // 1. Denda tersimpan dari buku yang sudah dikembalikan tapi belum lunas
        $dendaSelesai = $this->peminjamans()
            ->where('status', 'dikembalikan')
            ->where('denda_lunas', false)
            ->sum('denda');

        // 2. Denda berjalan dari buku yang masih dipinjam dan telah lewat jatuh tempo
        $pinjamanAktif = $this->peminjamans()
            ->where('status', 'dipinjam')
            ->get();

        $dendaBerjalan = 0;
        foreach ($pinjamanAktif as $p) {
            $dendaBerjalan += $p->hitungDendaBerjalan();
        }

        return (int) ($dendaSelesai + $dendaBerjalan);
    }

    /**
     * Cek apakah mahasiswa memiliki pinjaman yang terlambat.
     */
    public function memilikiPinjamanTerlambat(): bool
    {
        $today = now()->startOfDay()->toDateString();
        return $this->peminjamans()
            ->where('status', 'dipinjam')
            ->where('tanggal_jatuh_tempo', '<', $today)
            ->exists();
    }
}
