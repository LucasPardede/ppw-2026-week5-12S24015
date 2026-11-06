<?php

namespace Tests\Feature;

use App\Models\Buku;
use App\Models\Kategori;
use App\Models\Peminjaman;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SipusDelTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $mahasiswa;
    protected Kategori $kategori;
    protected Buku $buku;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Administrator Del',
            'email' => 'admin@del.ac.id',
            'role' => 'admin',
            'password' => Hash::make('admin123'),
        ]);

        $this->mahasiswa = User::create([
            'name' => 'Lucas Pardede',
            'email' => 'lucas.pardede@del.ac.id',
            'nim' => '12S24015',
            'role' => 'mahasiswa',
            'password' => Hash::make('password123'),
        ]);

        $this->kategori = Kategori::create([
            'kode_kategori' => 'IF-WEB',
            'nama_kategori' => 'Pemrograman Web',
        ]);

        $this->buku = Buku::create([
            'isbn' => '978-602-03-8811-1',
            'judul' => 'Clean Code: A Handbook of Agile Software Craftsmanship',
            'penulis' => 'Robert C. Martin',
            'penerbit' => 'Prentice Hall',
            'tahun_terbit' => 2018,
            'kategori_id' => $this->kategori->id,
            'stok' => 5,
            'sinopsis' => 'Buku standar industri penulisan kode bersih.',
        ]);
    }

    /**
     * UAT-02 & UAT-04: Autentikasi Pengguna & Otorisasi Peran
     */
    public function test_login_mahasiswa_simulasi_berhasil_ke_katalog(): void
    {
        $response = $this->post(route('login.post'), [
            'email' => '12S24015@students.del.ac.id',
            'password' => 'apapun',
        ]);

        $response->assertRedirect(route('katalog.index'));
        $this->assertAuthenticated();
    }

    public function test_login_admin_password_salah_ditolak(): void
    {
        $response = $this->post(route('login.post'), [
            'email' => 'admin@del.ac.id',
            'password' => 'password_salah',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_login_admin_password_benar_masuk_ke_dashboard(): void
    {
        $response = $this->post(route('login.post'), [
            'email' => 'admin@del.ac.id',
            'password' => 'admin123',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($this->admin);
    }

    public function test_mahasiswa_dilarang_mengakses_panel_admin_403(): void
    {
        $response = $this->actingAs($this->mahasiswa)->get(route('admin.dashboard'));
        $response->assertStatus(403);
    }

    /**
     * Kategori CRUD & Validasi & Proteksi Relasi
     */
    public function test_admin_bisa_menambah_kategori(): void
    {
        $response = $this->actingAs($this->admin)->post(route('kategori.store'), [
            'kode_kategori' => 'CS-SEC',
            'nama_kategori' => 'Keamanan Siber',
        ]);

        $response->assertRedirect(route('kategori.index'));
        $this->assertDatabaseHas('kategoris', ['kode_kategori' => 'CS-SEC']);
    }

    public function test_kode_kategori_duplikat_ditolak(): void
    {
        $response = $this->actingAs($this->admin)->post(route('kategori.store'), [
            'kode_kategori' => 'IF-WEB', // duplikat
            'nama_kategori' => 'Kategori Lain',
        ]);

        $response->assertSessionHasErrors('kode_kategori');
    }

    public function test_kategori_tidak_bisa_dihapus_jika_masih_memiliki_buku(): void
    {
        $response = $this->actingAs($this->admin)->delete(route('kategori.destroy', $this->kategori));

        $response->assertRedirect(route('kategori.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('kategoris', ['id' => $this->kategori->id]);
    }

    /**
     * Buku CRUD & Validasi & Proteksi Relasi
     */
    public function test_tambah_buku_validasi_judul_minimal_5_karakter(): void
    {
        $response = $this->actingAs($this->admin)->post(route('buku.store'), [
            'isbn' => '978-111-222-33-4',
            'judul' => 'Abc', // hanya 3 karakter, minimal 5
            'penulis' => 'Penulis',
            'penerbit' => 'Penerbit',
            'tahun_terbit' => 2021,
            'kategori_id' => $this->kategori->id,
            'stok' => 5,
        ]);

        $response->assertSessionHasErrors('judul');
    }

    public function test_admin_bisa_tambah_buku_berhasil(): void
    {
        $response = $this->actingAs($this->admin)->post(route('buku.store'), [
            'isbn' => '978-013-235088-4',
            'judul' => 'Design Patterns Elements',
            'penulis' => 'Gang of Four',
            'penerbit' => 'Addison-Wesley',
            'tahun_terbit' => 2016,
            'kategori_id' => $this->kategori->id,
            'stok' => 3,
            'sinopsis' => 'Katalog pola arsitektur objek.',
        ]);

        $response->assertRedirect(route('buku.index'));
        $this->assertDatabaseHas('bukus', ['isbn' => '978-013-235088-4']);
    }

    public function test_buku_tidak_bisa_dihapus_jika_memiliki_riwayat_peminjaman(): void
    {
        // Buat peminjaman untuk buku ini
        Peminjaman::create([
            'user_id' => $this->mahasiswa->id,
            'buku_id' => $this->buku->id,
            'tanggal_pinjam' => now()->toDateString(),
            'tanggal_jatuh_tempo' => now()->addDays(7)->toDateString(),
            'status' => 'dipinjam',
            'perpanjangan' => 0,
            'denda' => 0,
            'denda_lunas' => false,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('buku.destroy', $this->buku));

        $response->assertRedirect(route('buku.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('bukus', ['id' => $this->buku->id]);
    }

    /**
     * Siklus Peminjaman & Stok & Denda
     */
    public function test_mahasiswa_meminjam_buku_mengurangi_stok(): void
    {
        $stokAwal = $this->buku->stok;

        $response = $this->actingAs($this->mahasiswa)->post(route('katalog.pinjam', $this->buku));

        $response->assertRedirect(route('pinjaman.index'));
        $response->assertSessionHas('success');

        $this->assertEquals($stokAwal - 1, $this->buku->fresh()->stok);
        $this->assertDatabaseHas('peminjamans', [
            'user_id' => $this->mahasiswa->id,
            'buku_id' => $this->buku->id,
            'status' => 'dipinjam',
        ]);
    }

    public function test_mahasiswa_tidak_bisa_meminjam_buku_yang_sama_dua_kali_bersamaan(): void
    {
        // Pinjaman pertama
        $this->actingAs($this->mahasiswa)->post(route('katalog.pinjam', $this->buku));

        // Pinjaman kedua untuk buku yang sama
        $response = $this->actingAs($this->mahasiswa)->post(route('katalog.pinjam', $this->buku));

        $response->assertSessionHas('error');
    }

    public function test_perpanjangan_buku_maksimal_1_kali(): void
    {
        $pinjam = Peminjaman::create([
            'user_id' => $this->mahasiswa->id,
            'buku_id' => $this->buku->id,
            'tanggal_pinjam' => now()->toDateString(),
            'tanggal_jatuh_tempo' => now()->addDays(7)->toDateString(),
            'status' => 'dipinjam',
            'perpanjangan' => 0,
            'denda' => 0,
            'denda_lunas' => false,
        ]);

        // Perpanjangan ke-1
        $response = $this->actingAs($this->mahasiswa)->patch(route('pinjaman.perpanjang', $pinjam));
        $response->assertSessionHas('success');
        $this->assertEquals(1, $pinjam->fresh()->perpanjangan);

        // Perpanjangan ke-2 harus ditolak
        $response2 = $this->actingAs($this->mahasiswa)->patch(route('pinjaman.perpanjang', $pinjam));
        $response2->assertSessionHas('error');
    }

    public function test_admin_mencatat_pengembalian_dan_stok_bertambah(): void
    {
        $stokAwal = $this->buku->stok;

        $pinjam = Peminjaman::create([
            'user_id' => $this->mahasiswa->id,
            'buku_id' => $this->buku->id,
            'tanggal_pinjam' => now()->subDays(10)->toDateString(),
            'tanggal_jatuh_tempo' => now()->subDays(3)->toDateString(), // Terlambat 3 hari
            'status' => 'dipinjam',
            'perpanjangan' => 0,
            'denda' => 0,
            'denda_lunas' => false,
        ]);

        $response = $this->actingAs($this->admin)->patch(route('admin.peminjaman.kembali', $pinjam));

        $response->assertSessionHas('success');
        $this->assertEquals($stokAwal + 1, $this->buku->fresh()->stok);

        $pinjamFresh = $pinjam->fresh();
        $this->assertEquals('dikembalikan', $pinjamFresh->status);
        $this->assertEquals(3000, $pinjamFresh->denda); // 3 hari terlambat x Rp1000
        $this->assertFalse($pinjamFresh->denda_lunas);
    }
}
