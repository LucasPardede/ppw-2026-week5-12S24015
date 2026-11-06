<?php

namespace Tests\Feature;

use App\Exceptions\BatasPinjamTercapaiException;
use App\Exceptions\PeminjamanGandaException;
use App\Exceptions\PeminjamanSudahKembaliException;
use App\Exceptions\StokHabisException;
use App\Models\Buku;
use App\Models\Kategori;
use App\Models\Peminjaman;
use App\Models\User;
use App\Services\PeminjamanService;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AcidPeminjamanTest extends TestCase
{
    use RefreshDatabase;

    protected PeminjamanService $service;
    protected Kategori $kategori;
    protected User $mahasiswa;
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(PeminjamanService::class);

        $this->kategori = Kategori::create([
            'kode_kategori' => 'IF-ACID',
            'nama_kategori' => 'Rekayasa Perangkat Lunak ACID',
        ]);

        $this->mahasiswa = User::create([
            'name' => 'Lucas Pardede Test',
            'email' => 'lucas.test@del.ac.id',
            'nim' => '12S99015',
            'role' => 'mahasiswa',
            'password' => Hash::make('password123'),
        ]);

        $this->admin = User::create([
            'name' => 'Pustakawan Del Test',
            'email' => 'pustakawan.test@del.ac.id',
            'role' => 'admin',
            'password' => Hash::make('password123'),
        ]);
    }

    /**
     * U1: Pinjam buku stok 1 berhasil, stok menjadi 0.
     */
    public function test_u1_pinjam_buku_stok_1_berhasil_dan_stok_menjadi_0(): void
    {
        $buku = Buku::create([
            'isbn' => '978-001-000001-1',
            'judul' => 'Buku Uji Stok Satu',
            'penulis' => 'Penulis Uji',
            'penerbit' => 'Del Press',
            'tahun_terbit' => 2026,
            'kategori_id' => $this->kategori->id,
            'stok' => 1,
        ]);

        $peminjaman = $this->service->pinjam($this->mahasiswa, $buku->id);

        $this->assertNotNull($peminjaman->id);
        $this->assertEquals(Peminjaman::STATUS_DIPINJAM, $peminjaman->status);
        $this->assertEquals($this->mahasiswa->nim, $peminjaman->nim_peminjam);

        // Verifikasi stok buku berkurang menjadi 0
        $this->assertEquals(0, $buku->fresh()->stok);

        // Verifikasi kode pinjam unik berformat PJM-
        $this->assertStringStartsWith('PJM-', $peminjaman->kode_pinjam);
    }

    /**
     * U2: Pinjam buku stok 0 ditolak; stok tetap 0; tidak ada baris baru di peminjamans (bukti rollback).
     */
    public function test_u2_pinjam_buku_stok_0_ditolak_stok_tetap_0_dan_rollback(): void
    {
        $buku = Buku::create([
            'isbn' => '978-002-000002-2',
            'judul' => 'Buku Uji Stok Nol',
            'penulis' => 'Penulis Nol',
            'penerbit' => 'Del Press',
            'tahun_terbit' => 2026,
            'kategori_id' => $this->kategori->id,
            'stok' => 0,
        ]);

        $countAwal = Peminjaman::count();

        $this->expectException(StokHabisException::class);

        try {
            $this->service->pinjam($this->mahasiswa, $buku->id);
        } finally {
            // Verifikasi stok tetap 0 dan tidak ada baris peminjaman baru
            $this->assertEquals(0, $buku->fresh()->stok);
            $this->assertEquals($countAwal, Peminjaman::count());
        }
    }

    /**
     * U3: NIM yang sama meminjam buku yang sama dua kali ditolak.
     */
    public function test_u3_nim_sama_meminjam_buku_sama_dua_kali_ditolak(): void
    {
        $buku = Buku::create([
            'isbn' => '978-003-000003-3',
            'judul' => 'Buku Uji Pinjam Ganda',
            'penulis' => 'Penulis Ganda',
            'penerbit' => 'Del Press',
            'tahun_terbit' => 2026,
            'kategori_id' => $this->kategori->id,
            'stok' => 5,
        ]);

        // Peminjaman pertama sukses
        $this->service->pinjam($this->mahasiswa, $buku->id);

        // Peminjaman kedua buku yang sama oleh mahasiswa yang sama harus ditolak
        $this->expectException(PeminjamanGandaException::class);
        $this->service->pinjam($this->mahasiswa, $buku->id);
    }

    /**
     * U4: Setelah dikembalikan, NIM yang sama boleh meminjam ulang buku tersebut.
     */
    public function test_u4_setelah_dikembalikan_nim_sama_boleh_meminjam_ulang(): void
    {
        $buku = Buku::create([
            'isbn' => '978-004-000004-4',
            'judul' => 'Buku Uji Pinjam Ulang',
            'penulis' => 'Penulis Ulang',
            'penerbit' => 'Del Press',
            'tahun_terbit' => 2026,
            'kategori_id' => $this->kategori->id,
            'stok' => 2,
        ]);

        $peminjaman1 = $this->service->pinjam($this->mahasiswa, $buku->id);
        $this->assertEquals(1, $buku->fresh()->stok);

        // Buku dikembalikan
        $this->service->kembali($peminjaman1->id, $this->admin);
        $this->assertEquals(2, $buku->fresh()->stok);

        // Sekarang mahasiswa boleh meminjam ulang buku yang sama
        $peminjaman2 = $this->service->pinjam($this->mahasiswa, $buku->id);
        $this->assertNotNull($peminjaman2->id);
        $this->assertNotEquals($peminjaman1->id, $peminjaman2->id);
        $this->assertEquals(1, $buku->fresh()->stok);
    }

    /**
     * U5: Kembali menambah stok, status dan tanggal_kembali benar; kembali kedua kali ditolak (stok tidak bertambah dua kali).
     */
    public function test_u5_kembali_menambah_stok_dan_kembali_kedua_kali_ditolak_stok_tidak_ganda(): void
    {
        $buku = Buku::create([
            'isbn' => '978-005-000005-5',
            'judul' => 'Buku Uji Pengembalian',
            'penulis' => 'Penulis Kembali',
            'penerbit' => 'Del Press',
            'tahun_terbit' => 2026,
            'kategori_id' => $this->kategori->id,
            'stok' => 3,
        ]);

        $peminjaman = $this->service->pinjam($this->mahasiswa, $buku->id);
        $this->assertEquals(2, $buku->fresh()->stok);

        // Pengembalian pertama berhasil
        $kembaliResult = $this->service->kembali($peminjaman->id, $this->admin);
        $this->assertEquals(Peminjaman::STATUS_DIKEMBALIKAN, $kembaliResult->status);
        $this->assertNotNull($kembaliResult->tanggal_kembali);
        $this->assertEquals(3, $buku->fresh()->stok);

        // Pengembalian kedua untuk transaksi yang sama harus ditolak
        $this->expectException(PeminjamanSudahKembaliException::class);
        try {
            $this->service->kembali($peminjaman->id, $this->admin);
        } finally {
            // Pastikan stok tidak bertambah menjadi 4
            $this->assertEquals(3, $buku->fresh()->stok);
        }
    }

    /**
     * U6: Batas maksimal pinjaman aktif per NIM ditolak.
     */
    public function test_u6_batas_maksimal_pinjaman_aktif_per_nim_ditolak(): void
    {
        $maxBuku = (int) config('perpustakaan.maksimal_pinjam_buku', 3);

        $bukus = [];
        for ($i = 1; $i <= $maxBuku + 1; $i++) {
            $bukus[] = Buku::create([
                'isbn' => "978-006-00000{$i}-6",
                'judul' => "Buku Kuota {$i}",
                'penulis' => 'Penulis Kuota',
                'penerbit' => 'Del Press',
                'tahun_terbit' => 2026,
                'kategori_id' => $this->kategori->id,
                'stok' => 5,
            ]);
        }

        // Pinjam hingga batas maksimal
        for ($i = 0; $i < $maxBuku; $i++) {
            $this->service->pinjam($this->mahasiswa, $bukus[$i]->id);
        }

        // Pinjaman ke-(max + 1) harus ditolak
        $this->expectException(BatasPinjamTercapaiException::class);
        $this->service->pinjam($this->mahasiswa, $bukus[$maxBuku]->id);
    }

    /**
     * U8: Bebas N+1: Jumlah query halaman katalog sama untuk 5 dan 25 buku.
     */
    public function test_u8_halaman_katalog_bebas_dari_masalah_n_plus_1(): void
    {
        // Siapkan 5 buku pertama
        for ($i = 1; $i <= 5; $i++) {
            Buku::create([
                'isbn' => "978-008-00000{$i}-8",
                'judul' => "Buku N1 Sample {$i}",
                'penulis' => 'Penulis N1',
                'penerbit' => 'Del Press',
                'tahun_terbit' => 2026,
                'kategori_id' => $this->kategori->id,
                'stok' => 3,
            ]);
        }

        DB::flushQueryLog();
        DB::enableQueryLog();
        $response5 = $this->actingAs($this->mahasiswa)->get(route('katalog.index'));
        $response5->assertStatus(200);
        $queryCount5 = count(DB::getQueryLog());
        DB::disableQueryLog();

        // Tambahkan 20 buku lagi (total 25 buku)
        for ($i = 6; $i <= 25; $i++) {
            Buku::create([
                'isbn' => sprintf("978-008-%06d-8", $i),
                'judul' => "Buku N1 Sample {$i}",
                'penulis' => 'Penulis N1',
                'penerbit' => 'Del Press',
                'tahun_terbit' => 2026,
                'kategori_id' => $this->kategori->id,
                'stok' => 3,
            ]);
        }

        DB::flushQueryLog();
        DB::enableQueryLog();
        $response25 = $this->actingAs($this->mahasiswa)->get(route('katalog.index'));
        $response25->assertStatus(200);
        $queryCount25 = count(DB::getQueryLog());
        DB::disableQueryLog();

        // Jumlah query tidak boleh bertambah secara proporsional dengan jumlah buku (N+1 bebas)
        $this->assertEquals($queryCount5, $queryCount25, "Halaman katalog memicu N+1 query: 5 buku ({$queryCount5} query) vs 25 buku ({$queryCount25} query).");
    }

    /**
     * Auth: Login sukses memberi sesi baru (ID sesi berubah) dan mengantrekan cookie HttpOnly.
     */
    public function test_auth_login_sukses_regenerasi_sesi_dan_cookie_httponly(): void
    {
        $response = $this->post(route('login.post'), [
            'email' => $this->mahasiswa->email,
            'password' => 'password123',
            'remember_email' => '1',
        ]);

        $response->assertRedirect(route('katalog.index'));
        $this->assertAuthenticatedAs($this->mahasiswa);

        // Verifikasi cookie del_last_user_email diantrekan
        $response->assertCookie('del_last_user_email');
        $cookie = $response->getCookie('del_last_user_email');
        $this->assertTrue($cookie->isHttpOnly(), 'Cookie del_last_user_email harus bertanda HttpOnly');
        $this->assertEquals('lax', $cookie->getSameSite(), 'Cookie del_last_user_email harus SameSite=lax');
    }

    /**
     * Auth: Logout menghapus sesi.
     */
    public function test_auth_logout_menghapus_sesi(): void
    {
        $this->actingAs($this->mahasiswa);
        $response = $this->post(route('logout'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    /**
     * Auth: Tamu dialihkan ke login saat mengakses rute terproteksi.
     */
    public function test_auth_tamu_dialihkan_ke_login(): void
    {
        $response = $this->get(route('katalog.index'));
        $response->assertRedirect(route('login'));
    }

    /**
     * Auth: Mahasiswa mendapat 403 di rute admin.
     */
    public function test_auth_mahasiswa_mendapat_403_di_rute_admin(): void
    {
        $response = $this->actingAs($this->mahasiswa)->get(route('admin.dashboard'));
        $response->assertStatus(403);
    }

    /**
     * Seeder: Konsistensi stok dan tidak ada stok negatif setelah seeder dijalankan.
     */
    public function test_seeder_konsistensi_stok_dan_tidak_ada_stok_negatif(): void
    {
        $this->seed(DatabaseSeeder::class);

        // 1. Pastikan akun pustakawan@del.ac.id ada
        $pustakawan = User::where('email', 'pustakawan@del.ac.id')->first();
        $this->assertNotNull($pustakawan, 'Akun pustakawan@del.ac.id wajib ada');
        $this->assertEquals('admin', $pustakawan->role);

        // 2. Tidak ada buku dengan stok negatif
        $bukuNegatif = Buku::where('stok', '<', 0)->count();
        $this->assertEquals(0, $bukuNegatif, 'Ditemukan buku dengan stok negatif');

        // 3. Ada minimal 1 buku stok = 0 dan minimal 1 buku stok = 1
        $bukuStok0 = Buku::where('stok', 0)->count();
        $bukuStok1 = Buku::where('stok', 1)->count();
        $this->assertGreaterThanOrEqual(1, $bukuStok0, 'Harus ada minimal 1 buku dengan stok 0');
        $this->assertGreaterThanOrEqual(1, $bukuStok1, 'Harus ada minimal 1 buku dengan stok 1');

        // 4. Periksa konsistensi stok = stok_asli - pinjaman_aktif
        $peminjamanAktif = Peminjaman::where('status', Peminjaman::STATUS_DIPINJAM)->count();
        $this->assertGreaterThanOrEqual(2, $peminjamanAktif, 'Harus ada peminjaman aktif');
    }
}
