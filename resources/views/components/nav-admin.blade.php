<nav class="navbar navbar-expand-lg navbar-dark bg-del-gradient mb-4 sticky-top shadow-sm">
    <div class="container-fluid px-3 px-md-4 px-xl-5">
        <a class="navbar-brand fw-bold d-flex align-items-center gap-2 py-1" href="{{ route('admin.dashboard') }}">
            <div class="brand-badge shadow-sm">
                <i class="bi bi-shield-lock-fill" aria-hidden="true"></i>
            </div>
            <div>
                <span class="fs-5 tracking-tight text-white">SIPUS<span class="text-warning">-Del</span></span>
                <span class="badge bg-warning text-dark fw-bold rounded-pill ms-1" style="font-size: 0.62rem; letter-spacing: 0.5px;">ADMIN PANEL</span>
            </div>
        </a>

        <button class="navbar-toggler border-0 p-1" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar" aria-controls="adminNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="adminNavbar">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 gap-1 ps-lg-2">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                        <i class="bi bi-speedometer2 me-1" aria-hidden="true"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('buku.*') ? 'active' : '' }}" href="{{ route('buku.index') }}">
                        <i class="bi bi-book-fill me-1" aria-hidden="true"></i> Buku
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('kategori.*') ? 'active' : '' }}" href="{{ route('kategori.index') }}">
                        <i class="bi bi-tags-fill me-1" aria-hidden="true"></i> Kategori
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.peminjaman.*') ? 'active' : '' }}" href="{{ route('admin.peminjaman.index') }}">
                        <i class="bi bi-arrow-left-right me-1" aria-hidden="true"></i> Sirkulasi
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.mahasiswa.*') ? 'active' : '' }}" href="{{ route('admin.mahasiswa.index') }}">
                        <i class="bi bi-people-fill me-1" aria-hidden="true"></i> Mahasiswa
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.pengumuman.*') ? 'active' : '' }}" href="{{ route('admin.pengumuman.index') }}">
                        <i class="bi bi-megaphone-fill me-1" aria-hidden="true"></i> Pengumuman
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.laporan.*') ? 'active' : '' }}" href="{{ route('admin.laporan.index') }}">
                        <i class="bi bi-file-earmark-bar-graph-fill me-1" aria-hidden="true"></i> Laporan
                    </a>
                </li>
            </ul>

            <div class="d-flex align-items-center gap-3">
                <div class="d-none d-lg-flex align-items-center gap-2 px-3 py-1 rounded-pill" style="background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.25);">
                    <div class="rounded-circle bg-warning text-dark d-flex align-items-center justify-content-center fw-bold" style="width: 28px; height: 28px; font-size: 0.75rem;">
                        A
                    </div>
                    <div class="text-start">
                        <div class="text-white fw-semibold lh-1" style="font-size: 0.8rem;">{{ Str::limit(Auth::user()->name, 16) }}</div>
                        <div class="text-warning" style="font-size: 0.68rem;">
                            Pustakawan @if(session('login_timestamp')) &bull; <span class="text-white-50">Masuk: {{ session('login_timestamp') }}</span> @endif
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('logout') }}" class="d-inline" id="adminLogoutForm">
                    @csrf
                    <button type="button"
                            class="btn btn-outline-light btn-sm rounded-pill px-3 shadow-sm"
                            data-confirm-trigger
                            data-confirm-form="adminLogoutForm"
                            data-confirm-title="Konfirmasi Keluar"
                            data-confirm-message="Anda akan keluar dari Panel Administrator SIPUS-Del."
                            data-confirm-subtext="Pastikan seluruh pekerjaan telah disimpan."
                            data-confirm-ok="Ya, Keluar"
                            aria-label="Keluar dari sistem">
                        <i class="bi bi-box-arrow-right me-1" aria-hidden="true"></i> Keluar
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>
