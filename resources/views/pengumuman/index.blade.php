<x-layout title="Papan Pengumuman & Informasi — SIPUS-Del">
    {{-- Breadcrumb --}}
    <nav aria-label="Breadcrumb" class="mb-3">
        <ol class="breadcrumb breadcrumb-del mb-0" style="font-size: 0.82rem;">
            <li class="breadcrumb-item"><a href="{{ route('katalog.index') }}"><i class="bi bi-house-door me-1"></i>Beranda</a></li>
            <li class="breadcrumb-item active" aria-current="page">Pengumuman</li>
        </ol>
    </nav>

    {{-- Hero Banner Pengumuman --}}
    <div class="card border-0 shadow-sm mb-4 overflow-hidden rounded-4" style="background: linear-gradient(135deg, #3B0764 0%, #4C1D95 60%, #6D28D9 100%); color: #ffffff;">
        <div class="card-body p-4 p-md-5">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-3" style="background: rgba(255,255,255,0.15); backdrop-filter: blur(8px); border: 1px solid rgba(255,255,255,0.2); font-size: 0.8rem; font-weight: 600;">
                        <i class="bi bi-megaphone-fill text-warning"></i>
                        <span>Pusat Informasi & Edaran Resmi Perpustakaan</span>
                    </div>
                    <h1 class="display-6 fw-bold mb-2">Papan Pengumuman SIPUS-Del</h1>
                    <p class="lead mb-0 text-white-50" style="font-size: 1.05rem;">
                        Informasi jadwal layanan, pengembalian buku, agenda kegiatan literasi, dan pemberitahuan penting untuk seluruh civitas akademika Institut Teknologi Del.
                    </p>
                </div>
                <div class="col-lg-4 d-none d-lg-block text-end">
                    <div style="width: 140px; height: 140px; background: rgba(255,255,255,0.08); border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; border: 2px dashed rgba(255,255,255,0.25);">
                        <i class="bi bi-bell-fill display-4 text-warning opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Kategori Chips & Search --}}
    <div class="card card-del border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('pengumuman.index') }}" class="row g-3 align-items-center">
                <div class="col-lg-6">
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <span class="small fw-semibold text-muted me-1"><i class="bi bi-funnel me-1"></i>Kategori:</span>
                        <a href="{{ route('pengumuman.index', array_merge(request()->except('kategori', 'page'))) }}" 
                           class="cat-chip {{ !request('kategori') ? 'active' : '' }}">
                           Semua
                        </a>
                        <a href="{{ route('pengumuman.index', array_merge(request()->except('kategori', 'page'), ['kategori' => 'penting'])) }}" 
                           class="cat-chip {{ request('kategori') === 'penting' ? 'active' : '' }}">
                           <i class="bi bi-exclamation-triangle-fill text-danger me-1"></i>Penting
                        </a>
                        <a href="{{ route('pengumuman.index', array_merge(request()->except('kategori', 'page'), ['kategori' => 'layanan'])) }}" 
                           class="cat-chip {{ request('kategori') === 'layanan' ? 'active' : '' }}">
                           <i class="bi bi-clock-history text-info me-1"></i>Layanan
                        </a>
                        <a href="{{ route('pengumuman.index', array_merge(request()->except('kategori', 'page'), ['kategori' => 'kegiatan'])) }}" 
                           class="cat-chip {{ request('kategori') === 'kegiatan' ? 'active' : '' }}">
                           <i class="bi bi-calendar-event text-success me-1"></i>Kegiatan
                        </a>
                        <a href="{{ route('pengumuman.index', array_merge(request()->except('kategori', 'page'), ['kategori' => 'info'])) }}" 
                           class="cat-chip {{ request('kategori') === 'info' ? 'active' : '' }}">
                           <i class="bi bi-info-circle text-primary me-1"></i>Info Umum
                        </a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="input-group">
                        @if(request('kategori'))
                            <input type="hidden" name="kategori" value="{{ request('kategori') }}">
                        @endif
                        <span class="input-group-text bg-white border-end-0 text-muted">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}" 
                               class="form-control border-start-0 ps-0" 
                               placeholder="Cari judul atau isi pengumuman...">
                        <button class="btn btn-del" type="submit">
                            Cari
                        </button>
                        @if(request('search') || request('kategori'))
                            <a href="{{ route('pengumuman.index') }}" class="btn btn-outline-secondary" title="Reset filter">
                                <i class="bi bi-x-circle"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Highlight Pengumuman Disematkan (Pinned) --}}
    @if($pinnedPengumumans->isNotEmpty() && !request('search') && !request('kategori'))
        <div class="mb-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h2 class="h5 fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                    <i class="bi bi-pin-angle-fill text-danger"></i>
                    <span>Pengumuman Utama &amp; Prioritas</span>
                </h2>
                <span class="badge bg-danger-subtle text-danger px-2 py-1 rounded-pill small">Sematkan</span>
            </div>

            <div class="row g-3">
                @foreach($pinnedPengumumans as $pinned)
                    <div class="col-md-{{ $pinnedPengumumans->count() === 1 ? '12' : ($pinnedPengumumans->count() === 2 ? '6' : '4') }}">
                        <div class="card h-100 border-0 shadow-sm rounded-3 overflow-hidden position-relative" style="border-left: 4px solid #DC2626 !important; background: #FFFDFD;">
                            <div class="card-body p-4 d-flex flex-column">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    {!! $pinned->kategori_badge !!}
                                    <span class="badge bg-danger text-white rounded-pill px-2 py-1" style="font-size: 0.68rem;">
                                        <i class="bi bi-pin-fill me-1"></i>PENTING
                                    </span>
                                </div>
                                <h3 class="h5 fw-bold mb-2">
                                    <a href="{{ route('pengumuman.show', $pinned->slug) }}" class="text-dark text-decoration-none hover-purple">
                                        {{ $pinned->judul }}
                                    </a>
                                </h3>
                                <p class="text-muted small mb-3 flex-grow-1" style="line-height: 1.6;">
                                    {{ Str::limit(strip_tags($pinned->konten), 130) }}
                                </p>
                                <div class="d-flex align-items-center justify-content-between pt-3 border-top mt-auto small text-muted">
                                    <span class="d-flex align-items-center gap-1">
                                        <i class="bi bi-calendar3"></i> {{ $pinned->formatted_date }}
                                    </span>
                                    <a href="{{ route('pengumuman.show', $pinned->slug) }}" class="fw-semibold text-del text-decoration-none">
                                        Baca Selengkapnya <i class="bi bi-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Daftar Seluruh Pengumuman --}}
    <div class="mb-4">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h2 class="h5 fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                <i class="bi bi-newspaper text-del"></i>
                <span>Semua Pengumuman</span>
                @if(request('search'))
                    <span class="badge bg-light text-muted fw-normal fs-6">Hasil cari: "{{ request('search') }}"</span>
                @endif
                @if(request('kategori'))
                    <span class="badge bg-purple-subtle text-del fw-normal fs-6">Kategori: {{ ucfirst(request('kategori')) }}</span>
                @endif
            </h2>
            <span class="text-muted small">Total: {{ $pengumumans->total() }} berita</span>
        </div>

        @if($pengumumans->isEmpty())
            <div class="card border-0 shadow-sm rounded-4 p-5 text-center">
                <div class="py-4">
                    <i class="bi bi-bell-slash text-muted display-3 mb-3 d-block opacity-50"></i>
                    <h3 class="h5 fw-bold text-secondary">Belum ada pengumuman yang sesuai</h3>
                    <p class="text-muted small mb-3">Tidak ditemukan pengumuman dengan kata kunci atau filter kategori yang dipilih.</p>
                    <a href="{{ route('pengumuman.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-4">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Filter
                    </a>
                </div>
            </div>
        @else
            <div class="row g-4">
                @foreach($pengumumans as $item)
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 border-0 shadow-sm rounded-3 overflow-hidden position-relative hover-lift transition-all" style="border-top: 3px solid #4C1D95 !important;">
                            <div class="card-body p-4 d-flex flex-column">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    {!! $item->kategori_badge !!}
                                    @if($item->is_pinned)
                                        <span class="badge bg-danger text-white rounded-pill px-2 py-1" style="font-size: 0.65rem;">
                                            <i class="bi bi-pin-fill"></i> Pinned
                                        </span>
                                    @else
                                        <span class="text-muted small" style="font-size: 0.75rem;">
                                            <i class="bi bi-clock me-1"></i>{{ $item->created_at->diffForHumans() }}
                                        </span>
                                    @endif
                                </div>

                                <h3 class="h6 fw-bold mb-2">
                                    <a href="{{ route('pengumuman.show', $item->slug) }}" class="text-dark text-decoration-none hover-purple" style="line-height: 1.4;">
                                        {{ $item->judul }}
                                    </a>
                                </h3>

                                <p class="text-muted small mb-3 flex-grow-1" style="line-height: 1.6;">
                                    {{ Str::limit(strip_tags($item->konten), 120) }}
                                </p>

                                <div class="d-flex align-items-center justify-content-between pt-3 border-top mt-auto small text-muted">
                                    <span class="d-flex align-items-center gap-1">
                                        <i class="bi bi-person-circle text-del"></i> {{ $item->author->name ?? 'Admin Perpustakaan' }}
                                    </span>
                                    <a href="{{ route('pengumuman.show', $item->slug) }}" class="fw-semibold text-del text-decoration-none">
                                        Lihat &rarr;
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($pengumumans->hasPages())
                <div class="mt-4 d-flex justify-content-center">
                    {{ $pengumumans->links() }}
                </div>
            @endif
        @endif
    </div>
</x-layout>
