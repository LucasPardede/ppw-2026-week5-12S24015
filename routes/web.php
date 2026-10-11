<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BukuController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KatalogController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\MahasiswaController;
use App\Http\Controllers\PeminjamanController;
use App\Http\Controllers\PengumumanController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - SIPUS-Del (Sistem Informasi Perpustakaan Kampus Del)
|--------------------------------------------------------------------------
| Modul Praktikum Minggu 05 & Dokumen SyRS v2.0
| Mengimplementasikan 7 Aksi Baku CRUD RESTful Resource Controller,
| Route Model Binding, Validasi Server-Side, dan Middleware Otorisasi.
*/

// Rute Beranda Publik: Mengarahkan pengguna berdasarkan status autentikasi dan peran
Route::get('/', function () {
    if (!Auth::check()) {
        return redirect()->route('login');
    }

    return Auth::user()->role === 'admin'
        ? redirect()->route('admin.dashboard')
        : redirect()->route('katalog.index');
});

// Autentikasi Pengguna (CIS Mock / Lokal Admin)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// ==========================================
// RUTE PORTAL MAHASISWA (Role: Mahasiswa)
// ==========================================
Route::middleware(['auth', 'role:mahasiswa'])->group(function () {
    // Katalog Buku
    Route::get('/katalog', [KatalogController::class, 'index'])->name('katalog.index');
    Route::get('/katalog/{buku}', [KatalogController::class, 'show'])->name('katalog.show');
    Route::post('/katalog/{buku}/pinjam', [PeminjamanController::class, 'pinjam'])->name('katalog.pinjam');

    // Transaksi Peminjaman Mahasiswa
    Route::get('/pinjaman', [PeminjamanController::class, 'indexMahasiswa'])->name('pinjaman.index');
    Route::patch('/pinjaman/{peminjaman}/perpanjang', [PeminjamanController::class, 'perpanjang'])->name('pinjaman.perpanjang');

    // Pengumuman Perpustakaan untuk Mahasiswa
    Route::get('/pengumuman', [PengumumanController::class, 'indexMahasiswa'])->name('pengumuman.index');
    Route::get('/pengumuman/{pengumuman}', [PengumumanController::class, 'showMahasiswa'])->name('pengumuman.show');
});

// ==========================================
// RUTE PANEL ADMINISTRATOR (Role: Admin)
// ==========================================
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin', fn() => redirect()->route('admin.dashboard'));
    Route::get('/admin/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

    // 7 Aksi Baku CRUD RESTful Resource Controller (Sesuai Spesifikasi Modul)
    Route::resource('kategori', KategoriController::class);
    Route::resource('buku', BukuController::class);

    // Manajemen Transaksi Peminjaman (Admin)
    Route::get('/admin/peminjaman', [PeminjamanController::class, 'indexAdmin'])->name('admin.peminjaman.index');
    Route::patch('/admin/peminjaman/{peminjaman}/kembali', [PeminjamanController::class, 'kembalikan'])->name('admin.peminjaman.kembali');
    Route::patch('/admin/peminjaman/{peminjaman}/lunas', [PeminjamanController::class, 'tandaiLunas'])->name('admin.peminjaman.lunas');

    // Pemantauan Data Mahasiswa Peminjam
    Route::get('/admin/mahasiswa', [MahasiswaController::class, 'index'])->name('admin.mahasiswa.index');
    Route::get('/admin/mahasiswa/{user}', [MahasiswaController::class, 'show'])->name('admin.mahasiswa.show');

    // Manajemen Pengumuman Perpustakaan (Admin)
    Route::get('/admin/pengumuman', [PengumumanController::class, 'indexAdmin'])->name('admin.pengumuman.index');
    Route::get('/admin/pengumuman/create', [PengumumanController::class, 'create'])->name('admin.pengumuman.create');
    Route::post('/admin/pengumuman', [PengumumanController::class, 'store'])->name('admin.pengumuman.store');
    Route::get('/admin/pengumuman/{pengumuman:id}', [PengumumanController::class, 'showAdmin'])->name('admin.pengumuman.show');
    Route::get('/admin/pengumuman/{pengumuman:id}/edit', [PengumumanController::class, 'edit'])->name('admin.pengumuman.edit');
    Route::put('/admin/pengumuman/{pengumuman:id}', [PengumumanController::class, 'update'])->name('admin.pengumuman.update');
    Route::delete('/admin/pengumuman/{pengumuman:id}', [PengumumanController::class, 'destroy'])->name('admin.pengumuman.destroy');
    Route::patch('/admin/pengumuman/{pengumuman:id}/toggle-pin', [PengumumanController::class, 'togglePin'])->name('admin.pengumuman.pin');

    // Laporan dan Ekspor Data
    Route::get('/admin/laporan', [LaporanController::class, 'index'])->name('admin.laporan.index');
    Route::get('/admin/laporan/export', [LaporanController::class, 'exportCsv'])->name('admin.laporan.export');
});
