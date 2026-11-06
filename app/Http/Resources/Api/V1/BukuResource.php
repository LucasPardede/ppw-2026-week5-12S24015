<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BukuResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $links = [
            'self' => [
                'href' => url("/api/v1/bukus/{$this->id}"),
                'method' => 'GET',
            ],
            'collection' => [
                'href' => url('/api/v1/bukus'),
                'method' => 'GET',
            ],
            'referensi_eksternal' => [
                'href' => url("/api/v1/bukus/{$this->id}/referensi-eksternal"),
                'method' => 'GET',
            ],
        ];

        // HATEOAS: Link peminjaman HANYA muncul jika stok buku masih tersedia (> 0)
        if ($this->stok > 0) {
            $links['pinjam_buku'] = [
                'href' => url('/api/v1/peminjamans'),
                'method' => 'POST',
            ];
        }

        return [
            'id' => $this->id,
            'isbn' => $this->isbn,
            'judul' => $this->judul,
            'penulis' => $this->penulis,
            'penerbit' => $this->penerbit,
            'tahun_terbit' => (int) $this->tahun_terbit,
            'stok' => (int) $this->stok,
            'status_stok' => $this->status_stok,
            'sinopsis' => $this->sinopsis,
            'kategori' => new KategoriResource($this->whenLoaded('kategori')),
            'total_peminjaman' => $this->whenCounted('peminjamans', fn () => $this->peminjamans_count),
            'riwayat_peminjaman' => $this->whenLoaded('peminjamans', function () {
                return $this->peminjamans->map(function ($p) {
                    $nim = $p->nim_peminjam ?: ($p->user?->nim ?? '');
                    $len = strlen($nim);
                    $nimTersamar = $len > 4 ? substr($nim, 0, 5) . str_repeat('*', max(3, $len - 5)) : '***';

                    return [
                        'id' => $p->id,
                        'kode_pinjam' => $p->kode_pinjam,
                        'tanggal_pinjam' => $p->tanggal_pinjam?->toDateString(),
                        'jatuh_tempo' => $p->tanggal_jatuh_tempo?->toDateString(),
                        'tanggal_kembali' => $p->tanggal_kembali?->toDateString(),
                        'status' => $p->status,
                        'nim_tersamar' => $nimTersamar,
                    ];
                });
            }),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            '_links' => $links,
        ];
    }
}
