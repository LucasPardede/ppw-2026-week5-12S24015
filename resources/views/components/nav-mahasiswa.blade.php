<nav class="navbar navbar-expand-lg navbar-dark navbar-del mb-4 sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="{{ route('katalog.index') }}">
            <i class="bi bi-book-half text-warning fs-4"></i>
            <span>SIPUS-Del</span>
            <span class="badge bg-light text-dark fw-bold rounded-pill" style="font-size: 0.65rem;">PORTAL MAHASISWA</span>
        </a>

        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#mhsNavbar" aria-controls="mhsNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mhsNavbar">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 gap-1">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('katalog.*') ? 'active fw-bold text-white' : 'text-white-50' }}" href="{{ route('katalog.index') }}">
                        <i class="bi bi-grid me-1"></i> Katalog Koleksi
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('pinjaman.*') ? 'active fw-bold text-white' : 'text-white-50' }}" href="{{ route('pinjaman.index') }}">
                        <i class="bi bi-journal-check me-1"></i> Pinjaman Saya
                    </a>
                </li>
            </ul>

            <div class="d-flex align-items-center gap-3">
                @php
                    $dendaBelumLunas = Auth::user()->totalDendaBelumLunas();
                @endphp

                @if($dendaBelumLunas > 0)
                    <span class="badge bg-danger rounded-pill px-3 py-2 text-white" title="Ada tagihan denda keterlambatan">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> Denda: Rp{{ number_format($dendaBelumLunas, 0, ',', '.') }}
                    </span>
                @endif

                <div class="text-end d-none d-lg-block">
                    <div class="text-white fw-semibold small">{{ Auth::user()->name }}</div>
                    <div class="text-white-50 small" style="font-size: 0.75rem;">
                        {{ Auth::user()->nim ? 'NIM: ' . Auth::user()->nim : Auth::user()->email }}
                    </div>
                </div>

                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-outline-light btn-sm rounded-pill px-3" onclick="return confirm('Apakah Anda yakin ingin keluar?')">
                        <i class="bi bi-box-arrow-right me-1"></i> Keluar
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>
