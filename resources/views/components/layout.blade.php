<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'SIPUS-Del' }} — Institut Teknologi Del</title>

    <!-- Bootstrap 5.3 & Icons CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Vite Assets (App CSS & JS) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #F8FAFC;
            color: #1E293B;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .navbar-del {
            background-color: #4C1D95; /* Del Royal Purple */
            box-shadow: 0 4px 12px rgba(76, 29, 149, 0.15);
        }
        .btn-del {
            background-color: #4C1D95;
            color: #FFFFFF;
            border: 1px solid #4C1D95;
            font-weight: 500;
            transition: all 0.2s ease-in-out;
        }
        .btn-del:hover {
            background-color: #3B0764; /* Del Royal Purple Hover */
            border-color: #3B0764;
            color: #FFFFFF;
            transform: translateY(-1px);
        }
        .btn-outline-del {
            background-color: transparent;
            color: #4C1D95;
            border: 1px solid #4C1D95;
            font-weight: 500;
            transition: all 0.2s ease-in-out;
        }
        .btn-outline-del:hover {
            background-color: #4C1D95;
            color: #FFFFFF;
        }
        .badge-del {
            background-color: #EDE9FE;
            color: #5B21B6;
            font-weight: 600;
        }
        .card-del {
            border: 1px solid #E2E8F0;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .card-del:hover {
            box-shadow: 0 10px 25px -5px rgba(76, 29, 149, 0.1), 0 8px 10px -6px rgba(76, 29, 149, 0.1);
        }
        .page-header-del {
            background: linear-gradient(135deg, #4C1D95 0%, #6D28D9 100%);
            color: white;
            border-radius: 16px;
            padding: 2rem 2.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 20px rgba(76, 29, 149, 0.15);
        }
        .pagination .page-item.active .page-link {
            background-color: #4C1D95;
            border-color: #4C1D95;
        }
        .pagination .page-link {
            color: #4C1D95;
        }
        .footer-del {
            margin-top: auto;
            background-color: #FFFFFF;
            border-top: 1px solid #E2E8F0;
            padding: 1.5rem 0;
            color: #64748B;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>

    {{-- Navigasi Sesuai Peran Pengguna --}}
    @auth
        @if(Auth::user()->role === 'admin')
            <x-nav-admin />
        @else
            <x-nav-mahasiswa />
        @endif
    @else
        <nav class="navbar navbar-expand-lg navbar-dark navbar-del mb-4">
            <div class="container">
                <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="{{ route('login') }}">
                    <i class="bi bi-book-half fs-4 text-warning"></i>
                    <span>SIPUS-Del</span>
                    <span class="badge bg-white text-dark rounded-pill fs-7 ms-2 py-1 px-2" style="font-size: 0.7rem;">Institut Teknologi Del</span>
                </a>
                <div class="ms-auto">
                    <a href="{{ route('login') }}" class="btn btn-outline-light btn-sm px-3 rounded-pill">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Masuk Akun
                    </a>
                </div>
            </div>
        </nav>
    @endauth

    {{-- Konten Utama --}}
    <main class="container pb-5">
        {{-- Flash Messages Interaktif --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center shadow-sm rounded-3 mb-4" role="alert">
                <i class="bi bi-check-circle-fill fs-5 me-3 text-success"></i>
                <div class="flex-grow-1">
                    {{ session('success') }}
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center shadow-sm rounded-3 mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill fs-5 me-3 text-danger"></i>
                <div class="flex-grow-1">
                    {{ session('error') }}
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-warning alert-dismissible fade show shadow-sm rounded-3 mb-4" role="alert">
                <div class="d-flex align-items-center mb-1">
                    <i class="bi bi-exclamation-circle-fill fs-5 me-2 text-warning"></i>
                    <strong>Mohon periksa kembali formulir yang Anda kirim:</strong>
                </div>
                <ul class="mb-0 ps-4 small">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- Slot Konten Halaman --}}
        {{ $slot }}
    </main>

    {{-- Footer Resmi --}}
    <footer class="footer-del">
        <div class="container d-flex flex-wrap justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-book text-del"></i>
                <span class="fw-semibold text-dark">SIPUS-Del</span>
                <span>&copy; {{ date('Y') }} Institut Teknologi Del • Laguboti, Toba</span>
            </div>
            <div class="text-muted small">
                Praktikum Pemrograman & Pengujian Web (12S3101) • Laravel {{ app()->version() }} (PHP {{ PHP_VERSION }})
            </div>
        </div>
    </footer>

    <!-- Bootstrap Bundle with Popper JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
