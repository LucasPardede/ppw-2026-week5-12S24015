<x-layout title="Kelola Pengumuman — Admin SIPUS-Del">
    {{-- Breadcrumb --}}
    <nav aria-label="Breadcrumb" class="mb-3">
        <ol class="breadcrumb breadcrumb-del mb-0" style="font-size: 0.82rem;">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Kelola Pengumuman</li>
        </ol>
    </nav>

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h1 class="fw-bold mb-1 text-dark fs-3">
                <i class="bi bi-megaphone-fill text-del me-2" aria-hidden="true"></i>Kelola Pengumuman &amp; Edaran
            </h1>
            <p class="text-muted mb-0 small">Publikasikan informasi resmi perpustakaan, pengumuman sirkulasi, atau kegiatan literasi untuk mahasiswa.</p>
        </div>
        <div>
            <a href="{{ route('admin.pengumuman.create') }}" class="btn btn-del rounded-pill px-4 shadow-sm">
                <i class="bi bi-plus-circle me-1" aria-hidden="true"></i> Tambah Pengumuman Baru
            </a>
        </div>
    </div>

    {{-- Filter dan Pencarian --}}
    <div class="card card-del border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('admin.pengumuman.index') }}" class="row g-2 align-items-center">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted border-end-0">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}" 
                               class="form-control border-start-0 ps-0" 
                               placeholder="Cari judul atau isi pengumuman...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="kategori" class="form-select">
                        <option value="">-- Semua Kategori --</option>
                        <option value="penting" {{ request('kategori') === 'penting' ? 'selected' : '' }}>Penting / Urgent</option>
                        <option value="layanan" {{ request('kategori') === 'layanan' ? 'selected' : '' }}>Layanan & Sirkulasi</option>
                        <option value="kegiatan" {{ request('kategori') === 'kegiatan' ? 'selected' : '' }}>Kegiatan & Acara</option>
                        <option value="info" {{ request('kategori') === 'info' ? 'selected' : '' }}>Info Umum</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">-- Semua Status --</option>
                        <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Aktif / Tayang</option>
                        <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Draft / Nonaktif</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-del flex-grow-1">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                    @if(request('search') || request('kategori') || request('status') !== null)
                        <a href="{{ route('admin.pengumuman.index') }}" class="btn btn-outline-secondary" title="Reset Filter">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Tabel Pengumuman --}}
    <div class="card card-del border-0 shadow-sm overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h2 class="h6 card-title mb-0 fw-semibold text-secondary">
                <i class="bi bi-list-columns-reverse me-2" aria-hidden="true"></i>Daftar Pengumuman Terdaftar
            </h2>
            <span class="badge badge-del px-3 py-2 rounded-pill" style="font-size: 0.72rem;">
                Total: {{ $pengumumans->total() }} Pengumuman
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4" style="width: 50px;">No</th>
                        <th>Judul Pengumuman</th>
                        <th style="width: 130px;">Kategori</th>
                        <th style="width: 100px;">Status</th>
                        <th style="width: 140px;">Sematkan</th>
                        <th style="width: 150px;">Tanggal Dibuat</th>
                        <th class="text-end pe-4" style="width: 180px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pengumumans as $index => $item)
                        <tr class="{{ $item->is_pinned ? 'table-warning-subtle' : '' }}">
                            <td class="ps-4 text-muted small">
                                {{ $pengumumans->firstItem() + $index }}
                            </td>
                            <td>
                                <div class="fw-bold text-dark mb-1">
                                    <a href="{{ route('admin.pengumuman.show', $item->id) }}" class="text-decoration-none text-dark hover-purple">
                                        {{ $item->judul }}
                                    </a>
                                </div>
                                <div class="small text-muted text-truncate" style="max-width: 380px;">
                                    {{ Str::limit(strip_tags($item->konten), 90) }}
                                </div>
                                <div class="small text-muted mt-1" style="font-size: 0.72rem;">
                                    <i class="bi bi-person me-1"></i>Oleh: {{ $item->author->name ?? 'Admin' }}
                                </div>
                            </td>
                            <td>
                                {!! $item->kategori_badge !!}
                            </td>
                            <td>
                                @if($item->is_aktif)
                                    <span class="badge bg-success-subtle text-success rounded-pill px-2 py-1 small">
                                        <i class="bi bi-check-circle me-1"></i>Tayang
                                    </span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2 py-1 small">
                                        <i class="bi bi-eye-slash me-1"></i>Draft
                                    </span>
                                @endif
                            </td>
                            <td>
                                <form action="{{ route('admin.pengumuman.pin', $item->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" 
                                            class="btn btn-sm {{ $item->is_pinned ? 'btn-danger' : 'btn-outline-secondary' }} rounded-pill px-2 py-1" 
                                            style="font-size: 0.72rem;"
                                            title="{{ $item->is_pinned ? 'Klik untuk lepas sematan' : 'Klik untuk sematkan di atas' }}">
                                        <i class="bi bi-pin-angle-fill me-1"></i>
                                        {{ $item->is_pinned ? 'Pinned' : 'Pin' }}
                                    </button>
                                </form>
                            </td>
                            <td class="small text-muted">
                                <div>{{ $item->created_at->format('d M Y') }}</div>
                                <div style="font-size: 0.72rem;">{{ $item->created_at->format('H:i') }} WIB</div>
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.pengumuman.show', $item->id) }}" 
                                       class="btn btn-outline-info" 
                                       title="Lihat Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.pengumuman.edit', $item->id) }}" 
                                       class="btn btn-outline-warning" 
                                       title="Edit Pengumuman">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form id="deletePengumumanForm{{ $item->id }}" 
                                          action="{{ route('admin.pengumuman.destroy', $item->id) }}" 
                                          method="POST" 
                                          class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" 
                                                class="btn btn-outline-danger" 
                                                title="Hapus Pengumuman"
                                                data-delete-trigger
                                                data-delete-form="deletePengumumanForm{{ $item->id }}"
                                                data-delete-title="{{ $item->judul }}"
                                                data-delete-subtext="Pengumuman yang dihapus tidak akan dapat dilihat lagi oleh mahasiswa.">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-megaphone fs-1 d-block mb-2 text-secondary"></i>
                                    <p class="mb-2 fw-semibold">Belum ada pengumuman yang sesuai kriteria.</p>
                                    @if(request('search') || request('kategori') || request('status') !== null)
                                        <a href="{{ route('admin.pengumuman.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill">
                                            Reset Filter
                                        </a>
                                    @else
                                        <a href="{{ route('admin.pengumuman.create') }}" class="btn btn-del btn-sm rounded-pill">
                                            Buat Pengumuman Sekarang
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($pengumumans->hasPages())
            <div class="card-footer bg-white py-3 border-top d-flex justify-content-between align-items-center">
                <div class="small text-muted">
                    Menampilkan {{ $pengumumans->firstItem() }} - {{ $pengumumans->lastItem() }} dari {{ $pengumumans->total() }} pengumuman
                </div>
                <div>
                    {{ $pengumumans->links() }}
                </div>
            </div>
        @endif
    </div>
</x-layout>
