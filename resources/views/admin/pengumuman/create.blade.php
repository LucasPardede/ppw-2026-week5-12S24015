<x-layout title="Tambah Pengumuman Baru — Admin SIPUS-Del">
    {{-- Breadcrumb --}}
    <nav aria-label="Breadcrumb" class="mb-3">
        <ol class="breadcrumb breadcrumb-del mb-0" style="font-size: 0.82rem;">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.pengumuman.index') }}">Kelola Pengumuman</a></li>
            <li class="breadcrumb-item active" aria-current="page">Buat Pengumuman Baru</li>
        </ol>
    </nav>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h1 class="fw-bold mb-1 text-dark fs-3">
                        <i class="bi bi-megaphone-fill text-del me-2" aria-hidden="true"></i>Buat Pengumuman Baru
                    </h1>
                    <p class="text-muted small mb-0">Publikasikan informasi atau edaran yang akan langsung muncul di portal mahasiswa.</p>
                </div>
                <a href="{{ route('admin.pengumuman.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                    <i class="bi bi-arrow-left me-1" aria-hidden="true"></i> Kembali
                </a>
            </div>

            <div class="card card-del border-0 shadow-sm">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('admin.pengumuman.store') }}">
                        @csrf

                        {{-- Judul --}}
                        <div class="mb-3">
                            <label for="judul" class="form-label fw-semibold">
                                Judul Pengumuman <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control @error('judul') is-invalid @enderror" 
                                   id="judul" 
                                   name="judul" 
                                   value="{{ old('judul') }}" 
                                   placeholder="Contoh: Perpanjangan Waktu Layanan Sirkulasi Menjelang UTS" 
                                   minlength="5" 
                                   maxlength="255" 
                                   required>
                            <div class="form-text small text-muted">Judul singkat, padat, dan jelas (minimal 5 karakter).</div>
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
                                    <option value="">-- Pilih Kategori --</option>
                                    <option value="penting" {{ old('kategori') === 'penting' ? 'selected' : '' }}>Penting / Urgent (Merah)</option>
                                    <option value="layanan" {{ old('kategori') === 'layanan' ? 'selected' : '' }}>Layanan & Jam Buka (Biru Muda)</option>
                                    <option value="kegiatan" {{ old('kategori') === 'kegiatan' ? 'selected' : '' }}>Kegiatan & Literasi (Hijau)</option>
                                    <option value="info" {{ old('kategori', 'info') === 'info' ? 'selected' : '' }}>Informasi Umum (Ungu Del)</option>
                                </select>
                                @error('kategori')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold d-block">Opsi Publikasi</label>
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" role="switch" id="is_aktif" name="is_aktif" value="1" {{ old('is_aktif', '1') ? 'checked' : '' }}>
                                    <label class="form-check-label small fw-semibold" for="is_aktif">
                                        Langsung Publikasikan (Tayang Aktif)
                                    </label>
                                </div>
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" role="switch" id="is_pinned" name="is_pinned" value="1" {{ old('is_pinned') ? 'checked' : '' }}>
                                    <label class="form-check-label small fw-semibold text-danger" for="is_pinned">
                                        <i class="bi bi-pin-angle-fill me-1"></i>Sematkan di Atas (Highlight Utama)
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
                                      rows="8" 
                                      placeholder="Tuliskan detail pengumuman secara lengkap, gunakan baris baru untuk memisahkan paragraf atau poin-poin..." 
                                      required>{{ old('konten') }}</textarea>
                            <div class="form-text small text-muted">Mendukung format teks multisusun dan paragraf terstruktur. Minimal 10 karakter.</div>
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
                                <i class="bi bi-send-fill me-1" aria-hidden="true"></i> Publikasikan Pengumuman
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Panel Panduan --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 mb-4" style="background: rgba(76, 29, 149, 0.03); border: 1px solid rgba(76, 29, 149, 0.1) !important;">
                <div class="card-body p-4">
                    <h2 class="h6 fw-bold text-del mb-3 d-flex align-items-center gap-2">
                        <i class="bi bi-lightbulb-fill text-warning"></i>
                        <span>Petunjuk Penulisan Edaran</span>
                    </h2>
                    <ul class="small text-muted ps-3 mb-0" style="line-height: 1.8;">
                        <li><strong>Kategori Penting:</strong> Gunakan untuk pengumuman yang berdampak langsung pada sanksi denda, pengembalian buku, atau perubahan kebijakan mendesak.</li>
                        <li><strong>Kategori Layanan:</strong> Cocok untuk jadwal buka perpustakaan selama libur semester atau periode ujian.</li>
                        <li><strong>Sematkan (Pin):</strong> Pengumuman yang disematkan akan muncul pada ticker beranda katalog dan banner utama halaman pengumuman mahasiswa.</li>
                        <li>Pastikan menggunakan bahasa resmi dan komunikatif khas Institut Teknologi Del.</li>
                    </ul>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 p-4 text-center">
                <i class="bi bi-megaphone text-del display-4 mb-2 opacity-75"></i>
                <h3 class="h6 fw-bold text-dark">Langsung Terhubung ke Mahasiswa</h3>
                <p class="text-muted small mb-0">
                    Setiap pengumuman yang aktif akan langsung muncul pada menu "Pengumuman" serta cuplikan di beranda pencarian katalog.
                </p>
            </div>
        </div>
    </div>
</x-layout>
