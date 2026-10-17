<nav class="navbar navbar-expand-lg navbar-dark bg-del-gradient mb-4 sticky-top shadow-sm">
    <div class="container-fluid px-3 px-md-4 px-xl-5">
        <a class="navbar-brand fw-bold d-flex align-items-center gap-2 py-1" href="{{ route('katalog.index') }}">
            <div class="brand-badge shadow-sm">
                <i class="bi bi-book-half" aria-hidden="true"></i>
            </div>
            <div>
                <span class="fs-5 tracking-tight text-white">SIPUS<span class="text-warning">-Del</span></span>
                <span class="badge bg-warning text-dark fw-bold rounded-pill ms-1" style="font-size: 0.62rem; letter-spacing: 0.5px;">PORTAL MAHASISWA</span>
            </div>
        </a>

        <button class="navbar-toggler border-0 p-1" type="button" data-bs-toggle="collapse" data-bs-target="#mhsNavbar" aria-controls="mhsNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mhsNavbar">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 gap-1 ps-lg-3">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('katalog.*') ? 'active' : '' }}" href="{{ route('katalog.index') }}">
                        <i class="bi bi-grid-fill me-1" aria-hidden="true"></i> Katalog Koleksi
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('pinjaman.*') ? 'active' : '' }}" href="{{ route('pinjaman.index') }}">
                        <i class="bi bi-journal-bookmark-fill me-1" aria-hidden="true"></i> Pinjaman Saya
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('pengumuman.*') ? 'active' : '' }}" href="{{ route('pengumuman.index') }}">
                        <i class="bi bi-megaphone-fill me-1" aria-hidden="true"></i> Pengumuman
                        @php
                            $activeCount = \App\Models\Pengumuman::where('is_aktif', true)->count();
                        @endphp
                        @if($activeCount > 0)
                            <span class="badge rounded-pill ms-1 {{ request()->routeIs('pengumuman.*') ? 'bg-del text-white' : 'bg-warning text-dark' }}" style="font-size: 0.65rem;">{{ $activeCount }}</span>
                        @endif
                    </a>
                </li>
            </ul>

            <div class="d-flex align-items-center gap-3">
                @php
                    $dendaBelumLunas = Auth::user()->totalDendaBelumLunas();
                @endphp

                @if($dendaBelumLunas > 0)
                    <span class="badge bg-danger rounded-pill px-3 py-2 text-white shadow-sm" title="Ada tagihan denda keterlambatan">
                        <i class="bi bi-exclamation-triangle-fill me-1" aria-hidden="true"></i> Denda: Rp{{ number_format($dendaBelumLunas, 0, ',', '.') }}
                    </span>
                @endif

                <div class="d-none d-lg-flex align-items-center gap-2 px-3 py-1 rounded-pill" style="background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.25);">
                    <div class="rounded-circle bg-warning text-dark d-flex align-items-center justify-content-center fw-bold" style="width: 28px; height: 28px; font-size: 0.75rem;">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                    <div class="text-start">
                        <div class="text-white fw-semibold lh-1" style="font-size: 0.8rem;">{{ Str::limit(Auth::user()->name, 15) }}</div>
                        <div class="text-white-50" style="font-size: 0.68rem;">
                            {{ Auth::user()->nim ?? 'Mahasiswa' }}
                            @if(session('login_timestamp')) &bull; Masuk: {{ session('login_timestamp') }} @endif
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('logout') }}" class="d-inline" id="mhsLogoutForm">
                    @csrf
                    <button type="button"
                            class="btn btn-outline-light btn-sm rounded-pill px-3 shadow-sm"
                            data-confirm-trigger
                            data-confirm-form="mhsLogoutForm"
                            data-confirm-title="Konfirmasi Keluar"
                            data-confirm-message="Anda akan keluar dari Portal Mahasiswa SIPUS-Del."
                            data-confirm-subtext="Sesi Anda akan diakhiri dengan aman."
                            data-confirm-ok="Ya, Keluar"
                            aria-label="Keluar dari sistem">
                        <i class="bi bi-box-arrow-right me-1" aria-hidden="true"></i> Keluar
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>
