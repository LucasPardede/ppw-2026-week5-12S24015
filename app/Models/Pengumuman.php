<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Pengumuman extends Model
{
    use HasFactory;

    protected $table = 'pengumumans';

    protected $fillable = [
        'judul',
        'slug',
        'konten',
        'kategori',
        'is_pinned',
        'is_aktif',
        'user_id',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
        'is_aktif' => 'boolean',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($pengumuman) {
            if (empty($pengumuman->slug)) {
                $pengumuman->slug = Str::slug($pengumuman->judul) . '-' . Str::random(5);
            }
        });
    }

    /**
     * Gunakan slug sebagai route key (bukan id).
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Relasi ke pembuat pengumuman (admin / pustakawan).
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Scope untuk pengumuman aktif.
     */
    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('is_aktif', true);
    }

    /**
     * Scope untuk pengumuman yang disematkan (pinned).
     */
    public function scopePinned(Builder $query): Builder
    {
        return $query->where('is_pinned', true);
    }

    /**
     * Badge HTML kategori pengumuman (siap render di Blade).
     */
    public function getKategoriBadgeAttribute(): string
    {
        [$classes, $label, $icon] = match ($this->kategori) {
            'penting'  => ['bg-danger-subtle text-danger',   'Penting & Mendesak',  'exclamation-triangle-fill'],
            'layanan'  => ['bg-info-subtle text-info',        'Layanan & Sirkulasi', 'clock-history'],
            'kegiatan' => ['bg-success-subtle text-success',  'Kegiatan & Literasi', 'calendar-event'],
            default    => ['bg-purple-subtle text-del',       'Info Umum',           'info-circle'],
        };

        return '<span class="badge rounded-pill px-2 py-1 ' . $classes . '" style="font-size:0.72rem;">'
            . '<i class="bi bi-' . $icon . ' me-1"></i>' . htmlspecialchars($label)
            . '</span>';
    }

    /**
     * Label nama kategori.
     */
    public function getKategoriLabelAttribute(): string
    {
        return match ($this->kategori) {
            'penting'  => 'Penting & Mendesak',
            'layanan'  => 'Layanan & Sirkulasi',
            'kegiatan' => 'Kegiatan & Literasi',
            default    => 'Informasi Umum',
        };
    }

    /**
     * Tanggal dibuat dalam format Indonesia.
     */
    public function getFormattedDateAttribute(): string
    {
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];
        return $this->created_at->day . ' ' . $months[$this->created_at->month] . ' ' . $this->created_at->year;
    }
}
