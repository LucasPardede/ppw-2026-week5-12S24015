<x-layout title="Tambah Kategori Baru — SIPUS-Del">
    {{-- Breadcrumb --}}
    <nav aria-label="Breadcrumb" class="mb-3">
        <ol class="breadcrumb breadcrumb-del mb-0" style="font-size: 0.82rem;">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('kategori.index') }}">Kelola Kategori</a></li>
            <li class="breadcrumb-item active" aria-current="page">Tambah Kategori</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h1 class="fw-bold mb-1 text-dark fs-3">
                        <i class="bi bi-tag-fill text-del me-2" aria-hidden="true"></i>Tambah Kategori Baru
                    </h1>
                    <p class="text-muted small mb-0">Tambahkan klasifikasi kategori untuk koleksi perpustakaan.</p>
                </div>
                <a href="{{ route('kategori.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                    <i class="bi bi-arrow-left me-1" aria-hidden="true"></i> Kembali
                </a>
            </div>

            <div class="card card-del border-0 shadow-sm">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('kategori.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="kode_kategori" class="form-label fw-semibold">
                                Kode Kategori <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control @error('kode_kategori') is-invalid @enderror font-monospace" 
                                   id="kode_kategori" 
                                   name="kode_kategori" 
                                   value="{{ old('kode_kategori') }}" 
                                   placeholder="Contoh: IF-WEB, CS-SEC" 
                                   maxlength="10" 
                                   required>
                            <div class="form-text small text-muted">Maksimal 10 karakter alfanumerik unik (contoh: <code>IF-WEB</code>).</div>
                            @error('kode_kategori')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="nama_kategori" class="form-label fw-semibold">
                                Nama Kategori <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control @error('nama_kategori') is-invalid @enderror" 
                                   id="nama_kategori" 
                                   name="nama_kategori" 
                                   value="{{ old('nama_kategori') }}" 
                                   placeholder="Contoh: Pemrograman Web & Sistem Terdistribusi" 
                                   minlength="3" 
                                   maxlength="100" 
                                   required>
                            <div class="form-text small text-muted">Nama deskriptif kategori, minimal 3 karakter.</div>
                            @error('nama_kategori')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-end gap-2 pt-2 border-top">
                            <a href="{{ route('kategori.index') }}" class="btn btn-light rounded-pill px-4">Batal</a>
                            <button type="submit" class="btn btn-del rounded-pill px-4 shadow-sm">
                                <i class="bi bi-save me-1" aria-hidden="true"></i> Simpan Kategori
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layout>
