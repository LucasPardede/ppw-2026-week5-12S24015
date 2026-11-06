<x-layout title="Edit Pengumuman — Admin SIPUS-Del">
    {{-- Breadcrumb --}}
    <nav aria-label="Breadcrumb" class="mb-3">
        <ol class="breadcrumb breadcrumb-del mb-0" style="font-size: 0.82rem;">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.pengumuman.index') }}">Kelola Pengumuman</a></li>
            <li class="breadcrumb-item active" aria-current="page">Edit Pengumuman</li>
        </ol>
    </nav>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h1 class="fw-bold mb-1 text-dark fs-3">
                        <i class="bi bi-pencil-square text-del me-2" aria-hidden="true"></i>Edit Pengumuman
                    </h1>
                    <p class="text-muted small mb-0">Perbarui rincian pengumuman yang telah dipublikasikan.</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.pengumuman.show', $pengumuman->id) }}" class="btn btn-outline-info btn-sm rounded-pill px-3">
                        <i class="bi bi-eye me-1"></i> Pratinjau
                    </a>
                    <a href="{{ route('admin.pengumuman.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                        <i class="bi bi-arrow-left me-1"></i> Kembali
                    </a>
                </div>
            </div>

            <div class="card card-del border-0 shadow-sm">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('admin.pengumuman.update', $pengumuman->id) }}">
                        @csrf
                        @method('PUT')

                        {{-- Judul --}}
                        <div class="mb-3">
                            <label for="judul" class="form-label fw-semibold">
                                Judul Pengumuman <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control @error('judul') is-invalid @enderror" 
                                   id="judul" 
                                   name="judul" 
                                   value="{{ old('judul', $pengumuman->judul) }}" 
                                   minlength="5" 
                                   maxlength="255" 
                                   required>
                            @error('judul')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Kategori & Visibilitas --}}
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="kategori" class="form-label fw-semibold">
                                    Kategori Pengumuman <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('kategori') is-invalid @enderror" 
                                        id="kategori" 
                                        name="kategori" 
                                        required>
                                    <option value="penting" {{ old('kategori', $pengumuman->kategori) === 'penting' ? 'selected' : '' }}>Penting / Urgent (Merah)</option>
                                    <option value="layanan" {{ old('kategori', $pengumuman->kategori) === 'layanan' ? 'selected' : '' }}>Layanan & Jam Buka (Biru Muda)</option>
                                    <option value="kegiatan" {{ old('kategori', $pengumuman->kategori) === 'kegiatan' ? 'selected' : '' }}>Kegiatan & Literasi (Hijau)</option>
                                    <option value="info" {{ old('kategori', $pengumuman->kategori) === 'info' ? 'selected' : '' }}>Informasi Umum (Ungu Del)</option>
                                </select>
                                @error('kategori')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold d-block">Opsi Publikasi</label>
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" role="switch" id="is_aktif" name="is_aktif" value="1" {{ old('is_aktif', $pengumuman->is_aktif) ? 'checked' : '' }}>
                                    <label class="form-check-label small fw-semibold" for="is_aktif">
                                        Status Tayang Aktif
                                    </label>
                                </div>
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" role="switch" id="is_pinned" name="is_pinned" value="1" {{ old('is_pinned', $pengumuman->is_pinned) ? 'checked' : '' }}>
                                    <label class="form-check-label small fw-semibold text-danger" for="is_pinned">
                                        <i class="bi bi-pin-angle-fill me-1"></i>Sematkan di Bagian Atas
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- Isi Konten --}}
                        <div class="mb-4">
                            <label for="konten" class="form-label fw-semibold">
                                Isi Pengumuman Lengkap <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control @error('konten') is-invalid @enderror" 
                                      id="konten" 
                                      name="konten" 
                                      rows="9" 
                                      required>{{ old('konten', $pengumuman->konten) }}</textarea>
                            @error('konten')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Tombol Aksi --}}
                        <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                            <a href="{{ route('admin.pengumuman.index') }}" class="btn btn-outline-secondary px-4 rounded-pill">
                                Batal
                            </a>
                            <button type="submit" class="btn btn-del px-4 rounded-pill shadow-sm">
                                <i class="bi bi-check-circle me-1" aria-hidden="true"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Panel Info Audit --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h2 class="h6 fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                        <i class="bi bi-info-circle text-del"></i>
                        <span>Informasi Meta Pengumuman</span>
                    </h2>
                </div>
                <div class="card-body p-4 small">
                    <ul class="list-unstyled mb-0" style="line-height: 2;">
                        <li class="d-flex justify-content-between border-bottom pb-1">
                            <span class="text-muted">ID Pengumuman:</span>
                            <span class="font-monospace fw-bold">#{{ $pengumuman->id }}</span>
                        </li>
                        <li class="d-flex justify-content-between border-bottom py-1">
                            <span class="text-muted">Penulis:</span>
                            <strong>{{ $pengumuman->author->name ?? 'Admin' }}</strong>
                        </li>
                        <li class="d-flex justify-content-between border-bottom py-1">
                            <span class="text-muted">Slug:</span>
                            <span class="text-truncate font-monospace" style="max-width: 150px;">{{ $pengumuman->slug }}</span>
                        </li>
                        <li class="d-flex justify-content-between border-bottom py-1">
                            <span class="text-muted">Dibuat pada:</span>
                            <span>{{ $pengumuman->created_at->format('d M Y H:i') }}</span>
                        </li>
                        <li class="d-flex justify-content-between pt-1">
                            <span class="text-muted">Terakhir diubah:</span>
                            <span>{{ $pengumuman->updated_at->diffForHumans() }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</x-layout>
