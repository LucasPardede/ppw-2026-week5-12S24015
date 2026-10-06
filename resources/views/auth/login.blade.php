<x-layout title="Masuk Sistem — SIPUS-Del">
    <div class="row justify-content-center py-4">
        <div class="col-md-8 col-lg-5">
            <div class="card card-del shadow border-0 overflow-hidden">
                <div class="card-header bg-del text-white p-4 text-center">
                    <div class="d-inline-flex p-3 rounded-circle bg-white bg-opacity-10 mb-3">
                        <i class="bi bi-book-half fs-1 text-warning"></i>
                    </div>
                    <h3 class="fw-bold mb-1">SIPUS-Del</h3>
                    <p class="mb-0 text-white-50 small">Sistem Informasi Perpustakaan Kampus Del</p>
                </div>

                <div class="card-body p-4 p-md-5">
                    {{-- Banner Informasi Simulasi Autentikasi CIS --}}
                    <div class="alert alert-primary bg-light border-primary border-opacity-25 rounded-3 mb-4 p-3 small">
                        <div class="d-flex align-items-start gap-2">
                            <i class="bi bi-info-circle-fill text-primary fs-5 mt-n1"></i>
                            <div>
                                <strong class="text-primary d-block">Autentikasi Terintegrasi CIS</strong>
                                <span class="text-muted">
                                    Mahasiswa masuk menggunakan akun surel Del (mode simulasi aktif). Administrator menggunakan kredensial pustakawan lokal.
                                </span>
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('login.post') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">Alamat Email CIS / Del</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted border-end-0">
                                    <i class="bi bi-envelope"></i>
                                </span>
                                <input type="email" 
                                       class="form-control @error('email') is-invalid @enderror border-start-0 ps-0" 
                                       id="email" 
                                       name="email" 
                                       value="{{ old('email') }}" 
                                       placeholder="nama@del.ac.id atau nim@students.del.ac.id" 
                                       required 
                                       autofocus>
                            </div>
                            @error('email')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label fw-semibold">Kata Sandi</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted border-end-0">
                                    <i class="bi bi-key"></i>
                                </span>
                                <input type="password" 
                                       class="form-control @error('password') is-invalid @enderror border-start-0 ps-0" 
                                       id="password" 
                                       name="password" 
                                       placeholder="••••••••" 
                                       required>
                            </div>
                            @error('password')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label small text-muted" for="remember">
                                Ingat sesi login saya
                            </label>
                        </div>

                        <button type="submit" class="btn btn-del w-100 py-2 fs-6 fw-semibold shadow-sm">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Masuk ke SIPUS-Del
                        </button>
                    </form>

                    {{-- Panduan Kredensial Uji Coba untuk Penguji / Dosen --}}
                    <div class="mt-4 pt-3 border-top">
                        <span class="text-muted small fw-semibold d-block mb-2 text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                            Akun Uji Coba Demo:
                        </span>
                        <div class="row g-2 small">
                            <div class="col-12">
                                <div class="bg-light p-2 rounded border">
                                    <span class="badge bg-warning text-dark me-1">Admin</span>
                                    <code>admin@del.ac.id</code> | Pass: <code>admin123</code>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="bg-light p-2 rounded border">
                                    <span class="badge bg-primary me-1">Mahasiswa</span>
                                    <code>lucas.pardede@del.ac.id</code> | Pass: <code>(bebas)</code>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layout>
