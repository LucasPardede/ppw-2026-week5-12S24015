<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Peminjaman;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PeminjamanResource extends JsonResource
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
                'href' => url("/api/v1/peminjamans/{$this->id}"),
                'method' => 'GET',
            ],
            'buku' => [
                'href' => url("/api/v1/bukus/{$this->buku_id}"),
                'method' => 'GET',
            ],
        ];

        // HATEOAS: Link kembalikan_buku HANYA muncul jika status peminjaman masih 'dipinjam'
        if ($this->status === Peminjaman::STATUS_DIPINJAM) {
            $links['kembalikan_buku'] = [
                'href' => url("/api/v1/peminjamans/{$this->id}/kembalikan"),
                'method' => 'PATCH',
            ];
        }

        $bukuRingkas = null;
        if ($this->buku) {
            $bukuRingkas = [
                'id' => $this->buku->id,
                'isbn' => $this->buku->isbn,
                'judul' => $this->buku->judul,
                'penulis' => $this->buku->penulis,
                'kategori' => $this->buku->kategori?->nama_kategori,
            ];
        }

        return [
            'id' => $this->id,
            'kode_pinjam' => $this->kode_pinjam,
            'status' => $this->status,
            'tanggal_pinjam' => $this->tanggal_pinjam?->toDateString(),
            'jatuh_tempo' => $this->tanggal_jatuh_tempo?->toDateString(),
            'tanggal_kembali' => $this->tanggal_kembali?->toDateString(),
            'perpanjangan' => (int) $this->perpanjangan,
            'hari_terlambat' => (int) $this->hari_terlambat,
            'denda' => (int) $this->denda_efektif,
            'denda_lunas' => (bool) $this->denda_lunas,
            'buku' => $bukuRingkas,
            'peminjam' => [
                'nim' => $this->nim_peminjam ?: ($this->user?->nim ?? '-'),
                'nama' => $this->nama_peminjam ?: ($this->user?->name ?? 'Mahasiswa Del'),
            ],
            'created_at' => $this->created_at?->toIso8601String(),
            '_links' => $links,
        ];
    }
}

