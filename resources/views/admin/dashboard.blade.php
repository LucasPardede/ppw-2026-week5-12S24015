<x-layout title="Dashboard Administrator — SIPUS-Del">
    <div class="page-header-del d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h1 class="fw-bold mb-2 fs-2">
                <i class="bi bi-speedometer2 text-warning me-2"></i>Dashboard Manajemen Perpustakaan
            </h1>
            <p class="mb-0 text-white-50">
                Pusat kendali operasional SIPUS-Del • Institut Teknologi Del.
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('buku.create') }}" class="btn btn-warning fw-semibold rounded-pill px-3 shadow-sm">
                <i class="bi bi-plus-circle me-1"></i> Tambah Buku
            </a>
            <a href="{{ route('kategori.create') }}" class="btn btn-outline-light rounded-pill px-3">
                <i class="bi bi-folder-plus me-1"></i> Tambah Kategori
            </a>
        </div>
    </div>

    {{-- Kartu Ringkasan Metrik --}}
    <div class="row g-3 mb-4">
        {{-- Total Koleksi Buku --}}
        <div class="col-sm-6 col-lg-2">
            <div class="card card-del border-0 shadow-sm p-3 h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold text-uppercase">Total Buku</span>
                    <i class="bi bi-book fs-4 text-del"></i>
                </div>
                <h3 class="fw-bold mb-0 text-dark">{{ $totalBuku }}</h3>
                <span class="text-muted small mt-1">Judul Terdaftar</span>
            </div>
        </div>

        {{-- Total Kategori --}}
        <div class="col-sm-6 col-lg-2">
            <div class="card card-del border-0 shadow-sm p-3 h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold text-uppercase">Kategori</span>
                    <i class="bi bi-tags fs-4 text-primary"></i>
                </div>
                <h3 class="fw-bold mb-0 text-dark">{{ $totalKategori }}</h3>
                <span class="text-muted small mt-1">Klasifikasi</span>
            </div>
        </div>

        {{-- Total Mahasiswa --}}
        <div class="col-sm-6 col-lg-2">
            <div class="card card-del border-0 shadow-sm p-3 h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold text-uppercase">Mahasiswa</span>
                    <i class="bi bi-people fs-4 text-info"></i>
                </div>
                <h3 class="fw-bold mb-0 text-dark">{{ $totalMahasiswa }}</h3>
                <span class="text-muted small mt-1">Akun Terdaftar</span>
            </div>
        </div>

        {{-- Pinjaman Aktif --}}
        <div class="col-sm-6 col-lg-2">
            <div class="card card-del border-0 shadow-sm p-3 h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold text-uppercase">Dipinjam</span>
                    <i class="bi bi-arrow-repeat fs-4 text-warning"></i>
                </div>
                <h3 class="fw-bold mb-0 text-dark">{{ $pinjamanAktif }}</h3>
                <span class="text-muted small mt-1">Sirkulasi Aktif</span>
            </div>
        </div>

        {{-- Terlambat --}}
        <div class="col-sm-6 col-lg-2">
            <div class="card card-del border-0 shadow-sm p-3 h-100 bg-danger-subtle bg-opacity-25">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-danger small fw-semibold text-uppercase">Terlambat</span>
                    <i class="bi bi-exclamation-triangle-fill fs-4 text-danger"></i>
                </div>
                <h3 class="fw-bold mb-0 text-danger">{{ $pinjamanTerlambat }}</h3>
                <span class="text-danger small mt-1">Melewati Jatuh Tempo</span>
            </div>
        </div>

        {{-- Denda Belum Lunas --}}
        <div class="col-sm-6 col-lg-2">
            <div class="card card-del border-0 shadow-sm p-3 h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold text-uppercase">Denda</span>
                    <i class="bi bi-cash-stack fs-4 text-success"></i>
                </div>
                <h5 class="fw-bold mb-0 text-danger font-monospace">Rp{{ number_format($dendaBelumLunas, 0, ',', '.') }}</h5>
                <span class="text-muted small mt-1">Belum Lunas</span>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        {{-- 5 Buku Terpopuler --}}
        <div class="col-lg-6">
            <div class="card card-del border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-semibold text-secondary">
                        <i class="bi bi-trophy-fill text-warning me-2"></i>5 Koleksi Buku Terpopuler
                    </h5>
                    <span class="badge badge-del px-2 py-1 small">withCount('peminjamans')</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light text-secondary text-uppercase" style="font-size: 0.75rem;">
                            <tr>
                                <th class="ps-3">Judul Buku</th>
                                <th>Kategori</th>
                                <th class="text-center">Dipinjam</th>
                                <th class="text-end pe-3">Stok Saat Ini</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($bukuPopuler as $buku)
                                <tr>
                                    <td class="ps-3 fw-semibold">
                                        <a href="{{ route('buku.show', $buku) }}" class="text-dark text-decoration-none">
                                            {{ $buku->judul }}
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge badge-del">{{ $buku->kategori->nama_kategori ?? '-' }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1 fw-bold">
                                            {{ $buku->peminjamans_count }} Kali
                                        </span>
                                    </td>
                                    <td class="text-end pe-3">
                                        @if($buku->stok > 3)
                                            <span class="text-success fw-semibold">{{ $buku->stok }} eks</span>
                                        @elseif($buku->stok >= 1)
                                            <span class="text-warning fw-semibold">{{ $buku->stok }} eks</span>
                                        @else
                                            <span class="text-danger fw-semibold">Habis</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">Belum ada statistik peminjaman.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Peringatan Stok Menipis --}}
        <div class="col-lg-6">
            <div class="card card-del border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-semibold text-secondary">
                        <i class="bi bi-exclamation-circle-fill text-warning me-2"></i>Peringatan Stok Menipis (&le; 3 Eksemplar)
                    </h5>
                    <a href="{{ route('buku.index') }}" class="btn btn-outline-del btn-sm rounded-pill px-2 py-0">Lihat Semua</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light text-secondary text-uppercase" style="font-size: 0.75rem;">
                            <tr>
                                <th class="ps-3">Judul Buku</th>
                                <th>Kategori</th>
                                <th class="text-center">Sisa Stok</th>
                                <th class="text-end pe-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($stokMenipis as $item)
                                <tr>
                                    <td class="ps-3 fw-semibold">
                                        <a href="{{ route('buku.show', $item) }}" class="text-dark text-decoration-none">
                                            {{ $item->judul }}
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge badge-del">{{ $item->kategori->nama_kategori ?? '-' }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if($item->stok === 0)
                                            <span class="badge bg-danger rounded-pill px-2 py-1">0 (Habis)</span>
                                        @else
                                            <span class="badge bg-warning text-dark rounded-pill px-2 py-1">{{ $item->stok }} Unit</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-3">
                                        <a href="{{ route('buku.edit', $item) }}" class="btn btn-outline-primary btn-sm rounded-pill px-2 py-0">
                                            Tambah Stok
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-success">
                                        <i class="bi bi-check-circle me-1"></i> Semua koleksi buku memiliki stok aman (> 3).
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Peminjaman Terkini --}}
    <div class="card card-del border-0 shadow-sm overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0 fw-semibold text-secondary">
                <i class="bi bi-clock-history me-2 text-del"></i>5 Transaksi Peminjaman Terbaru
            </h5>
            <a href="{{ route('admin.peminjaman.index') }}" class="btn btn-outline-del btn-sm rounded-pill px-3">
                Kelola Semua Transaksi
            </a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light text-secondary text-uppercase" style="font-size: 0.75rem;">
                    <tr>
                        <th class="ps-3">Peminjam</th>
                        <th>Judul Buku</th>
                        <th>Tanggal Pinjam</th>
                        <th>Jatuh Tempo</th>
                        <th>Status</th>
                        <th class="text-end pe-3">Denda Berjalan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($peminjamanTerbaru as $tx)
                        <tr>
                            <td class="ps-3">
                                <span class="fw-semibold text-dark">{{ $tx->user->name ?? 'Mahasiswa' }}</span>
                                <div class="text-muted" style="font-size: 0.75rem;">{{ $tx->user->nim ?? $tx->user->email }}</div>
                            </td>
                            <td class="fw-semibold">
                                <a href="{{ route('buku.show', $tx->buku) }}" class="text-dark text-decoration-none">
                                    {{ $tx->buku->judul }}
                                </a>
                            </td>
                            <td>{{ $tx->tanggal_pinjam->format('d/m/Y') }}</td>
                            <td>{{ $tx->jatuh_tempo->format('d/m/Y') }}</td>
                            <td>
                                @if($tx->status === 'dipinjam')
                                    @if($tx->is_terlambat)
                                        <span class="badge bg-danger rounded-pill">Terlambat ({{ $tx->hari_terlambat }} hr)</span>
                                    @else
                                        <span class="badge bg-primary-subtle text-primary border rounded-pill">Dipinjam</span>
                                    @endif
                                @else
                                    <span class="badge bg-success-subtle text-success border rounded-pill">Dikembalikan</span>
                                @endif
                            </td>
                            <td class="text-end pe-3 font-monospace">
                                @if($tx->denda_efektif > 0)
                                    <span class="text-danger fw-semibold">
                                        Rp{{ number_format($tx->denda_efektif, 0, ',', '.') }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">Belum ada transaksi peminjaman.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layout>
