<x-layout title="Detail Buku: {{ $buku->judul }} — SIPUS-Del">
    {{-- Breadcrumb --}}
    <nav aria-label="Breadcrumb" class="mb-3">
        <ol class="breadcrumb breadcrumb-del mb-0" style="font-size: 0.82rem;">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('buku.index') }}">Kelola Buku</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ Str::limit($buku->judul, 35) }}</li>
        </ol>
    </nav>

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge badge-del px-2 py-1 font-monospace">{{ $buku->kategori->nama_kategori ?? 'Umum' }}</span>
                <span class="text-muted small">ISBN: <code>{{ $buku->isbn }}</code></span>
            </div>
            <h1 class="fw-bold mb-0 text-dark fs-3">{{ $buku->judul }}</h1>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('buku.edit', $buku) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                <i class="bi bi-pencil me-1" aria-hidden="true"></i> Ubah Data
            </a>
            <a href="{{ route('buku.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="bi bi-arrow-left me-1" aria-hidden="true"></i> Kembali ke Daftar
            </a>
        </div>
    </div>

    <div class="row g-4 mb-4">
        {{-- Metadata Buku --}}
        <div class="col-lg-5">
            <div class="card card-del border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="card-title mb-0 fw-semibold text-secondary">
                        <i class="bi bi-info-circle me-2"></i>Informasi Bibliografi
                    </h5>
                </div>
                <div class="card-body p-4">
                    <ul class="list-group list-group-flush small">
                        <li class="list-group-item d-flex justify-content-between px-0 py-2">
                            <span class="text-muted">Nomor ISBN:</span>
                            <span class="fw-semibold font-monospace">{{ $buku->isbn }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0 py-2">
                            <span class="text-muted">Penulis:</span>
                            <span class="fw-semibold">{{ $buku->penulis }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0 py-2">
                            <span class="text-muted">Penerbit:</span>
                            <span class="fw-semibold">{{ $buku->penerbit }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0 py-2">
                            <span class="text-muted">Tahun Terbit:</span>
                            <span class="fw-semibold">{{ $buku->tahun_terbit }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0 py-2">
                            <span class="text-muted">Kategori:</span>
                            <span class="badge badge-del">{{ $buku->kategori->nama_kategori ?? '-' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0 py-2 align-items-center">
                            <span class="text-muted">Status Ketersediaan:</span>
                            <span>
                                @if($buku->status_stok === 'tersedia')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1">
                                        {{ $buku->stok }} Tersedia
                                    </span>
                                @elseif($buku->status_stok === 'menipis')
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2 py-1">
                                        {{ $buku->stok }} Menipis
                                    </span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-1">
                                        Habis
                                    </span>
                                @endif
                            </span>
                        </li>
                    </ul>

                    @if($buku->sinopsis)
                        <div class="mt-4 pt-3 border-top">
                            <h6 class="fw-bold text-dark mb-2">Sinopsis / Ringkasan:</h6>
                            <p class="text-secondary small lh-base mb-0 text-justify">
                                {{ $buku->sinopsis }}
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Riwayat Peminjaman Buku Ini --}}
        <div class="col-lg-7">
            <div class="card card-del border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="card-title mb-0 fw-semibold text-secondary">
                        <i class="bi bi-clock-history me-2"></i>Riwayat Transaksi Peminjaman Buku Ini
                    </h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light text-secondary text-uppercase" style="font-size: 0.75rem;">
                            <tr>
                                <th class="ps-3">Peminjam</th>
                                <th>Tgl Pinjam</th>
                                <th>Jatuh Tempo</th>
                                <th>Status</th>
                                <th class="text-end pe-3">Denda</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($buku->peminjamans as $pinjam)
                                <tr>
                                    <td class="ps-3">
                                        <div class="fw-semibold text-dark">{{ $pinjam->user->name ?? 'Mahasiswa' }}</div>
                                        <div class="text-muted" style="font-size: 0.75rem;">{{ $pinjam->user->nim ?? $pinjam->user->email }}</div>
                                    </td>
                                    <td>{{ $pinjam->tanggal_pinjam->format('d/m/Y') }}</td>
                                    <td>{{ $pinjam->tanggal_jatuh_tempo->format('d/m/Y') }}</td>
                                    <td>
                                        @if($pinjam->status === 'dipinjam')
                                            @if($pinjam->is_terlambat)
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill">
                                                    Terlambat ({{ $pinjam->hari_terlambat }} hr)
                                                </span>
                                            @else
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill">
                                                    Dipinjam
                                                </span>
                                            @endif
                                        @else
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">
                                                Dikembalikan
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-3 font-monospace">
                                        @if($pinjam->denda_efektif > 0)
                                            <span class="text-danger fw-semibold">
                                                Rp{{ number_format($pinjam->denda_efektif, 0, ',', '.') }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        <i class="bi bi-inbox d-block fs-3 mb-1 text-secondary"></i>
                                        Buku ini belum pernah dipinjam.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-layout>
