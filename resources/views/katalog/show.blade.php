<x-layout title="{{ $buku->judul }} — SIPUS-Del Perpustakaan">
    <div class="py-2">
        {{-- Breadcrumb Navigation --}}
        <nav aria-label="Breadcrumb" class="mb-4">
            <ol class="breadcrumb breadcrumb-del mb-0" style="font-size: 0.85rem;">
                <li class="breadcrumb-item"><a href="{{ route('katalog.index') }}"><i class="bi bi-grid-fill me-1"></i>Katalog Koleksi</a></li>
                <li class="breadcrumb-item"><a href="{{ route('katalog.index', ['kategori_id' => $buku->kategori_id]) }}">{{ $buku->kategori->nama_kategori ?? 'Umum' }}</a></li>
                <li class="breadcrumb-item active text-truncate" style="max-width: 320px;" aria-current="page">{{ $buku->judul }}</li>
            </ol>
        </nav>

        {{-- Main Detail Card --}}
        <div class="book-detail-hero">
            <div class="row g-4 lg-g-5">

                {{-- Kolom Kiri: 3D Book Cover & Action Card --}}
                <div class="col-lg-4 col-md-5">
                    <div class="book-3d-wrap mb-3">
                        <div class="book-3d-cover book-3d-detail shadow-lg">
                            <div class="d-flex justify-content-between align-items-start">
                                <span class="badge bg-warning text-dark fw-bold rounded-pill px-2 py-1" style="font-size: 0.65rem;">
                                    {{ $buku->kategori->kode_kategori ?? 'DEL' }}
                                </span>
                                <i class="bi bi-bookmark-fill text-warning fs-5"></i>
                            </div>

                            <div class="text-center my-auto px-2">
                                <i class="bi bi-book-half text-white opacity-75 fs-1 mb-2 d-block"></i>
                                <h2 class="h5 fw-bold text-white mb-2" style="line-height: 1.35; text-shadow: 0 2px 4px rgba(0,0,0,0.3);">
                                    {{ $buku->judul }}
                                </h2>
                                <p class="small text-white-50 mb-0">
                                    {{ $buku->penulis }}
                                </p>
                            </div>

                            <div class="d-flex justify-content-between align-items-end pt-2 border-top border-white border-opacity-25">
                                <span class="font-monospace text-white-50" style="font-size: 0.65rem;">
                                    ISBN: {{ $buku->isbn }}
                                </span>
                                <span class="badge rounded-pill text-white" style="font-size: 0.62rem; background: rgba(255,255,255,0.25);">
                                    {{ $buku->tahun_terbit }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Card Ketersediaan & Aksi Pinjam --}}
                    <div class="card card-del border-0 p-3 mb-3 shadow-sm">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small fw-semibold">Ketersediaan Fisik</span>
                            @if($buku->status_stok === 'tersedia')
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1">
                                    <i class="bi bi-check-circle-fill me-1"></i> Tersedia ({{ $buku->stok }})
                                </span>
                            @elseif($buku->status_stok === 'menipis')
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-3 py-1">
                                    <i class="bi bi-exclamation-triangle-fill me-1"></i> Menipis ({{ $buku->stok }})
                                </span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-1">
                                    <i class="bi bi-x-circle-fill me-1"></i> Stok Habis
                                </span>
                            @endif
                        </div>

                        {{-- Stock Progress Bar --}}
                        <div class="stock-meter mb-3">
                            @php
                                $percent = min(100, max(10, ($buku->stok / 10) * 100));
                            @endphp
                            <div class="stock-meter-fill {{ $buku->stok === 0 ? 'bg-danger' : '' }}" style="width: {{ $buku->stok > 0 ? $percent : 0 }}%;"></div>
                        </div>

                        {{-- Tombol Aksi Pinjam --}}
                        @if($buku->stok > 0)
                            <form action="{{ route('katalog.pinjam', $buku) }}" method="POST" id="pinjamDetailForm{{ $buku->id }}" class="mb-3">
                                @csrf
                                <button type="button"
                                        class="btn btn-del w-100 py-2 rounded-pill fw-semibold shadow-sm"
                                        data-confirm-trigger
                                        data-confirm-form="pinjamDetailForm{{ $buku->id }}"
                                        data-confirm-title="Konfirmasi Peminjaman Buku"
                                        data-confirm-message="Ajukan peminjaman buku '{{ addslashes($buku->judul) }}'?"
                                        data-confirm-subtext="Masa peminjaman standar adalah <strong>7 hari kalender</strong>. Kuota maksimal 3 buku bersamaan."
                                        data-confirm-ok="Ajukan Sekarang"
                                        aria-label="Pinjam buku {{ $buku->judul }}">
                                    <i class="bi bi-bookmark-check-fill me-1" aria-hidden="true"></i> Pinjam Buku Sekarang
                                </button>
                            </form>
                        @else
                            <button class="btn btn-secondary w-100 py-2 rounded-pill fw-semibold mb-3" disabled>
                                <i class="bi bi-dash-circle me-1" aria-hidden="true"></i> Buku Sedang Dipinjam Semua
                            </button>
                        @endif

                        {{-- Info Ringkas Aturan --}}
                        <div class="bg-light p-2 rounded-3 small text-muted">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span><i class="bi bi-clock me-1 text-primary"></i> Durasi Pinjam:</span>
                                <strong class="text-dark">14 Hari</strong>
                            </div>
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span><i class="bi bi-arrow-repeat me-1 text-info"></i> Perpanjangan:</span>
                                <strong class="text-dark">1x (+7 Hari)</strong>
                            </div>
                            <div class="d-flex align-items-center justify-content-between">
                                <span><i class="bi bi-cash me-1 text-warning"></i> Denda Telat:</span>
                                <strong class="text-danger">Rp1.000 / Hari</strong>
                            </div>
                        </div>
                    </div>

                    {{-- Share Buttons Bar (Inspirasi perpus.org) --}}
                    <div class="card card-del border-0 p-3 text-center shadow-sm">
                        <span class="small fw-semibold text-secondary mb-2 d-block">
                            <i class="bi bi-share me-1 text-del"></i> Bagikan Informasi Buku Ini:
                        </span>
                        <div class="d-flex justify-content-center gap-2">
                            <a href="https://api.whatsapp.com/send?text={{ urlencode('Lihat referensi buku menarik di SIPUS-Del: ' . $buku->judul . ' - ' . url()->current()) }}"
                               target="_blank" rel="noopener noreferrer"
                               class="share-btn share-wa" title="Bagikan ke WhatsApp" aria-label="Share ke WhatsApp">
                                <i class="bi bi-whatsapp"></i>
                            </a>
                            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}"
                               target="_blank" rel="noopener noreferrer"
                               class="share-btn share-fb" title="Bagikan ke Facebook" aria-label="Share ke Facebook">
                                <i class="bi bi-facebook"></i>
                            </a>
                            <a href="https://twitter.com/intent/tweet?text={{ urlencode($buku->judul) }}&url={{ urlencode(url()->current()) }}"
                               target="_blank" rel="noopener noreferrer"
                               class="share-btn share-tw" title="Bagikan ke X / Twitter" aria-label="Share ke Twitter">
                                <i class="bi bi-twitter-x"></i>
                            </a>
                            <button type="button" class="share-btn share-link border-0" id="copyBookLinkBtn" title="Salin Tautan" aria-label="Salin Tautan Buku" onclick="copyCurrentLink()">
                                <i class="bi bi-link-45deg fs-5"></i>
                            </button>
                        </div>
                        <span id="copyFeedback" class="small text-success mt-1 fw-semibold" style="display: none;">
                            <i class="bi bi-check2"></i> Tautan berhasil disalin!
                        </span>
                    </div>
                </div>

                {{-- Kolom Kanan: Rincian Bibliografi & Tab Spesifikasi --}}
                <div class="col-lg-8 col-md-7">
                    <div class="mb-3">
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                            <span class="badge badge-del px-3 py-1 rounded-pill">
                                <i class="bi bi-tag-fill me-1"></i>{{ $buku->kategori->nama_kategori ?? 'Umum' }}
                            </span>
                            <span class="badge bg-light text-secondary border font-monospace px-3 py-1 rounded-pill">
                                ISBN: {{ $buku->isbn }}
                            </span>
                            <span class="badge bg-light text-secondary border px-3 py-1 rounded-pill">
                                <i class="bi bi-calendar-event me-1"></i>Tahun {{ $buku->tahun_terbit }}
                            </span>
                        </div>

                        <h1 class="fw-bold text-dark fs-2 mb-2" style="line-height: 1.3;">
                            {{ $buku->judul }}
                        </h1>

                        <div class="d-flex flex-wrap align-items-center text-muted small gap-3 mb-4">
                            <span><i class="bi bi-person-fill text-del me-1"></i>Penulis: <strong class="text-dark">{{ $buku->penulis }}</strong></span>
                            <span>&bull;</span>
                            <span><i class="bi bi-building text-del me-1"></i>Penerbit: <strong class="text-dark">{{ $buku->penerbit }}</strong></span>
                            <span>&bull;</span>
                            <span><i class="bi bi-geo-alt-fill text-del me-1"></i>Rak: <strong>Lantai 2 - Koleksi Teks</strong></span>
                        </div>
                    </div>

                    {{-- Tabs: Sinopsis, Spesifikasi, dan Aturan --}}
                    <ul class="nav nav-tabs nav-tabs-del mb-4" id="bookTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="sinopsis-tab" data-bs-toggle="tab" data-bs-target="#sinopsis-pane" type="button" role="tab" aria-controls="sinopsis-pane" aria-selected="true">
                                <i class="bi bi-card-text me-1"></i> Sinopsis & Deskripsi
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="spesifikasi-tab" data-bs-toggle="tab" data-bs-target="#spesifikasi-pane" type="button" role="tab" aria-controls="spesifikasi-pane" aria-selected="false">
                                <i class="bi bi-list-columns-reverse me-1"></i> Spesifikasi Buku
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="aturan-tab" data-bs-toggle="tab" data-bs-target="#aturan-pane" type="button" role="tab" aria-controls="aturan-pane" aria-selected="false">
                                <i class="bi bi-shield-check me-1"></i> Ketentuan Layanan
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="bookTabContent">
                        {{-- Tab 1: Sinopsis --}}
                        <div class="tab-pane fade show active" id="sinopsis-pane" role="tabpanel" aria-labelledby="sinopsis-tab">
                            <div class="bg-light p-4 rounded-3 border mb-4">
                                <div class="d-flex align-items-center gap-2 mb-3 text-del fw-semibold small">
                                    <i class="bi bi-quote fs-4 lh-1"></i> Rangkuman Isi Buku
                                </div>
                                <p class="text-secondary lh-lg mb-0" style="text-align: justify; font-size: 0.95rem;">
                                    {{ $buku->sinopsis ?? 'Buku ini merupakan bagian dari koleksi inti literatur akademik Institut Teknologi Del. Belum ada sinopsis deskriptif yang terunggah untuk judul ini, silakan membaca langsung di ruang baca perpustakaan.' }}
                                </p>
                            </div>

                            <div class="d-flex align-items-center gap-3 p-3 rounded-3 border bg-white">
                                <div class="brand-badge" style="width: 44px; height: 44px; font-size: 1.2rem;">
                                    <i class="bi bi-lightbulb-fill text-warning"></i>
                                </div>
                                <div class="small">
                                    <div class="fw-bold text-dark">Rekomendasi untuk Mahasiswa Del</div>
                                    <span class="text-muted">Buku ini sangat direkomendasikan untuk menunjang praktikum, mata kuliah terkait, dan pengerjaan Proyek Akhir / Skripsi.</span>
                                </div>
                            </div>
                        </div>

                        {{-- Tab 2: Spesifikasi Detail --}}
                        <div class="tab-pane fade" id="spesifikasi-pane" role="tabpanel" aria-labelledby="spesifikasi-tab">
                            <div class="table-responsive">
                                <table class="table specs-table mb-0">
                                    <tbody>
                                        <tr>
                                            <th>Judul Lengkap</th>
                                            <td>{{ $buku->judul }}</td>
                                        </tr>
                                        <tr>
                                            <th>Nomor ISBN-13</th>
                                            <td class="font-monospace text-del fw-bold">{{ $buku->isbn }}</td>
                                        </tr>
                                        <tr>
                                            <th>Nama Pengarang / Penulis</th>
                                            <td>{{ $buku->penulis }}</td>
                                        </tr>
                                        <tr>
                                            <th>Penerbit Resmi</th>
                                            <td>{{ $buku->penerbit }}</td>
                                        </tr>
                                        <tr>
                                            <th>Tahun Penerbitan</th>
                                            <td>{{ $buku->tahun_terbit }}</td>
                                        </tr>
                                        <tr>
                                            <th>Kategori Klasifikasi</th>
                                            <td>{{ $buku->kategori->nama_kategori ?? 'Umum' }} (Kode: <code>{{ $buku->kategori->kode_kategori ?? 'DEL' }}</code>)</td>
                                        </tr>
                                        <tr>
                                            <th>Total Stok Inventaris</th>
                                            <td>{{ $buku->stok }} Eksemplar Fisik</td>
                                        </tr>
                                        <tr>
                                            <th>Bahasa Pengantar</th>
                                            <td>Bahasa Indonesia / Inggris Terjemahan</td>
                                        </tr>
                                        <tr>
                                            <th>Lokasi Simpan Kampus</th>
                                            <td>Gedung Perpustakaan IT Del, Laguboti, Toba</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Tab 3: Ketentuan Peminjaman --}}
                        <div class="tab-pane fade" id="aturan-pane" role="tabpanel" aria-labelledby="aturan-tab">
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <div class="p-3 border rounded-3 bg-light h-100">
                                        <div class="fw-bold text-dark mb-1"><i class="bi bi-1-circle-fill text-del me-1"></i> Masa Peminjaman</div>
                                        <p class="small text-muted mb-0">Maksimal peminjaman adalah 7 hari kalender terhitung sejak hari transaksi diajukan.</p>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="p-3 border rounded-3 bg-light h-100">
                                        <div class="fw-bold text-dark mb-1"><i class="bi bi-2-circle-fill text-del me-1"></i> Perpanjangan Mandiri</div>
                                        <p class="small text-muted mb-0">Buku dapat diperpanjang secara mandiri sebanyak 1 kali (+7 hari) melalui menu Pinjaman Saya sebelum jatuh tempo.</p>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="p-3 border rounded-3 bg-light h-100">
                                        <div class="fw-bold text-dark mb-1"><i class="bi bi-3-circle-fill text-del me-1"></i> Kuota Maksimal Peminjam</div>
                                        <p class="small text-muted mb-0">Setiap mahasiswa Institut Teknologi Del memiliki kuota aktif maksimal 3 buku pada saat bersamaan.</p>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="p-3 border rounded-3 bg-light h-100">
                                        <div class="fw-bold text-dark mb-1"><i class="bi bi-4-circle-fill text-danger me-1"></i> Denda Keterlambatan</div>
                                        <p class="small text-muted mb-0">Denda dihitung otomatis sebesar Rp1.000 per hari keterlambatan untuk setiap judul buku yang tertunggak.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- Koleksi Buku Serupa / Rekomendasi Terkait (Inspirasi perpus.org) --}}
        @if(isset($relatedBukus) && $relatedBukus->count() > 0)
            <div class="mb-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <h2 class="h5 fw-bold text-dark mb-1">
                            <i class="bi bi-collection-fill text-del me-2"></i>Rekomendasi Buku Serupa
                        </h2>
                        <p class="text-muted small mb-0">Koleksi lain dalam kategori <strong>{{ $buku->kategori->nama_kategori ?? 'yang sama' }}</strong></p>
                    </div>
                    <a href="{{ route('katalog.index', ['kategori_id' => $buku->kategori_id]) }}" class="btn btn-outline-del btn-sm rounded-pill px-3">
                        Lihat Kategori Ini <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>

                <div class="row g-3">
                    @foreach($relatedBukus as $rel)
                        <div class="col-6 col-md-3">
                            <div class="card card-del h-100 border-0 p-3 shadow-sm book-card-modern">
                                <div class="book-card-cover-area rounded-3 mb-2" style="height: 130px; display: flex; align-items: center; justify-content: center;">
                                    <i class="bi bi-book-half text-del fs-1 opacity-75"></i>
                                </div>
                                <div class="d-flex flex-column flex-grow-1">
                                    <h3 class="h6 fw-bold text-dark mb-1 text-truncate" title="{{ $rel->judul }}">
                                        <a href="{{ route('katalog.show', $rel) }}" class="text-dark text-decoration-none">
                                            {{ $rel->judul }}
                                        </a>
                                    </h3>
                                    <div class="text-muted small mb-2 text-truncate">{{ $rel->penulis }}</div>
                                    <div class="mt-auto d-flex justify-content-between align-items-center">
                                        <span class="badge {{ $rel->stok > 0 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} rounded-pill" style="font-size: 0.65rem;">
                                            {{ $rel->stok > 0 ? $rel->stok . ' Tersedia' : 'Habis' }}
                                        </span>
                                        <a href="{{ route('katalog.show', $rel) }}" class="btn btn-sm btn-outline-del rounded-pill px-2 py-0" style="font-size: 0.75rem;">
                                            Lihat
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Navigasi Kembali --}}
        <div class="text-center py-3">
            <a href="{{ route('katalog.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Katalog Lengkap
            </a>
        </div>
    </div>

    <script>
    function copyCurrentLink() {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(window.location.href).then(function() {
                var feedback = document.getElementById('copyFeedback');
                if (feedback) {
                    feedback.style.display = 'block';
                    setTimeout(function() { feedback.style.display = 'none'; }, 2500);
                }
            });
        }
    }
    </script>
</x-layout>
