<x-layout title="Kelola Koleksi Buku — SIPUS-Del">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h2 class="fw-bold mb-1 text-dark">
                <i class="bi bi-book text-del me-2"></i>Katalog Koleksi Buku
            </h2>
            <p class="text-muted mb-0 small">Kelola seluruh buku, inventaris stok, dan pembaruan data koleksi perpustakaan.</p>
        </div>
        <div>
            <a href="{{ route('buku.create') }}" class="btn btn-del rounded-pill px-4 shadow-sm">
                <i class="bi bi-plus-circle me-1"></i> Tambah Buku Baru
            </a>
        </div>
    </div>

    {{-- Filter dan Pencarian --}}
    <div class="card card-del border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('buku.index') }}" class="row g-2 align-items-center">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted border-end-0">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}" 
                               class="form-control border-start-0 ps-0" 
                               placeholder="Cari berdasarkan judul, penulis, atau ISBN...">
                    </div>
                </div>
                <div class="col-md-4">
                    <select name="kategori_id" class="form-select">
                        <option value="">-- Semua Kategori Koleksi --</option>
                        @foreach($kategoris as $kat)
                            <option value="{{ $kat->id }}" {{ request('kategori_id') == $kat->id ? 'selected' : '' }}>
                                {{ $kat->kode_kategori }} — {{ $kat->nama_kategori }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-del flex-grow-1">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                    @if(request('search') || request('kategori_id'))
                        <a href="{{ route('buku.index') }}" class="btn btn-outline-secondary" title="Reset Filter">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Tabel Koleksi Buku --}}
    <div class="card card-del border-0 shadow-sm overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0 fw-semibold text-secondary">
                <i class="bi bi-list-columns-reverse me-2"></i>Daftar Inventaris Buku ({{ $bukus->total() }} Data)
            </h5>
            <span class="badge badge-del px-3 py-2 rounded-pill small">
                Eager Loading: with('kategori')
            </span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-secondary text-uppercase small" style="font-size: 0.8rem; letter-spacing: 0.5px;">
                    <tr>
                        <th class="ps-4" style="width: 5%;">No</th>
                        <th style="width: 35%;">Informasi Buku</th>
                        <th style="width: 15%;">ISBN</th>
                        <th style="width: 18%;">Kategori</th>
                        <th style="width: 12%;" class="text-center">Stok</th>
                        <th style="width: 15%;" class="text-end pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bukus as $index => $buku)
                        <tr>
                            <td class="ps-4 text-muted fw-semibold">{{ $bukus->firstItem() + $index }}</td>
                            <td>
                                <a href="{{ route('buku.show', $buku) }}" class="fw-bold text-dark text-decoration-none d-block">
                                    {{ $buku->judul }}
                                </a>
                                <div class="text-muted small mt-1">
                                    <i class="bi bi-person me-1"></i>{{ $buku->penulis }} 
                                    <span class="mx-1">•</span> 
                                    <i class="bi bi-building me-1"></i>{{ $buku->penerbit }} ({{ $buku->tahun_terbit }})
                                </div>
                            </td>
                            <td>
                                <code class="text-dark bg-light px-2 py-1 rounded small border">{{ $buku->isbn }}</code>
                            </td>
                            <td>
                                <span class="badge badge-del px-2 py-1 small">
                                    {{ $buku->kategori->nama_kategori ?? 'Tanpa Kategori' }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if($buku->status_stok === 'tersedia')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill">
                                        <i class="bi bi-check-circle me-1"></i> {{ $buku->stok }} Tersedia
                                    </span>
                                @elseif($buku->status_stok === 'menipis')
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1 rounded-pill">
                                        <i class="bi bi-exclamation-circle me-1"></i> {{ $buku->stok }} Menipis
                                    </span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 rounded-pill">
                                        <i class="bi bi-x-circle me-1"></i> Stok Habis
                                    </span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('buku.show', $buku) }}" class="btn btn-outline-secondary" title="Lihat Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('buku.edit', $buku) }}" class="btn btn-outline-primary" title="Ubah Data">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <form action="{{ route('buku.destroy', $buku) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus buku \'{{ $buku->judul }}\'?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Hapus Buku">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-search fs-1 d-block mb-2 text-secondary"></i>
                                    <p class="mb-2 fw-semibold">Tidak ditemukan koleksi buku yang sesuai.</p>
                                    @if(request('search') || request('kategori_id'))
                                        <a href="{{ route('buku.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill">
                                            Reset Pencarian
                                        </a>
                                    @else
                                        <a href="{{ route('buku.create') }}" class="btn btn-del btn-sm rounded-pill">
                                            Tambah Buku Baru
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($bukus->hasPages())
            <div class="card-footer bg-white py-3 border-top d-flex justify-content-between align-items-center">
                <div class="small text-muted">
                    Menampilkan {{ $bukus->firstItem() }} - {{ $bukus->lastItem() }} dari {{ $bukus->total() }} buku
                </div>
                <div>
                    {{ $bukus->links() }}
                </div>
            </div>
        @endif
    </div>
</x-layout>
