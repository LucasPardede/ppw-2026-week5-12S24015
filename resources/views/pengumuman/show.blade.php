<x-layout title="{{ $pengumuman->judul }} — Pengumuman SIPUS-Del">
    {{-- Breadcrumb --}}
    <nav aria-label="Breadcrumb" class="mb-3">
        <ol class="breadcrumb breadcrumb-del mb-0" style="font-size: 0.82rem;">
            <li class="breadcrumb-item"><a href="{{ route('katalog.index') }}"><i class="bi bi-house-door me-1"></i>Beranda</a></li>
            <li class="breadcrumb-item"><a href="{{ route('pengumuman.index') }}">Pengumuman</a></li>
            <li class="breadcrumb-item active text-truncate" style="max-width: 320px;" aria-current="page">{{ $pengumuman->judul }}</li>
        </ol>
    </nav>

    <div class="row g-4">
        {{-- Konten Utama Pengumuman --}}
        <div class="col-lg-8">
            <article class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                {{-- Header Pengumuman --}}
                <div class="card-header bg-white p-4 p-md-5 border-bottom">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                        {!! $pengumuman->kategori_badge !!}
                        @if($pengumuman->is_pinned)
                            <span class="badge bg-danger text-white rounded-pill px-3 py-1" style="font-size: 0.72rem;">
                                <i class="bi bi-pin-fill me-1"></i>PENGUMUMAN PENTING &amp; DISEMATKAN
                            </span>
                        @endif
                    </div>

                    <h1 class="h2 fw-bold text-dark mb-3" style="line-height: 1.35;">
                        {{ $pengumuman->judul }}
                    </h1>

                    <div class="d-flex flex-wrap align-items-center gap-3 text-muted small pt-2 border-top">
                        <span class="d-flex align-items-center gap-1">
                            <i class="bi bi-person-fill text-del"></i> Diterbitkan oleh: <strong class="text-dark">{{ $pengumuman->author->name ?? 'Admin Perpustakaan' }}</strong>
                        </span>
                        <span>&bull;</span>
                        <span class="d-flex align-items-center gap-1">
                            <i class="bi bi-calendar3 text-del"></i> {{ $pengumuman->formatted_date }}
                        </span>
                        <span>&bull;</span>
                        <span class="d-flex align-items-center gap-1">
                            <i class="bi bi-clock text-del"></i> {{ $pengumuman->created_at->format('H:i') }} WIB
                        </span>
                    </div>
                </div>

                {{-- Badan Artikel --}}
                <div class="card-body p-4 p-md-5">
                    <div class="pengumuman-content fs-6 text-dark" style="line-height: 1.85; word-break: break-word;">
                        {!! nl2br(e($pengumuman->konten)) !!}
                    </div>

                    {{-- Catatan Resmi Del --}}
                    <div class="mt-5 p-3 rounded-3" style="background: rgba(76, 29, 149, 0.04); border-left: 4px solid #4C1D95;">
                        <div class="d-flex align-items-start gap-3">
                            <i class="bi bi-shield-check text-del fs-4 flex-shrink-0"></i>
                            <div class="small">
                                <strong class="d-block text-del mb-1">Informasi Resmi Perpustakaan Institut Teknologi Del</strong>
                                <span class="text-muted">Jika Anda memiliki pertanyaan lebih lanjut mengenai isi pengumuman ini, silakan hubungi bagian sirkulasi perpustakaan atau surel ke <a href="mailto:library@del.ac.id" class="text-del fw-semibold">library@del.ac.id</a>.</span>
                            </div>
                        </div>
                    </div>

                    {{-- Bagikan Pengumuman --}}
                    <div class="mt-4 pt-4 border-top">
                        <span class="small fw-semibold text-muted d-block mb-2">Bagikan Pengumuman Ini:</span>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="https://api.whatsapp.com/send?text={{ urlencode($pengumuman->judul . ' - ' . url()->current()) }}" 
                               target="_blank" rel="noopener noreferrer" 
                               class="share-btn share-wa">
                                <i class="bi bi-whatsapp"></i> WhatsApp
                            </a>
                            <a href="https://twitter.com/intent/tweet?text={{ urlencode($pengumuman->judul) }}&url={{ urlencode(url()->current()) }}" 
                               target="_blank" rel="noopener noreferrer" 
                               class="share-btn share-x">
                                <i class="bi bi-twitter-x"></i> X (Twitter)
                            </a>
                            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}" 
                               target="_blank" rel="noopener noreferrer" 
                               class="share-btn share-fb">
                                <i class="bi bi-facebook"></i> Facebook
                            </a>
                            <button type="button" 
                                    class="share-btn share-copy" 
                                    onclick="navigator.clipboard.writeText(window.location.href); alert('Tautan pengumuman berhasil disalin ke papan klip!');">
                                <i class="bi bi-link-45deg"></i> Salin Tautan
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card-footer bg-light p-3 d-flex justify-content-between align-items-center">
                    <a href="{{ route('pengumuman.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                        <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Pengumuman
                    </a>
                    <a href="{{ route('katalog.index') }}" class="btn btn-del btn-sm rounded-pill px-3">
                        <i class="bi bi-book me-1"></i> Jelajahi Koleksi Buku
                    </a>
                </div>
            </article>
        </div>

        {{-- Sidebar --}}
        <div class="col-lg-4">
            {{-- Pengumuman Terkait/Terbaru --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center gap-2">
                    <i class="bi bi-newspaper text-del"></i>
                    <h2 class="h6 fw-bold mb-0">Pengumuman Lainnya</h2>
                </div>
                <div class="list-group list-group-flush">
                    @forelse($recentPengumumans as $recent)
                        <a href="{{ route('pengumuman.show', $recent->slug) }}" class="list-group-item list-group-item-action p-3">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                {!! $recent->kategori_badge !!}
                                <span class="text-muted" style="font-size: 0.72rem;">{{ $recent->created_at->diffForHumans() }}</span>
                            </div>
                            <h3 class="fw-semibold text-dark mb-1" style="font-size: 0.88rem; line-height: 1.35;">
                                {{ $recent->judul }}
                            </h3>
                            <p class="text-muted small mb-0">
                                {{ Str::limit(strip_tags($recent->konten), 60) }}
                            </p>
                        </a>
                    @empty
                        <div class="p-3 text-center text-muted small">
                            Tidak ada pengumuman lainnya saat ini.
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Layanan & Jam Operasional Perpustakaan Del --}}
            <div class="card border-0 shadow-sm rounded-4 p-4 text-white" style="background: linear-gradient(135deg, #4C1D95 0%, #3B0764 100%);">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(255,255,255,0.15); display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-clock-history fs-4 text-warning"></i>
                    </div>
                    <div>
                        <h3 class="h6 fw-bold mb-0">Jam Layanan Fisik</h3>
                        <span class="small text-white-50">Gedung Perpustakaan Del</span>
                    </div>
                </div>
                <ul class="list-unstyled small mb-0" style="line-height: 2;">
                    <li class="d-flex justify-content-between border-bottom border-white-10 pb-1">
                        <span>Senin - Jumat:</span>
                        <strong>08.00 - 21.00 WIB</strong>
                    </li>
                    <li class="d-flex justify-content-between border-bottom border-white-10 py-1">
                        <span>Sabtu:</span>
                        <strong>08.00 - 15.00 WIB</strong>
                    </li>
                    <li class="d-flex justify-content-between pt-1 text-white-50">
                        <span>Minggu & Hari Libur:</span>
                        <span>Tutup (Layanan Digital Aktif)</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</x-layout>
