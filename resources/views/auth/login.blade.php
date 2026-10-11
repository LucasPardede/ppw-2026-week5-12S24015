<x-layout title="Masuk Sistem — SIPUS-Del">
    <div class="row justify-content-center py-4">
        <div class="col-md-8 col-lg-5 col-xl-4">
            <div class="card border-0 overflow-hidden" style="border-radius: 20px; box-shadow: 0 20px 60px rgba(76,29,149,0.15);">

                {{-- Card Header --}}
                <div class="card-header border-0 p-5 text-center" style="background: linear-gradient(135deg, #4C1D95, #6D28D9);">
                    <div class="d-inline-flex p-3 rounded-circle mb-3" style="background: rgba(255,255,255,0.15);">
                        <i class="bi bi-book-half" style="font-size: 2.5rem; color: #F59E0B;" aria-hidden="true"></i>
                    </div>
                    <h1 class="fw-bold mb-1 fs-3" style="color: #ffffff;">SIPUS-Del</h1>
                    <p class="mb-0 small" style="color: rgba(255,255,255,0.75);">Sistem Informasi Perpustakaan<br>Institut Teknologi Del</p>
                </div>

                <div class="card-body p-4 p-md-5" style="background: #ffffff;">

                    {{-- Info Banner --}}
                    <div class="d-flex align-items-start gap-3 p-3 mb-4 rounded-3" style="background: #EDE9FE; border: 1px solid #C4B5FD;">
                        <i class="bi bi-info-circle-fill fs-5 flex-shrink-0 mt-1" style="color: #4C1D95;" aria-hidden="true"></i>
                        <div class="small">
                            <strong class="d-block mb-1" style="color: #3B0764;">Autentikasi Terintegrasi</strong>
                            <span style="color: #4C1D95;">Mahasiswa masuk menggunakan akun email Del (mode simulasi). Admin menggunakan kredensial pustakawan lokal.</span>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('login.post') }}" id="loginForm" novalidate>
                        @csrf

                        {{-- Email Field --}}
                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold text-uppercase" style="color: #334155; font-size: 0.75rem; letter-spacing: 0.5px;">
                                Alamat Email
                            </label>
                            <div class="input-group">
                                <span class="input-group-text" style="background: #F8FAFC; border-color: #CBD5E1;">
                                    <i class="bi bi-envelope" style="color: #64748B;" aria-hidden="true"></i>
                                </span>
                                <input type="email"
                                       class="form-control border-start-0 ps-1 @error('email') is-invalid @enderror"
                                       id="email"
                                       name="email"
                                       value="{{ old('email') }}"
                                       placeholder="nim@students.del.ac.id atau admin@del.ac.id"
                                       required
                                       autocomplete="email"
                                       autofocus
                                       style="border-color: #CBD5E1;"
                                       aria-describedby="emailHelp">
                            </div>
                            <div id="emailHelp" class="mt-1 small" style="color: #475569;">
                                <i class="bi bi-info-circle me-1" style="color: #6D28D9;"></i>
                                Mahasiswa: <code style="color: #4C1D95; background: #F5F3FF; padding: 1px 4px; border-radius: 4px;">NIM@students.del.ac.id</code>
                            </div>
                            @error('email')
                                <div class="invalid-feedback d-block mt-1 small">
                                    <i class="bi bi-exclamation-circle me-1" aria-hidden="true"></i>{{ $message }}
                                </div>
                            @enderror
                        </div>

                        {{-- Password Field --}}
                        <div class="mb-4">
                            <label for="password" class="form-label fw-semibold text-uppercase mb-1" style="color: #334155; font-size: 0.75rem; letter-spacing: 0.5px;">
                                Kata Sandi
                            </label>
                            <div class="input-group">
                                <span class="input-group-text" style="background: #F8FAFC; border-color: #CBD5E1;">
                                    <i class="bi bi-key" style="color: #64748B;" aria-hidden="true"></i>
                                </span>
                                <input type="password"
                                       class="form-control border-start-0 border-end-0 ps-1 @error('password') is-invalid @enderror"
                                       id="password"
                                       name="password"
                                       placeholder="Kata sandi Anda"
                                       required
                                       autocomplete="current-password"
                                       style="border-color: #CBD5E1;"
                                       aria-describedby="passwordHelp">
                                <button class="input-group-text bg-white"
                                        type="button"
                                        data-password-toggle="password"
                                        aria-label="Tampilkan kata sandi"
                                        style="border-color: #CBD5E1; cursor: pointer;">
                                    <i class="bi bi-eye" style="color: #64748B;" aria-hidden="true"></i>
                                </button>
                            </div>
                            <div id="passwordHelp" class="mt-1 small" style="color: #475569;">
                                <i class="bi bi-shield-check me-1" style="color: #6D28D9;"></i>
                                Mahasiswa: password bebas <span style="color: #64748B;">(mode simulasi CIS aktif)</span>
                            </div>
                            @error('password')
                                <div class="invalid-feedback d-block mt-1 small">
                                    <i class="bi bi-exclamation-circle me-1" aria-hidden="true"></i>{{ $message }}
                                </div>
                            @enderror
                        </div>

                        {{-- Remember Me --}}
                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label small" for="remember" style="color: #475569;">
                                Ingat sesi login saya di perangkat ini
                            </label>
                        </div>

                        {{-- Submit Button --}}
                        <button type="submit"
                                id="loginSubmitBtn"
                                class="btn btn-del w-100 py-2 fs-6 fw-semibold rounded-pill shadow-sm">
                            <i class="bi bi-box-arrow-in-right me-1" aria-hidden="true"></i> Masuk ke SIPUS-Del
                        </button>
                    </form>

                    {{-- Demo Credentials (hanya jika APP_ENV=local) --}}
                    @if(app()->environment('local'))
                        <div class="mt-4 pt-3 border-top">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="badge bg-danger rounded-pill" style="font-size: 0.65rem;">DEV ONLY</span>
                                <span class="fw-semibold text-uppercase" style="font-size: 0.7rem; color: #64748B; letter-spacing: 0.5px;">
                                    <i class="bi bi-tools me-1"></i>Akun Demo
                                </span>
                            </div>
                            <div class="d-flex flex-column gap-2 small">
                                <button type="button"
                                        class="btn btn-outline-secondary text-start border rounded-2 p-2"
                                        onclick="document.getElementById('email').value='admin@del.ac.id'; document.getElementById('password').value='admin123';"
                                        title="Isi otomatis kredensial admin">
                                    <span class="badge bg-warning text-dark me-1">Admin</span>
                                    <code style="color: #1e1b4b;">admin@del.ac.id</code>
                                    <span style="color: #64748B;" class="ms-1">/ admin123</span>
                                </button>
                                <button type="button"
                                        class="btn btn-outline-secondary text-start border rounded-2 p-2"
                                        onclick="document.getElementById('email').value='12S24015@students.del.ac.id'; document.getElementById('password').value='password123';"
                                        title="Isi otomatis kredensial mahasiswa">
                                    <span class="badge bg-primary me-1">Mahasiswa</span>
                                    <code style="color: #1e1b4b;">12S24015@students.del.ac.id</code>
                                    <span style="color: #64748B;" class="ms-1">/ (bebas)</span>
                                </button>
                            </div>
                        </div>
                    @endif

                </div>
            </div>

            <div class="text-center mt-3">
                <small style="color: #64748B;">Institut Teknologi Del &bull; Laguboti, Toba &bull; &copy; {{ date('Y') }}</small>
            </div>
        </div>
    </div>
</x-layout>
