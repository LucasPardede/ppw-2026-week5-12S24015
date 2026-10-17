<x-layout title="Dashboard Administrator — SIPUS-Del">

    {{-- Page Header --}}
    <div class="page-header-del d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <p class="text-white-50 small mb-1">
                <i class="bi bi-house-door me-1" aria-hidden="true"></i>
                Admin Panel &rsaquo; Dashboard
            </p>
            <h1 class="fw-bold mb-1 fs-2">
                <i class="bi bi-speedometer2 text-warning me-2" aria-hidden="true"></i>Dashboard Manajemen
            </h1>
            <p class="mb-0 text-white-50 small">
                Pusat kendali operasional SIPUS-Del &bull; Institut Teknologi Del
                <span class="ms-2 badge rounded-pill text-white" style="font-size: 0.7rem; background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.25);">
                    Update: {{ now()->format('d M Y, H:i') }}
                </span>
            </p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('admin.pengumuman.create') }}" class="btn btn-warning fw-semibold rounded-pill px-3 shadow-sm">
                <i class="bi bi-megaphone-fill me-1" aria-hidden="true"></i> Buat Pengumuman
            </a>
            <a href="{{ route('buku.create') }}" class="btn btn-light fw-semibold rounded-pill px-3 shadow-sm">
                <i class="bi bi-plus-circle me-1" aria-hidden="true"></i> Tambah Buku
            </a>
            <a href="{{ route('kategori.create') }}" class="btn btn-outline-light rounded-pill px-3">
                <i class="bi bi-folder-plus me-1" aria-hidden="true"></i> Tambah Kategori
            </a>
        </div>
    </div>

    {{-- ===== STAT CARDS ===== --}}
    <div class="row g-3 mb-4">
        {{-- Total Buku --}}
        <div class="col-6 col-md-4 col-lg-2">
            <div class="stat-card purple text-center shadow-sm">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="rounded-2 p-2 d-inline-flex" style="background: #EDE9FE;">
                        <i class="bi bi-book fs-5 text-del" aria-hidden="true"></i>
                    </div>
                    <a href="{{ route('buku.index') }}" class="btn btn-link p-0 text-muted" style="font-size: 0.75rem;" title="Kelola Buku">
                        <i class="bi bi-arrow-right-circle"></i>
                    </a>
                </div>
                <div class="fw-bold fs-3 text-dark lh-1 mb-1">{{ $totalBuku }}</div>
                <div class="text-muted" style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Total Buku</div>
                <div class="text-muted" style="font-size: 0.7rem;">Judul Terdaftar</div>
            </div>
        </div>

        {{-- Total Kategori --}}
        <div class="col-6 col-md-4 col-lg-2">
            <div class="stat-card blue text-center shadow-sm">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="rounded-2 p-2 d-inline-flex" style="background: #DBEAFE;">
                        <i class="bi bi-tags fs-5 text-primary" aria-hidden="true"></i>
                    </div>
                    <a href="{{ route('kategori.index') }}" class="btn btn-link p-0 text-muted" style="font-size: 0.75rem;" title="Kelola Kategori">
                        <i class="bi bi-arrow-right-circle"></i>
                    </a>
                </div>
                <div class="fw-bold fs-3 text-dark lh-1 mb-1">{{ $totalKategori }}</div>
                <div class="text-muted" style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Kategori</div>
                <div class="text-muted" style="font-size: 0.7rem;">Klasifikasi</div>
            </div>
        </div>

        {{-- Total Mahasiswa --}}
        <div class="col-6 col-md-4 col-lg-2">
            <div class="stat-card teal text-center shadow-sm">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="rounded-2 p-2 d-inline-flex" style="background: #CCFBF1;">
                        <i class="bi bi-people fs-5 text-info" aria-hidden="true"></i>
                    </div>
                    <a href="{{ route('admin.mahasiswa.index') }}" class="btn btn-link p-0 text-muted" style="font-size: 0.75rem;" title="Data Mahasiswa">
                        <i class="bi bi-arrow-right-circle"></i>
                    </a>
                </div>
                <div class="fw-bold fs-3 text-dark lh-1 mb-1">{{ $totalMahasiswa }}</div>
                <div class="text-muted" style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Mahasiswa</div>
                <div class="text-muted" style="font-size: 0.7rem;">Akun Terdaftar</div>
            </div>
        </div>

        {{-- Pinjaman Aktif --}}
        <div class="col-6 col-md-4 col-lg-2">
            <div class="stat-card amber text-center shadow-sm">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="rounded-2 p-2 d-inline-flex" style="background: #FEF3C7;">
                        <i class="bi bi-arrow-repeat fs-5 text-warning" aria-hidden="true"></i>
                    </div>
                    <a href="{{ route('admin.peminjaman.index') }}" class="btn btn-link p-0 text-muted" style="font-size: 0.75rem;" title="Data Peminjaman">
                        <i class="bi bi-arrow-right-circle"></i>
                    </a>
                </div>
                <div class="fw-bold fs-3 text-dark lh-1 mb-1">{{ $pinjamanAktif }}</div>
                <div class="text-muted" style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Dipinjam</div>
                <div class="text-muted" style="font-size: 0.7rem;">Sirkulasi Aktif</div>
            </div>
        </div>

        {{-- Terlambat --}}
        <div class="col-6 col-md-4 col-lg-2">
            <div class="stat-card red text-center shadow-sm" style="background: #FFF5F5;">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="rounded-2 p-2 d-inline-flex" style="background: #FEE2E2;">
                        <i class="bi bi-exclamation-triangle-fill fs-5 text-danger" aria-hidden="true"></i>
                    </div>
                    <a href="{{ route('admin.peminjaman.index') }}?status=terlambat" class="btn btn-link p-0 text-muted" style="font-size: 0.75rem;" title="Lihat yang terlambat">
                        <i class="bi bi-arrow-right-circle"></i>
                    </a>
                </div>
                <div class="fw-bold fs-3 text-danger lh-1 mb-1">{{ $pinjamanTerlambat }}</div>
                <div class="text-danger" style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Terlambat</div>
                <div class="text-muted" style="font-size: 0.7rem;">Melewati Jatuh Tempo</div>
            </div>
        </div>

        {{-- Denda Belum Lunas --}}
        <div class="col-6 col-md-4 col-lg-2">
            <div class="stat-card green text-center shadow-sm">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="rounded-2 p-2 d-inline-flex" style="background: #DCFCE7;">
                        <i class="bi bi-cash-stack fs-5 text-success" aria-hidden="true"></i>
                    </div>
                    <a href="{{ route('admin.laporan.index') }}" class="btn btn-link p-0 text-muted" style="font-size: 0.75rem;" title="Laporan">
                        <i class="bi bi-arrow-right-circle"></i>
                    </a>
                </div>
                <div class="fw-bold text-danger lh-1 mb-1" style="font-size: 1.1rem;">
                    Rp{{ number_format($dendaBelumLunas, 0, ',', '.') }}
                </div>
                <div class="text-muted" style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Denda</div>
                <div class="text-muted" style="font-size: 0.7rem;">Belum Lunas</div>
            </div>
        </div>
    </div>

    {{-- ===== TABEL: Buku Populer & Stok Menipis ===== --}}
    <div class="row g-4 mb-4">

        {{-- 5 Buku Terpopuler --}}
        <div class="col-lg-6">
            <div class="card card-del border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h2 class="h6 card-title mb-0 fw-semibold text-secondary">
                        <i class="bi bi-trophy-fill text-warning me-2" aria-hidden="true"></i>5 Koleksi Buku Terpopuler
                    </h2>
                    <span class="badge badge-del px-2 py-1" style="font-size: 0.7rem;">Berdasarkan total pinjaman</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small" aria-label="5 buku terpopuler">
                        <thead class="table-light text-secondary text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.4px;">
                            <tr>
                                <th class="ps-3">#</th>
                                <th>Judul Buku</th>
                                <th>Kategori</th>
                                <th class="text-center">Dipinjam</th>
                                <th class="text-end pe-3">Stok</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($bukuPopuler as $i => $buku)
                                <tr>
                                    <td class="ps-3">
                                        @if($i === 0)
                                            <i class="bi bi-trophy-fill text-warning"></i>
                                        @elseif($i === 1)
                                            <i class="bi bi-trophy-fill text-secondary"></i>
                                        @elseif($i === 2)
                                            <i class="bi bi-trophy-fill" style="color: #CD7F32;"></i>
                                        @else
                                            <span class="text-muted fw-semibold">{{ $i + 1 }}</span>
                                        @endif
                                    </td>
                                    <td class="fw-semibold">
                                        <a href="{{ route('buku.show', $buku->id ?? $buku) }}" class="text-dark text-decoration-none">
                                            {{ $buku->judul }}
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge badge-del">{{ $buku->nama_kategori ?? $buku->kategori->nama_kategori ?? '-' }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1 fw-bold">
                                            {{ $buku->peminjamans_count }}×
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
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        <i class="bi bi-bar-chart d-block fs-3 mb-1 opacity-50" aria-hidden="true"></i>
                                        Belum ada statistik peminjaman.
                                    </td>
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
                    <h2 class="h6 card-title mb-0 fw-semibold text-secondary">
                        <i class="bi bi-exclamation-circle-fill text-warning me-2" aria-hidden="true"></i>Peringatan Stok Menipis (&le; 3 Eks)
                    </h2>
                    <a href="{{ route('buku.index') }}" class="btn btn-outline-del btn-sm rounded-pill px-2 py-0" style="font-size: 0.75rem;">
                        Lihat Semua
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small" aria-label="Buku dengan stok menipis">
                        <thead class="table-light text-secondary text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.4px;">
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
                                        <a href="{{ route('buku.edit', $item) }}" class="btn btn-outline-primary btn-sm rounded-pill px-2 py-0" style="font-size: 0.75rem;">
                                            <i class="bi bi-plus-circle me-1" aria-hidden="true"></i>Tambah Stok
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-success">
                                        <i class="bi bi-check-circle-fill d-block fs-3 mb-1" aria-hidden="true"></i>
                                        Semua koleksi buku memiliki stok aman (&gt; 3).
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== Peminjaman Terkini ===== --}}
    <div class="card card-del border-0 shadow-sm overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h2 class="h6 card-title mb-0 fw-semibold text-secondary">
                <i class="bi bi-clock-history me-2 text-del" aria-hidden="true"></i>5 Transaksi Peminjaman Terbaru
            </h2>
            <a href="{{ route('admin.peminjaman.index') }}" class="btn btn-outline-del btn-sm rounded-pill px-3" style="font-size: 0.75rem;">
                Kelola Semua Transaksi
            </a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small" aria-label="5 transaksi peminjaman terbaru">
                <thead class="table-light text-secondary text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.4px;">
                    <tr>
                        <th class="ps-3">Peminjam</th>
                        <th>Judul Buku</th>
                        <th>Tgl Pinjam</th>
                        <th>Jatuh Tempo</th>
                        <th>Status</th>
                        <th class="text-end pe-3">Denda Berjalan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($peminjamanTerbaru as $tx)
                        <tr>
                            <td class="ps-3">
                                <span class="fw-semibold text-dark d-block">{{ $tx->user->name ?? 'Mahasiswa' }}</span>
                                <span class="text-muted" style="font-size: 0.72rem;">{{ $tx->user->nim ?? $tx->user->email }}</span>
                            </td>
                            <td class="fw-semibold">
                                <a href="{{ route('buku.show', $tx->buku) }}" class="text-dark text-decoration-none">
                                    {{ $tx->buku->judul }}
                                </a>
                            </td>
                            <td>{{ $tx->tanggal_pinjam->format('d/m/Y') }}</td>
                            <td>{{ $tx->tanggal_jatuh_tempo->format('d/m/Y') }}</td>
                            <td>
                                @if($tx->status === 'dipinjam')
                                    @if($tx->is_terlambat)
                                        <span class="badge bg-danger rounded-pill">
                                            <i class="bi bi-clock-history me-1"></i>Terlambat {{ $tx->hari_terlambat }} hr
                                        </span>
                                    @else
                                        <span class="badge bg-primary-subtle text-primary border rounded-pill">Dipinjam</span>
                                    @endif
                                @else
                                    <span class="badge bg-success-subtle text-success border rounded-pill">Dikembalikan</span>
                                @endif
                            </td>
                            <td class="text-end pe-3 font-monospace">
                                @if($tx->denda_efektif > 0)
                                    <span class="text-danger fw-semibold">Rp{{ number_format($tx->denda_efektif, 0, ',', '.') }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox d-block fs-2 mb-2 opacity-50" aria-hidden="true"></i>
                                Belum ada transaksi peminjaman.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ===== Pengumuman & Edaran Perpustakaan Terkini ===== --}}
    <div class="card card-del border-0 shadow-sm overflow-hidden mt-4">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-megaphone-fill text-del" aria-hidden="true"></i>
                <h2 class="h6 card-title mb-0 fw-semibold text-secondary">Pengumuman &amp; Edaran Perpustakaan Terkini</h2>
                <span class="badge bg-purple-subtle text-del rounded-pill small ms-1">{{ $totalPengumuman }} Total</span>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.pengumuman.index') }}" class="btn btn-outline-del btn-sm rounded-pill px-3" style="font-size: 0.75rem;">
                    Kelola Semua Pengumuman
                </a>
                <a href="{{ route('admin.pengumuman.create') }}" class="btn btn-del btn-sm rounded-pill px-3" style="font-size: 0.75rem;">
                    <i class="bi bi-plus-circle me-1"></i> Buat Baru
                </a>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small" aria-label="Pengumuman perpustakaan terkini">
                <thead class="table-light text-secondary text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.4px;">
                    <tr>
                        <th class="ps-3">Judul Pengumuman</th>
                        <th style="width: 140px;">Kategori</th>
                        <th style="width: 110px;">Status</th>
                        <th style="width: 130px;">Disematkan</th>
                        <th style="width: 150px;">Tanggal Dibuat</th>
                        <th class="text-end pe-3" style="width: 120px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pengumumanTerbaru as $pg)
                        <tr class="{{ $pg->is_pinned ? 'table-warning-subtle' : '' }}">
                            <td class="ps-3">
                                <a href="{{ route('admin.pengumuman.show', $pg->id) }}" class="fw-bold text-dark text-decoration-none hover-purple d-block">
                                    {{ $pg->judul }}
                                </a>
                                <span class="text-muted" style="font-size: 0.72rem;">
                                    Oleh: {{ $pg->author->name ?? 'Admin' }} &bull; {{ Str::limit(strip_tags($pg->konten), 70) }}
                                </span>
                            </td>
                            <td>{!! $pg->kategori_badge !!}</td>
                            <td>
                                @if($pg->is_aktif)
                                    <span class="badge bg-success-subtle text-success rounded-pill px-2 py-1" style="font-size: 0.7rem;">
                                        <i class="bi bi-check-circle me-1"></i>Tayang
                                    </span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2 py-1" style="font-size: 0.7rem;">
                                        Draft
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($pg->is_pinned)
                                    <span class="badge bg-danger rounded-pill px-2 py-1" style="font-size: 0.7rem;">
                                        <i class="bi bi-pin-fill me-1"></i>Pinned
                                    </span>
                                @else
                                    <span class="text-muted" style="font-size: 0.75rem;">—</span>
                                @endif
                            </td>
                            <td class="text-muted" style="font-size: 0.75rem;">
                                {{ $pg->created_at->format('d M Y') }}
                            </td>
                            <td class="text-end pe-3">
                                <a href="{{ route('admin.pengumuman.show', $pg->id) }}" class="btn btn-outline-info btn-sm rounded-pill px-2 py-0" style="font-size: 0.75rem;" title="Lihat">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('admin.pengumuman.edit', $pg->id) }}" class="btn btn-outline-warning btn-sm rounded-pill px-2 py-0" style="font-size: 0.75rem;" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="bi bi-megaphone d-block fs-3 mb-2 opacity-50" aria-hidden="true"></i>
                                Belum ada pengumuman yang diterbitkan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ===== RIWAYAT TRANSAKSI AUDIT LOG (ACID LOGGING) ===== --}}
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden mb-4">
        <div class="card-header bg-white py-3 px-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-shield-check text-del fs-5"></i>
                <h2 class="h6 fw-bold mb-0 text-dark">Log Audit Transaksi (ACID Audit Trail)</h2>
                <span class="badge bg-purple-subtle text-del rounded-pill small ms-1">{{ count($riwayatTransaksi) }} Terbaru</span>
            </div>
            <span class="badge bg-light text-muted border" style="font-size: 0.72rem;">
                <i class="bi bi-lock-fill me-1 text-primary"></i>Pencatatan ACID Terverifikasi
            </span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small" aria-label="Riwayat audit transaksi perpustakaan">
                <thead class="table-light text-secondary text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.4px;">
                    <tr>
                        <th class="ps-3" style="width: 80px;">ID</th>
                        <th style="width: 120px;">Aksi</th>
                        <th>Keterangan Transaksi</th>
                        <th style="width: 180px;">Aktor / Petugas</th>
                        <th class="text-end pe-3" style="width: 160px;">Waktu Eksekusi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($riwayatTransaksi as $rt)
                        <tr>
                            <td class="ps-3 text-muted fw-bold">#{{ $rt->id }}</td>
                            <td>
                                @if($rt->aksi === 'pinjam')
                                    <span class="badge bg-primary-subtle text-primary rounded-pill px-2 py-1">
                                        <i class="bi bi-box-arrow-up-right me-1"></i>Pinjam
                                    </span>
                                @elseif($rt->aksi === 'kembali')
                                    <span class="badge bg-success-subtle text-success rounded-pill px-2 py-1">
                                        <i class="bi bi-box-arrow-in-down-left me-1"></i>Kembali
                                    </span>
                                @elseif($rt->aksi === 'perpanjang')
                                    <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill px-2 py-1">
                                        <i class="bi bi-arrow-repeat me-1"></i>Perpanjang
                                    </span>
                                @elseif($rt->aksi === 'lunas')
                                    <span class="badge bg-info-subtle text-info-emphasis rounded-pill px-2 py-1">
                                        <i class="bi bi-cash-coin me-1"></i>Pelunasan
                                    </span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2 py-1">
                                        {{ ucfirst($rt->aksi) }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span class="fw-semibold text-dark">{{ $rt->keterangan }}</span>
                                @if($rt->peminjaman)
                                    <span class="d-block text-muted" style="font-size: 0.72rem;">
                                        Ref: {{ $rt->peminjaman->kode_pinjam }} &bull; Buku: {{ $rt->peminjaman->buku->judul ?? '-' }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-1">
                                    <i class="bi bi-person-circle text-muted"></i>
                                    <span class="text-dark">{{ $rt->aktor->name ?? 'Sistem' }}</span>
                                </div>
                            </td>
                            <td class="text-end pe-3 text-muted" style="font-size: 0.75rem;">
                                {{ $rt->created_at->format('d M Y, H:i:s') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                <i class="bi bi-journal-check d-block fs-3 mb-2 opacity-50" aria-hidden="true"></i>
                                Belum ada riwayat transaksi audit log.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</x-layout>
