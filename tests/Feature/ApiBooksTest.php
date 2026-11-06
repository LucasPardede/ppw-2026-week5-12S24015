<?php

namespace Tests\Feature;

use App\Models\Buku;
use App\Models\Kategori;
use App\Models\Peminjaman;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ApiBooksTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $mahasiswa;
    private Kategori $kategori1;
    private Kategori $kategori2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Pustakawan Del',
            'email' => 'pustakawan@del.ac.id',
            'role' => 'admin',
            'password' => bcrypt('password123'),
        ]);

        $this->mahasiswa = User::create([
            'name' => 'Lucas Pardede',
            'email' => 'lucas.pardede@del.ac.id',
            'nim' => '12S24015',
            'role' => 'mahasiswa',
            'password' => bcrypt('password123'),
        ]);

        $this->kategori1 = Kategori::create([
            'kode_kategori' => 'IF-WEB',
            'nama_kategori' => 'Pemrograman Web',
        ]);

        $this->kategori2 = Kategori::create([
            'kode_kategori' => 'IF-DB',
            'nama_kategori' => 'Basis Data',
        ]);
    }

    public function test_index_books_returns_paginated_collection_with_meta_and_links(): void
    {
        Buku::create([
            'isbn' => '978-013-000001-1',
            'judul' => 'Buku Alpha',
            'penulis' => 'Penulis Alpha',
            'penerbit' => 'Penerbit Alpha',
            'tahun_terbit' => 2021,
            'kategori_id' => $this->kategori1->id,
            'stok' => 5,
        ]);

        $response = $this->getJson('/api/v1/bukus');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                '*' => [
                    'id',
                    'isbn',
                    'judul',
                    'penulis',
                    'penerbit',
                    'tahun_terbit',
                    'stok',
                    'status_stok',
                    'kategori' => ['id', 'kode_kategori', 'nama_kategori'],
                    'total_peminjaman',
                    '_links' => [
                        'self',
                        'collection',
                        'referensi_eksternal',
                    ],
                ],
            ],
            'meta' => [
                'current_page',
                'per_page',
                'total_items',
                'total_pages',
                'links' => ['first', 'prev', 'next', 'last'],
            ],
        ]);
    }

    public function test_index_books_filter_by_search_query(): void
    {
        Buku::create([
            'isbn' => '978-013-111111-1',
            'judul' => 'Laravel Mastery Guide',
            'penulis' => 'Taylor Otwell',
            'penerbit' => 'Taylor Press',
            'tahun_terbit' => 2024,
            'kategori_id' => $this->kategori1->id,
            'stok' => 3,
        ]);

        Buku::create([
            'isbn' => '978-013-222222-2',
            'judul' => 'Database Optimization',
            'penulis' => 'C.J. Date',
            'penerbit' => 'Addison-Wesley',
            'tahun_terbit' => 2020,
            'kategori_id' => $this->kategori2->id,
            'stok' => 4,
        ]);

        $response = $this->getJson('/api/v1/bukus?search=Laravel');
        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('Laravel Mastery Guide', $response->json('data.0.judul'));
    }

    public function test_index_books_filter_by_kategori_id(): void
    {
        Buku::create([
            'isbn' => '978-013-333333-1',
            'judul' => 'Buku Web 1',
            'penulis' => 'Penulis 1',
            'penerbit' => 'Penerbit 1',
            'tahun_terbit' => 2022,
            'kategori_id' => $this->kategori1->id,
            'stok' => 2,
        ]);

        Buku::create([
            'isbn' => '978-013-444444-2',
            'judul' => 'Buku DB 1',
            'penulis' => 'Penulis 2',
            'penerbit' => 'Penerbit 2',
            'tahun_terbit' => 2023,
            'kategori_id' => $this->kategori2->id,
            'stok' => 3,
        ]);

        $response = $this->getJson("/api/v1/bukus?kategori_id={$this->kategori2->id}");
        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('Buku DB 1', $response->json('data.0.judul'));
    }

    public function test_index_books_filter_by_tersedia(): void
    {
        Buku::create([
            'isbn' => '978-013-555555-1',
            'judul' => 'Buku Habis',
            'penulis' => 'Penulis',
            'penerbit' => 'Penerbit',
            'tahun_terbit' => 2022,
            'kategori_id' => $this->kategori1->id,
            'stok' => 0,
        ]);

        Buku::create([
            'isbn' => '978-013-666666-2',
            'judul' => 'Buku Tersedia',
            'penulis' => 'Penulis',
            'penerbit' => 'Penerbit',
            'tahun_terbit' => 2023,
            'kategori_id' => $this->kategori1->id,
            'stok' => 5,
        ]);

        $response = $this->getJson('/api/v1/bukus?tersedia=1');
        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('Buku Tersedia', $response->json('data.0.judul'));
    }

    public function test_index_books_is_free_from_n_plus_1_query_problem(): void
    {
        // Masukkan 3 buku
        for ($i = 1; $i <= 3; $i++) {
            Buku::create([
                'isbn' => "978-000-00000{$i}-0",
                'judul' => "Buku Sample {$i}",
                'penulis' => 'Penulis N1',
                'penerbit' => 'Del Press',
                'tahun_terbit' => 2023,
                'kategori_id' => $this->kategori1->id,
                'stok' => 2,
            ]);
        }

        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->getJson('/api/v1/bukus?per_page=3');
        $queriesFor3 = count(DB::getQueryLog());

        // Tambah 12 buku lagi sehingga total 15
        for ($i = 4; $i <= 15; $i++) {
            Buku::create([
                'isbn' => "978-000-0000" . str_pad((string)$i, 2, '0', STR_PAD_LEFT) . "-0",
                'judul' => "Buku Sample {$i}",
                'penulis' => 'Penulis N1',
                'penerbit' => 'Del Press',
                'tahun_terbit' => 2023,
                'kategori_id' => $this->kategori2->id,
                'stok' => 2,
            ]);
        }

        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->getJson('/api/v1/bukus?per_page=15');
        $queriesFor15 = count(DB::getQueryLog());

        // Jumlah query harus konstan (misal count(*) + select with eager loads)
        $this->assertEquals(
            $queriesFor3,
            $queriesFor15,
            "Terdeteksi masalah N+1: query bertambah dari {$queriesFor3} menjadi {$queriesFor15} ketika item bertambah."
        );
    }

    public function test_show_book_returns_detail_with_kategori_and_anonymized_loans(): void
    {
        $buku = Buku::create([
            'isbn' => '978-013-777777-1',
            'judul' => 'Sistem Informasi Lanjut',
            'penulis' => 'Chandro Pardede',
            'penerbit' => 'Del Press',
            'tahun_terbit' => 2023,
            'kategori_id' => $this->kategori1->id,
            'stok' => 2,
        ]);

        Peminjaman::create([
            'buku_id' => $buku->id,
            'user_id' => $this->mahasiswa->id,
            'nim_peminjam' => '12S24015',
            'nama_peminjam' => 'Lucas Pardede',
            'tanggal_pinjam' => now()->subDays(3)->toDateString(),
            'tanggal_jatuh_tempo' => now()->addDays(4)->toDateString(),
            'status' => 'dipinjam',
            'perpanjangan' => 0,
            'denda' => 0,
            'denda_lunas' => false,
        ]);

        $response = $this->getJson("/api/v1/bukus/{$buku->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'id' => $buku->id,
                'judul' => 'Sistem Informasi Lanjut',
                'kategori' => [
                    'id' => $this->kategori1->id,
                    'kode_kategori' => 'IF-WEB',
                ],
            ],
        ]);

        // Verifikasi privasi data peminjam: NIM disamarkan dan nama tidak terekspos
        $riwayat = $response->json('data.riwayat_peminjaman');
        $this->assertNotEmpty($riwayat);
        $this->assertEquals('12S24***', $riwayat[0]['nim_tersamar']);
        $this->assertArrayNotHasKey('nama_peminjam', $riwayat[0]);
        $this->assertArrayNotHasKey('name', $riwayat[0]);
    }

    public function test_show_book_with_stok_greater_than_zero_includes_pinjam_buku_hateoas_link(): void
    {
        $buku = Buku::create([
            'isbn' => '978-013-888888-1',
            'judul' => 'Buku Tersedia',
            'penulis' => 'Penulis',
            'penerbit' => 'Penerbit',
            'tahun_terbit' => 2022,
            'kategori_id' => $this->kategori1->id,
            'stok' => 3,
        ]);

        $response = $this->getJson("/api/v1/bukus/{$buku->id}");
        $response->assertStatus(200);

        $links = $response->json('data._links');
        $this->assertArrayHasKey('pinjam_buku', $links, 'Link pinjam_buku wajib muncul saat stok > 0');
        $this->assertEquals('POST', $links['pinjam_buku']['method']);
    }

    public function test_show_book_with_stok_zero_omits_pinjam_buku_hateoas_link(): void
    {
        $buku = Buku::create([
            'isbn' => '978-013-999999-1',
            'judul' => 'Buku Habis Stok',
            'penulis' => 'Penulis',
            'penerbit' => 'Penerbit',
            'tahun_terbit' => 2022,
            'kategori_id' => $this->kategori1->id,
            'stok' => 0,
        ]);

        $response = $this->getJson("/api/v1/bukus/{$buku->id}");
        $response->assertStatus(200);

        $links = $response->json('data._links');
        $this->assertArrayNotHasKey('pinjam_buku', $links, 'Link pinjam_buku TIDAK boleh muncul saat stok 0');
    }

    public function test_show_book_not_found_returns_404_problem(): void
    {
        $response = $this->getJson('/api/v1/bukus/99999');

        $response->assertStatus(404);
        $response->assertHeader('Content-Type', 'application/problem+json');
        $response->assertJson([
            'status' => 404,
            'title' => 'Resource Not Found',
        ]);
    }

    public function test_store_book_by_admin_returns_201_with_location_header(): void
    {
        $token = $this->admin->createToken('admin-token')->plainTextToken;

        $payload = [
            'isbn' => '978-602-99999-1-1',
            'judul' => 'Arsitektur RESTful Modern',
            'penulis' => 'Roy Fielding',
            'penerbit' => 'MIT Press',
            'tahun_terbit' => 2024,
            'kategori_id' => $this->kategori1->id,
            'stok' => 5,
            'sinopsis' => 'Panduan mendalam arsitektur REST, semantik HTTP, dan kontrak OpenAPI.',
        ];

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/bukus', $payload);

        $response->assertStatus(201);
        $newId = $response->json('data.id');
        $this->assertNotEmpty($newId);
        $response->assertHeader('Location', url("/api/v1/bukus/{$newId}"));
        $this->assertDatabaseHas('bukus', ['isbn' => '978-602-99999-1-1']);
    }

    public function test_store_book_validation_fails_returns_422_problem(): void
    {
        $token = $this->admin->createToken('admin-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/bukus', [
                'isbn' => '',
                'judul' => 'Abc', // < 5 karakter
            ]);

        $response->assertStatus(422);
        $response->assertHeader('Content-Type', 'application/problem+json');
        $response->assertJsonStructure(['errors' => ['isbn', 'judul']]);
    }

    public function test_store_book_unauthenticated_returns_401(): void
    {
        $response = $this->postJson('/api/v1/bukus', [
            'isbn' => '978-111-22233-4-5',
            'judul' => 'Buku Tanpa Auth',
        ]);

        $response->assertStatus(401);
        $response->assertHeader('Content-Type', 'application/problem+json');
    }

    public function test_store_book_forbidden_for_student_returns_403(): void
    {
        $token = $this->mahasiswa->createToken('student-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/bukus', [
                'isbn' => '978-111-22233-4-5',
                'judul' => 'Buku Dari Mahasiswa',
            ]);

        $response->assertStatus(403);
        $response->assertHeader('Content-Type', 'application/problem+json');
    }

    public function test_put_book_full_replacement_resets_omitted_nullable_fields_to_null(): void
    {
        $token = $this->admin->createToken('admin-token')->plainTextToken;

        $buku = Buku::create([
            'isbn' => '978-013-123456-7',
            'judul' => 'Judul Asli Sebelum PUT',
            'penulis' => 'Penulis Asli',
            'penerbit' => 'Penerbit Asli',
            'tahun_terbit' => 2020,
            'kategori_id' => $this->kategori1->id,
            'stok' => 5,
            'sinopsis' => 'Sinopsis lama yang panjang.',
        ]);

        // Semantik PUT: seluruh atribut wajib dikirim, sinopsis tidak disertakan -> harus direset jadi null
        $payload = [
            'isbn' => '978-013-123456-7',
            'judul' => 'Judul Baru Setelah PUT',
            'penulis' => 'Penulis Baru',
            'penerbit' => 'Penerbit Baru',
            'tahun_terbit' => 2024,
            'kategori_id' => $this->kategori2->id,
            'stok' => 10,
        ];

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson("/api/v1/bukus/{$buku->id}", $payload);

        $response->assertStatus(200);
        $this->assertStringContainsString('PUT', $response->json('message'));
        $this->assertEquals('Judul Baru Setelah PUT', $response->json('data.judul'));
        $this->assertNull($response->json('data.sinopsis'), 'Pada semantik PUT, field sinopsis yang tidak dikirim harus direset ke null.');
    }

    public function test_patch_book_partial_update_preserves_unmentioned_fields(): void
    {
        $token = $this->admin->createToken('admin-token')->plainTextToken;

        $buku = Buku::create([
            'isbn' => '978-013-654321-0',
            'judul' => 'Judul Tidak Berubah',
            'penulis' => 'Penulis Tetap',
            'penerbit' => 'Penerbit Tetap',
            'tahun_terbit' => 2021,
            'kategori_id' => $this->kategori1->id,
            'stok' => 5,
            'sinopsis' => 'Sinopsis tetap aman tidak tersentuh.',
        ]);

        // Hanya mengirim field stok
        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->patchJson("/api/v1/bukus/{$buku->id}", [
                'stok' => 8,
            ]);

        $response->assertStatus(200);
        $this->assertStringContainsString('PATCH', $response->json('message'));
        $this->assertEquals(8, $response->json('data.stok'));
        $this->assertEquals('Judul Tidak Berubah', $response->json('data.judul'));
        $this->assertEquals('Sinopsis tetap aman tidak tersentuh.', $response->json('data.sinopsis'));
    }

    public function test_patch_book_with_empty_payload_returns_400_problem(): void
    {
        $token = $this->admin->createToken('admin-token')->plainTextToken;

        $buku = Buku::create([
            'isbn' => '978-013-987654-3',
            'judul' => 'Judul Buku',
            'penulis' => 'Penulis',
            'penerbit' => 'Penerbit',
            'tahun_terbit' => 2021,
            'kategori_id' => $this->kategori1->id,
            'stok' => 5,
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->patchJson("/api/v1/bukus/{$buku->id}", []);

        $response->assertStatus(400);
        $response->assertHeader('Content-Type', 'application/problem+json');
        $response->assertJson([
            'status' => 400,
            'title' => 'Bad Request',
        ]);
    }

    public function test_delete_book_with_active_loans_returns_409_conflict(): void
    {
        $token = $this->admin->createToken('admin-token')->plainTextToken;

        $buku = Buku::create([
            'isbn' => '978-013-112233-4',
            'judul' => 'Buku Sedang Dipinjam',
            'penulis' => 'Penulis',
            'penerbit' => 'Penerbit',
            'tahun_terbit' => 2022,
            'kategori_id' => $this->kategori1->id,
            'stok' => 1,
        ]);

        Peminjaman::create([
            'buku_id' => $buku->id,
            'user_id' => $this->mahasiswa->id,
            'nim_peminjam' => '12S24015',
            'nama_peminjam' => 'Lucas Pardede',
            'tanggal_pinjam' => now()->toDateString(),
            'tanggal_jatuh_tempo' => now()->addDays(7)->toDateString(),
            'status' => 'dipinjam',
            'perpanjangan' => 0,
            'denda' => 0,
            'denda_lunas' => false,
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->deleteJson("/api/v1/bukus/{$buku->id}");

        $response->assertStatus(409);
        $response->assertHeader('Content-Type', 'application/problem+json');
        $response->assertJson([
            'status' => 409,
            'title' => 'Conflict — Book Has Active Loans',
        ]);
        $this->assertDatabaseHas('bukus', ['id' => $buku->id]);
    }

    public function test_delete_book_with_only_history_loans_returns_409_conflict_with_different_message(): void
    {
        $token = $this->admin->createToken('admin-token')->plainTextToken;

        $buku = Buku::create([
            'isbn' => '978-013-445566-7',
            'judul' => 'Buku Riwayat Saja',
            'penulis' => 'Penulis',
            'penerbit' => 'Penerbit',
            'tahun_terbit' => 2021,
            'kategori_id' => $this->kategori1->id,
            'stok' => 3,
        ]);

        Peminjaman::create([
            'buku_id' => $buku->id,
            'user_id' => $this->mahasiswa->id,
            'nim_peminjam' => '12S24015',
            'nama_peminjam' => 'Lucas Pardede',
            'tanggal_pinjam' => now()->subDays(10)->toDateString(),
            'tanggal_jatuh_tempo' => now()->subDays(3)->toDateString(),
            'tanggal_kembali' => now()->subDays(3)->toDateString(),
            'status' => 'dikembalikan',
            'perpanjangan' => 0,
            'denda' => 0,
            'denda_lunas' => true,
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->deleteJson("/api/v1/bukus/{$buku->id}");

        $response->assertStatus(409);
        $response->assertHeader('Content-Type', 'application/problem+json');
        $response->assertJson([
            'status' => 409,
            'title' => 'Conflict — Book Has Loan History',
        ]);
        $this->assertDatabaseHas('bukus', ['id' => $buku->id]);
    }

    public function test_delete_clean_book_returns_204_no_content(): void
    {
        $token = $this->admin->createToken('admin-token')->plainTextToken;

        $buku = Buku::create([
            'isbn' => '978-013-778899-0',
            'judul' => 'Buku Bersih Tanpa Pinjaman',
            'penulis' => 'Penulis Bersih',
            'penerbit' => 'Penerbit Bersih',
            'tahun_terbit' => 2023,
            'kategori_id' => $this->kategori1->id,
            'stok' => 4,
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->deleteJson("/api/v1/bukus/{$buku->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('bukus', ['id' => $buku->id]);
    }
}
