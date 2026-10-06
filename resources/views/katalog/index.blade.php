<x-layout title="Katalog Koleksi Buku — Portal Mahasiswa SIPUS-Del">
    {{-- Header Banner Portal Mahasiswa --}}
    <div class="page-header-del d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h1 class="fw-bold mb-2 fs-2">
                <i class="bi bi-book-half text-warning me-2"></i>Katalog Perpustakaan Institut Teknologi Del
            </h1>
            <p class="mb-0 text-white-50">
                Temukan referensi buku akademik, sains terapan, dan materi perkuliahan terkini.
            </p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('pinjaman.index') }}" class="btn btn-warning fw-semibold rounded-pill px-4 shadow-sm">
                <i class="bi bi-journal-check me-1"></i> Pinjaman Saya
            </a>
        </div>
    </div>

    {{-- Filter Pencarian & Kategori --}}
    <div class="card card-del border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('katalog.index') }}" class="row g-2 align-items-center">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted border-end-0">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}" 
                               class="form-control border-start-0 ps-0" 
                               placeholder="Cari judul buku, pengarang, atau ISBN...">
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
                        <i class="bi bi-search me-1"></i> Cari Buku
                    </button>
                    @if(request('search') || request('kategori_id'))
                        <a href="{{ route('katalog.index') }}" class="btn btn-outline-secondary" title="Reset Filter">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Grid Kartu Koleksi Buku --}}
    <div class="row g-4 mb-4">
        @forelse($bukus as $buku)
            <div class="col-md-6 col-lg-4">
                <div class="card card-del border-0 shadow-sm h-100 d-flex flex-column">
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                            <span class="badge badge-del px-2 py-1 small">
                                {{ $buku->kategori->nama_kategori ?? 'Umum' }}
                            </span>
                            @if($buku->status_stok === 'tersedia')
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1 small">
                                    <i class="bi bi-check-circle me-1"></i> {{ $buku->stok }} Tersedia
                                </span>
                            @elseif($buku->status_stok === 'menipis')
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2 py-1 small">
                                    <i class="bi bi-exclamation-circle me-1"></i> Sisa {{ $buku->stok }}
                                </span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-1 small">
                                    <i class="bi bi-x-circle me-1"></i> Stok Habis
                                </span>
                            @endif
                        </div>

                        <h5 class="card-title fw-bold text-dark mb-2">
                            <a href="{{ route('katalog.show', $buku) }}" class="text-decoration-none text-dark hover-del">
                                {{ $buku->judul }}
                            </a>
                        </h5>

                        <div class="text-muted small mb-3">
                            <i class="bi bi-person me-1"></i>{{ $buku->penulis }} • {{ $buku->tahun_terbit }}
                        </div>

                        <p class="card-text text-secondary small flex-grow-1 text-truncate-3 mb-3" style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                            {{ $buku->sinopsis ?? 'Tidak ada sinopsis untuk buku ini.' }}
                        </p>

                        <div class="border-top pt-3 d-flex align-items-center justify-content-between gap-2 mt-auto">
                            <a href="{{ route('katalog.show', $buku) }}" class="btn btn-outline-secondary btn-sm px-3 rounded-pill">
                                <i class="bi bi-info-circle me-1"></i> Rincian
                            </a>

                            @if($buku->stok > 0)
                                <form action="{{ route('katalog.pinjam', $buku) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda ingin meminjam buku \'{{ $buku->judul }}\'? Batas peminjaman adalah 7 hari.')">
                                    @csrf
                                    <button type="submit" class="btn btn-del btn-sm px-3 rounded-pill shadow-sm">
                                        <i class="bi bi-bookmark-plus me-1"></i> Pinjam Buku
                                    </button>
                                </form>
                            @else
                                <button class="btn btn-secondary btn-sm px-3 rounded-pill" disabled>
                                    <i class="bi bi-slash-circle me-1"></i> Tidak Tersedia
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card card-del border-0 shadow-sm p-5 text-center">
                    <i class="bi bi-journal-x fs-1 text-secondary mb-3"></i>
                    <h5 class="fw-bold text-dark">Koleksi Buku Tidak Ditemukan</h5>
                    <p class="text-muted small mb-3">Tidak ada buku yang sesuai dengan kata kunci atau kategori yang Anda pilih.</p>
                    <div>
                        <a href="{{ route('katalog.index') }}" class="btn btn-del btn-sm rounded-pill px-4">
                            Lihat Semua Koleksi
                        </a>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Pagination Berbasis Bootstrap 5 --}}
    @if($bukus->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $bukus->links() }}
        </div>
    @endif
</x-layout>
