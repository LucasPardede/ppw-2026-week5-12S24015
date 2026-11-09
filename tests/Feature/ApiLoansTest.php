<?php

namespace Tests\Feature;

use App\Models\Buku;
use App\Models\Kategori;
use App\Models\Peminjaman;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiLoansTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $mahasiswa;
    private Buku $buku;
    private Kategori $kategori;

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

        $this->kategori = Kategori::create([
            'kode_kategori' => 'IF-WEB',
            'nama_kategori' => 'Pemrograman Web',
        ]);

        $this->buku = Buku::create([
            'isbn' => '978-013-111222-3',
            'judul' => 'Clean Code Architecture',
            'penulis' => 'Robert C. Martin',
            'penerbit' => 'Prentice Hall',
            'tahun_terbit' => 2020,
            'kategori_id' => $this->kategori->id,
            'stok' => 2,
        ]);
    }

    public function test_borrow_book_success_reduces_stock_and_returns_201_with_location(): void
    {
        $token = $this->admin->createToken('admin-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/peminjamans', [
                'buku_id' => $this->buku->id,
                'nim_peminjam' => '12S24015',
                'nama_peminjam' => 'Lucas Pardede',
                'durasi_hari' => 7,
            ]);

        $response->assertStatus(201);
        $newId = $response->json('data.id');
        $this->assertNotEmpty($newId);
        $response->assertHeader('Location', url("/api/v1/peminjamans/{$newId}"));
        $this->assertEquals(1, $this->buku->fresh()->stok, 'Stok buku harus berkurang tepat 1.');
        $this->assertEquals('dipinjam', $response->json('data.status'));
        $this->assertEquals('12S24015', $response->json('data.peminjam.nim'));
        $this->assertArrayHasKey('kembalikan_buku', $response->json('data._links'));
    }

    public function test_borrow_book_when_stock_is_zero_returns_422_problem_and_stock_remains(): void
    {
        $token = $this->admin->createToken('admin-token')->plainTextToken;
        $this->buku->update(['stok' => 0]);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/peminjamans', [
                'buku_id' => $this->buku->id,
                'nim_peminjam' => '12S24015',
                'nama_peminjam' => 'Lucas Pardede',
                'durasi_hari' => 7,
            ]);

        $response->assertStatus(422);
        $response->assertHeader('Content-Type', 'application/problem+json');
        $response->assertJson([
            'status' => 422,
            'title' => 'Unprocessable Content',
            'detail' => 'Stok buku sedang habis.',
        ]);
        $this->assertEquals(0, $this->buku->fresh()->stok);
    }

    public function test_borrow_same_book_twice_returns_409_conflict(): void
    {
        $token = $this->admin->createToken('admin-token')->plainTextToken;

        // Pinjaman pertama berhasil
        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/peminjamans', [
                'buku_id' => $this->buku->id,
                'nim_peminjam' => '12S24015',
                'nama_peminjam' => 'Lucas Pardede',
                'durasi_hari' => 7,
            ])->assertStatus(201);

        // Pinjaman kedua untuk buku yang sama oleh NIM yang sama harus 409
        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/peminjamans', [
                'buku_id' => $this->buku->id,
                'nim_peminjam' => '12S24015',
                'nama_peminjam' => 'Lucas Pardede',
                'durasi_hari' => 7,
            ]);

        $response->assertStatus(409);
        $response->assertHeader('Content-Type', 'application/problem+json');
        $response->assertJson([
            'status' => 409,
            'title' => 'Duplicate Loan Conflict',
        ]);
        $this->assertEquals(1, $this->buku->fresh()->stok, 'Stok buku tidak boleh berkurang pada request yang ditolak.');
    }

    public function test_borrow_exceeding_max_active_loans_limit_returns_422(): void
    {
        $token = $this->admin->createToken('admin-token')->plainTextToken;
        $maxLimit = (int) config('perpustakaan.maksimal_pinjam_buku', 3);

        // Buat 3 buku lain dan pinjamkan ke mahasiswa ini hingga limit
        for ($i = 1; $i <= $maxLimit; $i++) {
            $otherBook = Buku::create([
                'isbn' => "978-000-00000{$i}-9",
                'judul' => "Buku Lain {$i}",
                'penulis' => 'Penulis',
                'penerbit' => 'Penerbit',
                'tahun_terbit' => 2021,
                'kategori_id' => $this->kategori->id,
                'stok' => 5,
            ]);

            Peminjaman::create([
                'buku_id' => $otherBook->id,
                'user_id' => $this->mahasiswa->id,
                'nim_peminjam' => $this->mahasiswa->nim,
                'nama_peminjam' => $this->mahasiswa->name,
                'tanggal_pinjam' => now()->toDateString(),
                'tanggal_jatuh_tempo' => now()->addDays(7)->toDateString(),
                'status' => Peminjaman::STATUS_DIPINJAM,
                'perpanjangan' => 0,
                'denda' => 0,
                'denda_lunas' => false,
            ]);
        }

        // Coba pinjam buku ke-(limit + 1)
        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/peminjamans', [
                'buku_id' => $this->buku->id,
                'nim_peminjam' => $this->mahasiswa->nim,
                'nama_peminjam' => $this->mahasiswa->name,
                'durasi_hari' => 7,
            ]);

        $response->assertStatus(422);
        $response->assertHeader('Content-Type', 'application/problem+json');
        $response->assertJson([
            'status' => 422,
            'title' => 'Max Loan Limit Reached',
        ]);
    }

    public function test_borrow_when_student_has_overdue_loan_returns_409(): void
    {
        $token = $this->admin->createToken('admin-token')->plainTextToken;

        $overdueBook = Buku::create([
            'isbn' => '978-000-999999-9',
            'judul' => 'Buku Terlambat',
            'penulis' => 'Penulis',
            'penerbit' => 'Penerbit',
            'tahun_terbit' => 2021,
            'kategori_id' => $this->kategori->id,
            'stok' => 5,
        ]);

        // Pinjaman aktif yang sudah lewat jatuh tempo
        Peminjaman::create([
            'buku_id' => $overdueBook->id,
            'user_id' => $this->mahasiswa->id,
            'nim_peminjam' => $this->mahasiswa->nim,
            'nama_peminjam' => $this->mahasiswa->name,
            'tanggal_pinjam' => now()->subDays(10)->toDateString(),
            'tanggal_jatuh_tempo' => now()->subDays(3)->toDateString(),
            'status' => Peminjaman::STATUS_DIPINJAM,
            'perpanjangan' => 0,
            'denda' => 0,
            'denda_lunas' => false,
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/peminjamans', [
                'buku_id' => $this->buku->id,
                'nim_peminjam' => $this->mahasiswa->nim,
                'nama_peminjam' => $this->mahasiswa->name,
                'durasi_hari' => 7,
            ]);

        $response->assertStatus(409);
        $response->assertHeader('Content-Type', 'application/problem+json');
        $response->assertJson([
            'status' => 409,
            'title' => 'Conflict — Overdue Loan Exists',
        ]);
    }

    public function test_borrow_when_student_has_unpaid_fine_returns_409(): void
    {
        $token = $this->admin->createToken('admin-token')->plainTextToken;

        $pastBook = Buku::create([
            'isbn' => '978-000-888888-8',
            'judul' => 'Buku Riwayat Denda',
            'penulis' => 'Penulis',
            'penerbit' => 'Penerbit',
            'tahun_terbit' => 2021,
            'kategori_id' => $this->kategori->id,
            'stok' => 5,
        ]);

        // Pinjaman sudah dikembalikan tetapi denda belum lunas
        Peminjaman::create([
            'buku_id' => $pastBook->id,
            'user_id' => $this->mahasiswa->id,
            'nim_peminjam' => $this->mahasiswa->nim,
            'nama_peminjam' => $this->mahasiswa->name,
            'tanggal_pinjam' => now()->subDays(15)->toDateString(),
            'tanggal_jatuh_tempo' => now()->subDays(8)->toDateString(),
            'tanggal_kembali' => now()->subDays(5)->toDateString(),
            'status' => Peminjaman::STATUS_DIKEMBALIKAN,
            'perpanjangan' => 0,
            'denda' => 3000,
            'denda_lunas' => false,
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/peminjamans', [
                'buku_id' => $this->buku->id,
                'nim_peminjam' => $this->mahasiswa->nim,
                'nama_peminjam' => $this->mahasiswa->name,
                'durasi_hari' => 7,
            ]);

        $response->assertStatus(409);
        $response->assertHeader('Content-Type', 'application/problem+json');
        $response->assertJson([
            'status' => 409,
            'title' => 'Conflict — Unpaid Fine Exists',
        ]);
    }

    public function test_rollback_creates_no_orphan_user_when_borrowing_fails(): void
    {
        $token = $this->admin->createToken('admin-token')->plainTextToken;
        $this->buku->update(['stok' => 0]); // Buat gagal di cek stok

        $newNim = '12S24099';
        $this->assertNull(User::where('nim', $newNim)->first());

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/peminjamans', [
                'buku_id' => $this->buku->id,
                'nim_peminjam' => $newNim,
                'nama_peminjam' => 'Mahasiswa Baru Gagal',
                'durasi_hari' => 7,
            ]);

        $response->assertStatus(422);
        // Pastikan tidak ada user yatim yang terlanjur terbuat di database
        $this->assertNull(User::where('nim', $newNim)->first(), 'User baru tidak boleh dibuat jika transaksi peminjaman gagal.');
    }

    public function test_return_book_success_increases_stock_and_calculates_fine_correctly(): void
    {
        $token = $this->admin->createToken('admin-token')->plainTextToken;

        // Pinjaman terlambat 3 hari
        $loan = Peminjaman::create([
            'buku_id' => $this->buku->id,
            'user_id' => $this->mahasiswa->id,
            'nim_peminjam' => $this->mahasiswa->nim,
            'nama_peminjam' => $this->mahasiswa->name,
            'tanggal_pinjam' => now()->subDays(10)->toDateString(),
            'tanggal_jatuh_tempo' => now()->subDays(3)->toDateString(),
            'status' => Peminjaman::STATUS_DIPINJAM,
            'perpanjangan' => 0,
            'denda' => 0,
            'denda_lunas' => false,
        ]);

        $initialStock = $this->buku->stok;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->patchJson("/api/v1/peminjamans/{$loan->id}/kembalikan");

        $response->assertStatus(200);
        $this->assertEquals('dikembalikan', $response->json('data.status'));
        $this->assertEquals(3, $response->json('data.hari_terlambat'));
        $this->assertEquals(3000, $response->json('data.denda'), 'Denda terlambat 3 hari * Rp1.000 harus 3000.');
        $this->assertFalse($response->json('data.denda_lunas'));
        $this->assertEquals($initialStock + 1, $this->buku->fresh()->stok, 'Stok buku harus bertambah 1 setelah pengembalian.');

        // Link kembalikan_buku harus hilang setelah dikembalikan (HATEOAS dinamis)
        $this->assertArrayNotHasKey('kembalikan_buku', $response->json('data._links'));
    }

    public function test_return_book_twice_returns_409_conflict_and_stock_does_not_increase_again(): void
    {
        $token = $this->admin->createToken('admin-token')->plainTextToken;

        $loan = Peminjaman::create([
            'buku_id' => $this->buku->id,
            'user_id' => $this->mahasiswa->id,
            'nim_peminjam' => $this->mahasiswa->nim,
            'nama_peminjam' => $this->mahasiswa->name,
            'tanggal_pinjam' => now()->subDays(5)->toDateString(),
            'tanggal_jatuh_tempo' => now()->addDays(2)->toDateString(),
            'status' => Peminjaman::STATUS_DIPINJAM,
            'perpanjangan' => 0,
            'denda' => 0,
            'denda_lunas' => false,
        ]);

        // Pengembalian pertama berhasil
        $this->withHeader('Authorization', "Bearer {$token}")
            ->patchJson("/api/v1/peminjamans/{$loan->id}/kembalikan")
            ->assertStatus(200);

        $stockAfterFirstReturn = $this->buku->fresh()->stok;

        // Pengembalian kedua harus ditolak 409
        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->patchJson("/api/v1/peminjamans/{$loan->id}/kembalikan");

        $response->assertStatus(409);
        $response->assertHeader('Content-Type', 'application/problem+json');
        $response->assertJson([
            'status' => 409,
            'title' => 'Conflict — Already Returned',
        ]);
        $this->assertEquals($stockAfterFirstReturn, $this->buku->fresh()->stok, 'Stok tidak boleh bertambah dua kali.');
    }

    public function test_circulation_endpoints_unauthenticated_return_401(): void
    {
        $this->getJson('/api/v1/peminjamans')->assertStatus(401);
        $this->postJson('/api/v1/peminjamans', [])->assertStatus(401);
        $this->patchJson('/api/v1/peminjamans/1/kembalikan')->assertStatus(401);
    }

    public function test_circulation_endpoints_forbidden_for_students_return_403(): void
    {
        $token = $this->mahasiswa->createToken('student-token')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/peminjamans')
            ->assertStatus(403);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/peminjamans', [])
            ->assertStatus(403);
    }

    public function test_index_loans_filter_by_status_and_nim(): void
    {
        $token = $this->admin->createToken('admin-token')->plainTextToken;

        Peminjaman::create([
            'buku_id' => $this->buku->id,
            'user_id' => $this->mahasiswa->id,
            'nim_peminjam' => $this->mahasiswa->nim,
            'nama_peminjam' => $this->mahasiswa->name,
            'tanggal_pinjam' => now()->toDateString(),
            'tanggal_jatuh_tempo' => now()->addDays(7)->toDateString(),
            'status' => Peminjaman::STATUS_DIPINJAM,
            'perpanjangan' => 0,
            'denda' => 0,
            'denda_lunas' => false,
        ]);

        // Filter valid
        $res = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/peminjamans?status=dipinjam&nim=12S24015');
        $res->assertStatus(200);
        $this->assertCount(1, $res->json('data'));

        // Filter status tidak valid harus 422 Problem
        $resInvalid = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/peminjamans?status=hilang');
        $resInvalid->assertStatus(422);
        $resInvalid->assertHeader('Content-Type', 'application/problem+json');
    }
}
