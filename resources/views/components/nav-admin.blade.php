<nav class="navbar navbar-expand-lg navbar-dark navbar-del mb-4 sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="{{ route('admin.dashboard') }}">
            <i class="bi bi-shield-lock-fill text-warning fs-4"></i>
            <span>SIPUS-Del</span>
            <span class="badge bg-warning text-dark fw-bold rounded-pill" style="font-size: 0.65rem;">ADMIN PANEL</span>
        </a>

        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar" aria-controls="adminNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="adminNavbar">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 gap-1">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active fw-bold text-white' : 'text-white-50' }}" href="{{ route('admin.dashboard') }}">
                        <i class="bi bi-speedometer2 me-1"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('buku.*') ? 'active fw-bold text-white' : 'text-white-50' }}" href="{{ route('buku.index') }}">
                        <i class="bi bi-book me-1"></i> Kelola Buku
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('kategori.*') ? 'active fw-bold text-white' : 'text-white-50' }}" href="{{ route('kategori.index') }}">
                        <i class="bi bi-tags me-1"></i> Kelola Kategori
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.peminjaman.*') ? 'active fw-bold text-white' : 'text-white-50' }}" href="{{ route('admin.peminjaman.index') }}">
                        <i class="bi bi-arrow-left-right me-1"></i> Peminjaman
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.mahasiswa.*') ? 'active fw-bold text-white' : 'text-white-50' }}" href="{{ route('admin.mahasiswa.index') }}">
                        <i class="bi bi-people me-1"></i> Mahasiswa
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.laporan.*') ? 'active fw-bold text-white' : 'text-white-50' }}" href="{{ route('admin.laporan.index') }}">
                        <i class="bi bi-file-earmark-bar-graph me-1"></i> Laporan
                    </a>
                </li>
            </ul>

            <div class="d-flex align-items-center gap-3">
                <div class="text-end d-none d-lg-block">
                    <div class="text-white fw-semibold small">{{ Auth::user()->name }}</div>
                    <div class="text-warning small" style="font-size: 0.75rem;">Pustakawan Admin</div>
                </div>

                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-outline-light btn-sm rounded-pill px-3" onclick="return confirm('Apakah Anda yakin ingin keluar dari sistem?')">
                        <i class="bi bi-box-arrow-right me-1"></i> Keluar
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>
