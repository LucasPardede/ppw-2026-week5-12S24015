<?php

namespace Tests\Feature;

use App\Models\Pengumuman;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PengumumanTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $mahasiswa;

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
    }

    /**
     * Mahasiswa dapat melihat daftar pengumuman yang aktif.
     */
    public function test_mahasiswa_can_view_active_announcements(): void
    {
        $pengumuman = Pengumuman::create([
            'judul' => 'Jadwal Layanan Perpustakaan Periode UTS Semester Genap',
            'slug' => 'jadwal-layanan-perpustakaan-uts',
            'konten' => 'Perpustakaan buka hingga pukul 22.00 WIB selama masa ujian.',
            'kategori' => 'layanan',
            'is_pinned' => true,
            'is_aktif' => true,
            'user_id' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->mahasiswa)
            ->get(route('pengumuman.index'));

        $response->assertStatus(200);
        $response->assertSee('Jadwal Layanan Perpustakaan Periode UTS');
        $response->assertSee('Papan Pengumuman SIPUS-Del');
    }

    /**
     * Mahasiswa dapat melihat detail pengumuman aktif berdasarkan slug.
     */
    public function test_mahasiswa_can_view_announcement_detail(): void
    {
        $pengumuman = Pengumuman::create([
            'judul' => 'Pengembalian Buku Wajib Sebelum Pengisian KRS',
            'slug' => 'pengembalian-buku-wajib-krs',
            'konten' => 'Seluruh mahasiswa diwajibkan mengembalikan buku pinjaman.',
            'kategori' => 'penting',
            'is_pinned' => true,
            'is_aktif' => true,
            'user_id' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->mahasiswa)
            ->get(route('pengumuman.show', $pengumuman->slug));

        $response->assertStatus(200);
        $response->assertSee('Pengembalian Buku Wajib Sebelum Pengisian KRS');
        $response->assertSee('Seluruh mahasiswa diwajibkan mengembalikan');
    }

    /**
     * Mahasiswa tidak dapat melihat pengumuman berstatus draft (is_aktif = false).
     */
    public function test_mahasiswa_cannot_view_inactive_announcement(): void
    {
        $draft = Pengumuman::create([
            'judul' => 'Draft Pengumuman Rahasia Pengadaan Buku',
            'slug' => 'draft-pengumuman-rahasia',
            'konten' => 'Konten ini masih dalam proses peninjauan oleh kepala perpustakaan.',
            'kategori' => 'info',
            'is_pinned' => false,
            'is_aktif' => false,
            'user_id' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->mahasiswa)
            ->get(route('pengumuman.show', $draft->slug));

        $response->assertStatus(404);
    }

    /**
     * Mahasiswa dilarang mengakses panel kelola pengumuman admin.
     */
    public function test_mahasiswa_forbidden_from_admin_announcement_routes(): void
    {
        $response = $this->actingAs($this->mahasiswa)
            ->get(route('admin.pengumuman.index'));

        $response->assertStatus(403);
    }

    /**
     * Admin dapat melihat daftar kelola pengumuman.
     */
    public function test_admin_can_view_announcement_management_index(): void
    {
        Pengumuman::create([
            'judul' => 'Sosialisasi E-Journal IEEE Xplore',
            'slug' => 'sosialisasi-ejournal-ieee',
            'konten' => 'Sosialisasi akses database jurnal IEEE untuk mahasiswa tingkat akhir.',
            'kategori' => 'kegiatan',
            'is_pinned' => false,
            'is_aktif' => true,
            'user_id' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.pengumuman.index'));

        $response->assertStatus(200);
        $response->assertSee('Kelola Pengumuman &amp; Edaran', false);
        $response->assertSee('Sosialisasi E-Journal IEEE Xplore');
    }

    /**
     * Admin dapat membuat pengumuman baru.
     */
    public function test_admin_can_create_new_announcement(): void
    {
        $payload = [
            'judul' => 'Workshop Literasi Digital 2026',
            'konten' => 'Pelatihan penulisan karya ilmiah dan sitasi menggunakan Mendeley di Lab Komputer 1.',
            'kategori' => 'kegiatan',
            'is_pinned' => '1',
            'is_aktif' => '1',
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.pengumuman.store'), $payload);

        $response->assertRedirect(route('admin.pengumuman.index'));
        $this->assertDatabaseHas('pengumumans', [
            'judul' => 'Workshop Literasi Digital 2026',
            'kategori' => 'kegiatan',
            'is_pinned' => true,
            'is_aktif' => true,
        ]);
    }

    /**
     * Admin dapat memperbarui pengumuman yang sudah ada.
     */
    public function test_admin_can_update_announcement(): void
    {
        $pengumuman = Pengumuman::create([
            'judul' => 'Judul Awal Pengumuman',
            'slug' => 'judul-awal-pengumuman',
            'konten' => 'Konten awal sebelum dilakukan penyuntingan oleh administrator.',
            'kategori' => 'info',
            'is_pinned' => false,
            'is_aktif' => true,
            'user_id' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->put(route('admin.pengumuman.update', $pengumuman->id), [
                'judul' => 'Judul Pengumuman Diperbarui',
                'konten' => 'Konten ini sudah diperbarui dengan informasi tambahan yang valid.',
                'kategori' => 'penting',
                'is_pinned' => '1',
                'is_aktif' => '1',
            ]);

        $response->assertRedirect(route('admin.pengumuman.index'));
        $this->assertDatabaseHas('pengumumans', [
            'id' => $pengumuman->id,
            'judul' => 'Judul Pengumuman Diperbarui',
            'kategori' => 'penting',
            'is_pinned' => true,
        ]);
    }

    /**
     * Admin dapat menghapus pengumuman.
     */
    public function test_admin_can_delete_announcement(): void
    {
        $pengumuman = Pengumuman::create([
            'judul' => 'Pengumuman Sementara Yang Akan Dihapus',
            'slug' => 'pengumuman-sementara-dihapus',
            'konten' => 'Konten sementara yang sudah tidak relevan.',
            'kategori' => 'info',
            'is_pinned' => false,
            'is_aktif' => true,
            'user_id' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.pengumuman.destroy', $pengumuman->id));

        $response->assertRedirect(route('admin.pengumuman.index'));
        $this->assertDatabaseMissing('pengumumans', [
            'id' => $pengumuman->id,
        ]);
    }

    /**
     * Admin dapat melakukan toggle sematan (pin/unpin) pengumuman.
     */
    public function test_admin_can_toggle_pin_announcement(): void
    {
        $pengumuman = Pengumuman::create([
            'judul' => 'Pengumuman Status Sematan',
            'slug' => 'pengumuman-status-sematan',
            'konten' => 'Pengujian fungsionalitas pin dan unpin oleh admin.',
            'kategori' => 'layanan',
            'is_pinned' => false,
            'is_aktif' => true,
            'user_id' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->patch(route('admin.pengumuman.pin', $pengumuman->id));

        $response->assertRedirect();
        $this->assertTrue($pengumuman->fresh()->is_pinned);

        // Toggle kembali ke unpin
        $this->actingAs($this->admin)
            ->patch(route('admin.pengumuman.pin', $pengumuman->id));

        $this->assertFalse($pengumuman->fresh()->is_pinned);
    }
}
