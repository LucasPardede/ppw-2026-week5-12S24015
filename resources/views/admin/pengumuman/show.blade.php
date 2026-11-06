<x-layout title="Detail Pengumuman #{{ $pengumuman->id }} — Admin SIPUS-Del">
    {{-- Breadcrumb --}}
    <nav aria-label="Breadcrumb" class="mb-3">
        <ol class="breadcrumb breadcrumb-del mb-0" style="font-size: 0.82rem;">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.pengumuman.index') }}">Kelola Pengumuman</a></li>
            <li class="breadcrumb-item active text-truncate" style="max-width: 250px;" aria-current="page">{{ $pengumuman->judul }}</li>
        </ol>
    </nav>

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h1 class="fw-bold mb-1 text-dark fs-3">
                <i class="bi bi-file-text text-del me-2" aria-hidden="true"></i>Rincian Pengumuman
            </h1>
            <p class="text-muted mb-0 small">Pratinjau konten pengumuman sebagaimana ditampilkan kepada mahasiswa.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.pengumuman.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
            <a href="{{ route('admin.pengumuman.edit', $pengumuman->id) }}" class="btn btn-del btn-sm rounded-pill px-3">
                <i class="bi bi-pencil me-1"></i> Edit Pengumuman
            </a>
            <form action="{{ route('admin.pengumuman.pin', $pengumuman->id) }}" method="POST" class="d-inline">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn {{ $pengumuman->is_pinned ? 'btn-danger' : 'btn-outline-danger' }} btn-sm rounded-pill px-3">
                    <i class="bi bi-pin-angle-fill me-1"></i> {{ $pengumuman->is_pinned ? 'Lepas Sematan' : 'Sematkan di Atas' }}
                </button>
            </form>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-white p-4 border-bottom">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                        {!! $pengumuman->kategori_badge !!}
                        @if($pengumuman->is_pinned)
                            <span class="badge bg-danger text-white rounded-pill px-2 py-1 small">
                                <i class="bi bi-pin-fill me-1"></i>DISEMATKAN
                            </span>
                        @endif
                        @if($pengumuman->is_aktif)
                            <span class="badge bg-success-subtle text-success rounded-pill px-2 py-1 small">
                                <i class="bi bi-check-circle me-1"></i>Tayang Aktif
                            </span>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2 py-1 small">
                                <i class="bi bi-eye-slash me-1"></i>Draft (Tidak Muncul di Mahasiswa)
                            </span>
                        @endif
                    </div>

                    <h2 class="h3 fw-bold text-dark mb-3">{{ $pengumuman->judul }}</h2>

                    <div class="d-flex flex-wrap gap-3 small text-muted">
                        <span><i class="bi bi-person me-1 text-del"></i>Penulis: <strong>{{ $pengumuman->author->name ?? 'Admin' }}</strong></span>
                        <span>&bull;</span>
                        <span><i class="bi bi-calendar3 me-1 text-del"></i>{{ $pengumuman->formatted_date }} ({{ $pengumuman->created_at->format('H:i') }} WIB)</span>
                    </div>
                </div>

                <div class="card-body p-4 fs-6" style="line-height: 1.8;">
                    {!! nl2br(e($pengumuman->konten)) !!}
                </div>

                <div class="card-footer bg-light p-3 d-flex justify-content-between align-items-center">
                    <span class="text-muted small">
                        Tautan Publik Mahasiswa: 
                        <a href="{{ route('pengumuman.show', $pengumuman->slug) }}" target="_blank" class="text-del fw-semibold">
                            /pengumuman/{{ $pengumuman->slug }} <i class="bi bi-box-arrow-up-right ms-1"></i>
                        </a>
                    </span>
                    <form id="deleteDetailForm{{ $pengumuman->id }}" action="{{ route('admin.pengumuman.destroy', $pengumuman->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="button" 
                                class="btn btn-outline-danger btn-sm rounded-pill px-3"
                                data-delete-trigger
                                data-delete-form="deleteDetailForm{{ $pengumuman->id }}"
                                data-delete-title="{{ $pengumuman->judul }}"
                                data-delete-subtext="Pengumuman ini akan dihapus secara permanen.">
                            <i class="bi bi-trash me-1"></i> Hapus Pengumuman
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h3 class="h6 fw-bold mb-0">Status &amp; Akses Mahasiswa</h3>
                </div>
                <div class="card-body p-4 small">
                    <div class="mb-3">
                        <label class="text-muted d-block mb-1">Status Publikasi:</label>
                        @if($pengumuman->is_aktif)
                            <div class="alert alert-success py-2 px-3 mb-0 rounded-3">
                                <i class="bi bi-check-circle-fill me-1"></i> Pengumuman ini <strong>dapat dilihat</strong> oleh mahasiswa.
                            </div>
                        @else
                            <div class="alert alert-warning py-2 px-3 mb-0 rounded-3">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i> Mode Draft. Mahasiswa <strong>tidak dapat melihat</strong> edaran ini.
                            </div>
                        @endif
                    </div>

                    <div>
                        <label class="text-muted d-block mb-1">Status Penempatan:</label>
                        @if($pengumuman->is_pinned)
                            <p class="mb-0 text-danger fw-semibold">
                                <i class="bi bi-pin-angle-fill me-1"></i> Disematkan di deretan teratas dan muncul di running ticker katalog.
                            </p>
                        @else
                            <p class="mb-0 text-muted">
                                Berada di urutan kronologis standar sesuai tanggal dibuat.
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layout>
