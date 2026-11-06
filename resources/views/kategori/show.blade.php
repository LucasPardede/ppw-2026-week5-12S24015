<x-layout title="Detail Kategori: {{ $kategori->nama_kategori }} — SIPUS-Del">
    {{-- Breadcrumb --}}
    <nav aria-label="Breadcrumb" class="mb-3">
        <ol class="breadcrumb breadcrumb-del mb-0" style="font-size: 0.82rem;">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('kategori.index') }}">Kelola Kategori</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $kategori->nama_kategori }}</li>
        </ol>
    </nav>

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge badge-del px-2 py-1 font-monospace">{{ $kategori->kode_kategori }}</span>
                <h1 class="fw-bold mb-0 text-dark fs-3">{{ $kategori->nama_kategori }}</h1>
            </div>
            <p class="text-muted small mb-0">Rincian kategori koleksi dan buku yang terdaftar di dalamnya.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('kategori.edit', $kategori) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                <i class="bi bi-pencil me-1" aria-hidden="true"></i> Ubah Kategori
            </a>
            <a href="{{ route('kategori.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="bi bi-arrow-left me-1" aria-hidden="true"></i> Kembali ke Daftar
            </a>
        </div>
    </div>

    <div class="card card-del border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h2 class="h6 card-title mb-0 fw-semibold text-secondary">
                <i class="bi bi-book me-2" aria-hidden="true"></i>Buku dalam Kategori Ini ({{ $kategori->bukus->count() }} Judul)
            </h2>
            <a href="{{ route('buku.create') }}" class="btn btn-del btn-sm rounded-pill px-3">
                <i class="bi bi-plus-lg me-1" aria-hidden="true"></i> Tambah Buku Baru
            </a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-secondary text-uppercase small" style="font-size: 0.8rem;">
                    <tr>
                        <th class="ps-4">No</th>
                        <th>ISBN</th>
                        <th>Judul Buku</th>
                        <th>Penulis</th>
                        <th>Tahun</th>
                        <th class="text-center">Stok</th>
                        <th class="text-end pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($kategori->bukus as $index => $buku)
                        <tr>
                            <td class="ps-4 text-muted fw-semibold">{{ $index + 1 }}</td>
                            <td><code>{{ $buku->isbn }}</code></td>
                            <td class="fw-semibold text-dark">
                                <a href="{{ route('buku.show', $buku) }}" class="text-decoration-none text-dark">
                                    {{ $buku->judul }}
                                </a>
                            </td>
                            <td>{{ $buku->penulis }}</td>
                            <td>{{ $buku->tahun_terbit }}</td>
                            <td class="text-center">
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
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('buku.show', $buku) }}" class="btn btn-outline-secondary" title="Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('buku.edit', $buku) }}" class="btn btn-outline-primary" title="Ubah">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary"></i>
                                Belum ada buku yang terdaftar di bawah kategori ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layout>
