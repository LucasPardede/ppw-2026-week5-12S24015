<x-layout title="Ubah Data Buku: {{ $buku->judul }} — SIPUS-Del">
    {{-- Breadcrumb --}}
    <nav aria-label="Breadcrumb" class="mb-3">
        <ol class="breadcrumb breadcrumb-del mb-0" style="font-size: 0.82rem;">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('buku.index') }}">Kelola Buku</a></li>
            <li class="breadcrumb-item active" aria-current="page">Ubah Buku</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h1 class="fw-bold mb-1 text-dark fs-3">
                        <i class="bi bi-pencil-square text-del me-2" aria-hidden="true"></i>Ubah Data Buku
                    </h1>
                    <p class="text-muted small mb-0">Perbarui informasi koleksi buku <strong>{{ $buku->judul }}</strong>.</p>
                </div>
                <a href="{{ route('buku.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                    <i class="bi bi-arrow-left me-1" aria-hidden="true"></i> Kembali ke Daftar
                </a>
            </div>

            <div class="card card-del border-0 shadow-sm">
                <div class="card-body p-4 p-md-5">
                    <form method="POST" action="{{ route('buku.update', $buku) }}">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            {{-- Judul Buku --}}
                            <div class="col-12">
                                <label for="judul" class="form-label fw-semibold">
                                    Judul Buku <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control @error('judul') is-invalid @enderror" 
                                       id="judul" 
                                       name="judul" 
                                       value="{{ old('judul', $buku->judul) }}" 
                                       minlength="5" 
                                       maxlength="200" 
                                       required>
                                <div class="form-text small text-muted">Minimal 5 karakter, maksimal 200 karakter.</div>
                                @error('judul')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- ISBN & Kategori --}}
                            <div class="col-md-6">
                                <label for="isbn" class="form-label fw-semibold">
                                    Nomor ISBN <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control @error('isbn') is-invalid @enderror font-monospace" 
                                       id="isbn" 
                                       name="isbn" 
                                       value="{{ old('isbn', $buku->isbn) }}" 
                                       maxlength="20" 
                                       required>
                                @error('isbn')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="kategori_id" class="form-label fw-semibold">
                                    Kategori Buku <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('kategori_id') is-invalid @enderror" id="kategori_id" name="kategori_id" required>
                                    <option value="">-- Pilih Kategori --</option>
                                    @foreach($kategoris as $kategori)
                                        <option value="{{ $kategori->id }}" {{ old('kategori_id', $buku->kategori_id) == $kategori->id ? 'selected' : '' }}>
                                            {{ $kategori->kode_kategori }} — {{ $kategori->nama_kategori }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('kategori_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Penulis & Penerbit --}}
                            <div class="col-md-6">
                                <label for="penulis" class="form-label fw-semibold">
                                    Nama Penulis / Pengarang <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control @error('penulis') is-invalid @enderror" 
                                       id="penulis" 
                                       name="penulis" 
                                       value="{{ old('penulis', $buku->penulis) }}" 
                                       maxlength="150" 
                                       required>
                                @error('penulis')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="penerbit" class="form-label fw-semibold">
                                    Penerbit <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control @error('penerbit') is-invalid @enderror" 
                                       id="penerbit" 
                                       name="penerbit" 
                                       value="{{ old('penerbit', $buku->penerbit) }}" 
                                       maxlength="100" 
                                       required>
                                @error('penerbit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Tahun Terbit & Stok --}}
                            <div class="col-md-6">
                                <label for="tahun_terbit" class="form-label fw-semibold">
                                    Tahun Terbit <span class="text-danger">*</span>
                                </label>
                                <input type="number" 
                                       class="form-control @error('tahun_terbit') is-invalid @enderror" 
                                       id="tahun_terbit" 
                                       name="tahun_terbit" 
                                       value="{{ old('tahun_terbit', $buku->tahun_terbit) }}" 
                                       min="1900" 
                                       max="{{ date('Y') }}" 
                                       required>
                                @error('tahun_terbit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="stok" class="form-label fw-semibold">
                                    Jumlah Stok Eksemplar <span class="text-danger">*</span>
                                </label>
                                <input type="number" 
                                       class="form-control @error('stok') is-invalid @enderror" 
                                       id="stok" 
                                       name="stok" 
                                       value="{{ old('stok', $buku->stok) }}" 
                                       min="0" 
                                       required>
                                @error('stok')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Sinopsis / Deskripsi Ringkas --}}
                            <div class="col-12">
                                <label for="sinopsis" class="form-label fw-semibold">
                                    Sinopsis / Deskripsi Buku <span class="text-muted fw-normal">(Opsional)</span>
                                </label>
                                <textarea class="form-control @error('sinopsis') is-invalid @enderror" 
                                          id="sinopsis" 
                                          name="sinopsis" 
                                          rows="4">{{ old('sinopsis', $buku->sinopsis) }}</textarea>
                                @error('sinopsis')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 pt-4 mt-4 border-top">
                            <a href="{{ route('buku.index') }}" class="btn btn-light rounded-pill px-4">Batal</a>
                            <button type="submit" class="btn btn-del rounded-pill px-4 shadow-sm">
                                <i class="bi bi-check-lg me-1" aria-hidden="true"></i> Perbarui Koleksi Buku
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layout>
