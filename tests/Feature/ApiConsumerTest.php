<?php

namespace Tests\Feature;

use App\Models\Buku;
use App\Models\Kategori;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ApiConsumerTest extends TestCase
{
    use RefreshDatabase;

    private Buku $buku;
    private Kategori $kategori;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();

        $this->kategori = Kategori::create([
            'kode_kategori' => 'IF-WEB',
            'nama_kategori' => 'Pemrograman Web',
        ]);

        $this->buku = Buku::create([
            'isbn' => '978-013-235088-4',
            'judul' => 'Clean Code: A Handbook of Agile Software Craftsmanship',
            'penulis' => 'Robert C. Martin',
            'penerbit' => 'Prentice Hall',
            'tahun_terbit' => 2018,
            'kategori_id' => $this->kategori->id,
            'stok' => 4,
            'sinopsis' => 'Panduan menulis kode bersih.',
        ]);
    }

    public function test_referensi_eksternal_success_fetches_and_maps_openlibrary_metadata(): void
    {
        $cleanIsbn = '9780132350884';

        Http::fake([
            'https://openlibrary.org/api/books*' => Http::response([
                "ISBN:{$cleanIsbn}" => [
                    'url' => 'https://openlibrary.org/books/OL26222911M/Clean_Code',
                    'title' => 'Clean Code',
                    'publishers' => [
                        ['name' => 'Prentice Hall'],
                    ],
                    'number_of_pages' => 431,
                    'publish_date' => 'July 2008',
                    'ebooks' => [
                        ['preview_url' => 'https://archive.org/details/cleancodehandboo0000unse'],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->getJson("/api/v1/bukus/{$this->buku->id}/referensi-eksternal");

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'buku_lokal' => [
                    'id' => $this->buku->id,
                    'isbn' => '978-013-235088-4',
                    'judul' => 'Clean Code: A Handbook of Agile Software Craftsmanship',
                ],
                'referensi_eksternal' => [
                    'judul_eksternal' => 'Clean Code',
                    'penerbit' => 'Prentice Hall',
                    'jumlah_halaman' => 431,
                    'tanggal_terbit' => 'July 2008',
                    'url_pratinjau' => 'https://openlibrary.org/books/OL26222911M/Clean_Code',
                    'sumber' => 'OpenLibrary',
                ],
            ],
        ]);

        // Verifikasi hasil disimpan di cache
        $this->assertTrue(Cache::has("buku:referensi:{$cleanIsbn}"));
    }

    public function test_referensi_eksternal_upstream_404_returns_404_problem(): void
    {
        Http::fake([
            'https://openlibrary.org/api/books*' => Http::response([], 200),
        ]);

        $response = $this->getJson("/api/v1/bukus/{$this->buku->id}/referensi-eksternal");

        $response->assertStatus(404);
        $response->assertHeader('Content-Type', 'application/problem+json');
        $response->assertJson([
            'status' => 404,
            'title' => 'Resource Not Found',
        ]);

        // Tidak boleh dicache saat gagal
        $cleanIsbn = '9780132350884';
        $this->assertFalse(Cache::has("buku:referensi:{$cleanIsbn}"));
    }

    public function test_referensi_eksternal_upstream_timeout_returns_504_problem(): void
    {
        Http::fake([
            'https://openlibrary.org/api/books*' => fn () => throw new ConnectionException('cURL error 28: Operation timed out'),
        ]);

        $response = $this->getJson("/api/v1/bukus/{$this->buku->id}/referensi-eksternal");

        $response->assertStatus(504);
        $response->assertHeader('Content-Type', 'application/problem+json');
        $response->assertJson([
            'status' => 504,
            'title' => 'Gateway Timeout',
        ]);
    }

    public function test_referensi_eksternal_upstream_500_returns_502_problem(): void
    {
        Http::fake([
            'https://openlibrary.org/api/books*' => Http::response(['error' => 'Internal server error'], 500),
        ]);

        $response = $this->getJson("/api/v1/bukus/{$this->buku->id}/referensi-eksternal");

        $response->assertStatus(502);
        $response->assertHeader('Content-Type', 'application/problem+json');
        $response->assertJson([
            'status' => 502,
            'title' => 'Bad Gateway',
        ]);
    }

    public function test_referensi_eksternal_local_book_not_found_returns_404_problem(): void
    {
        $response = $this->getJson('/api/v1/bukus/99999/referensi-eksternal');

        $response->assertStatus(404);
        $response->assertHeader('Content-Type', 'application/problem+json');
        $response->assertJson([
            'status' => 404,
            'title' => 'Resource Not Found',
        ]);
    }

    public function test_referensi_eksternal_missing_upstream_fields_mapped_defensively_to_null(): void
    {
        $cleanIsbn = '9780132350884';

        Http::fake([
            'https://openlibrary.org/api/books*' => Http::response([
                "ISBN:{$cleanIsbn}" => [
                    'title' => 'Clean Code (Minimal)',
                ],
            ], 200),
        ]);

        $response = $this->getJson("/api/v1/bukus/{$this->buku->id}/referensi-eksternal");

        $response->assertStatus(200);
        $ref = $response->json('data.referensi_eksternal');
        $this->assertEquals('Clean Code (Minimal)', $ref['judul_eksternal']);
        $this->assertNull($ref['penerbit']);
        $this->assertNull($ref['jumlahHalaman'] ?? $ref['jumlah_halaman']);
        $this->assertNull($ref['tanggal_terbit']);
        $this->assertNull($ref['url_pratinjau']);
        $this->assertEquals('OpenLibrary', $ref['sumber']);
    }
}
