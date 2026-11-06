<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="SIPUS-Del — Sistem Informasi Perpustakaan Institut Teknologi Del. Kelola peminjaman buku, katalog koleksi, dan data mahasiswa secara digital.">
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
        <nav class="navbar navbar-expand-lg navbar-dark bg-del-gradient mb-4 sticky-top shadow-sm">
            <div class="container-fluid px-3 px-md-4 px-xl-5">
                <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="{{ route('login') }}">
                    <div class="brand-badge">
                        <i class="bi bi-book-half" aria-hidden="true"></i>
                    </div>
                    <span class="text-white fw-bold">SIPUS<span class="text-warning">-Del</span></span>
                    <span class="badge bg-warning text-dark fw-semibold rounded-pill ms-1" style="font-size: 0.62rem;">Institut Teknologi Del</span>
                </a>
                <div class="ms-auto">
                    <a href="{{ route('login') }}" class="btn btn-outline-light btn-sm px-3 rounded-pill">
                        <i class="bi bi-box-arrow-in-right me-1" aria-hidden="true"></i> Masuk Akun
                    </a>
                </div>
            </div>
        </nav>
    @endauth

    {{-- Konten Utama --}}
    <main class="container-fluid px-3 px-md-4 px-xl-5 pb-5" id="main-content">

        {{-- ===== TOAST NOTIFICATIONS ===== --}}
        <div class="toast-container-del" id="toastContainer" aria-live="polite" aria-atomic="true">
            @if(session('success'))
                <div class="toast toast-del show align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="true" data-bs-delay="5000">
                    <div class="d-flex align-items-center p-3">
                        <i class="bi bi-check-circle-fill fs-5 me-3 flex-shrink-0" aria-hidden="true"></i>
                        <div class="flex-grow-1 fw-medium">{{ session('success') }}</div>
                        <button type="button" class="btn-close btn-close-white ms-3 flex-shrink-0" data-bs-dismiss="toast" aria-label="Tutup notifikasi"></button>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="toast toast-del show align-items-center text-bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="true" data-bs-delay="6000">
                    <div class="d-flex align-items-center p-3">
                        <i class="bi bi-exclamation-triangle-fill fs-5 me-3 flex-shrink-0" aria-hidden="true"></i>
                        <div class="flex-grow-1 fw-medium">{{ session('error') }}</div>
                        <button type="button" class="btn-close btn-close-white ms-3 flex-shrink-0" data-bs-dismiss="toast" aria-label="Tutup notifikasi"></button>
                    </div>
                </div>
            @endif

            @if(session('warning'))
                <div class="toast toast-del show align-items-center text-bg-warning border-0" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="true" data-bs-delay="5500">
                    <div class="d-flex align-items-center p-3">
                        <i class="bi bi-exclamation-circle-fill fs-5 me-3 flex-shrink-0" aria-hidden="true"></i>
                        <div class="flex-grow-1 fw-medium text-dark">{{ session('warning') }}</div>
                        <button type="button" class="btn-close ms-3 flex-shrink-0" data-bs-dismiss="toast" aria-label="Tutup notifikasi"></button>
                    </div>
                </div>
            @endif
        </div>

        {{-- Validasi Error Banner --}}
        @if($errors->any())
            <div class="alert alert-warning alert-dismissible fade show shadow-sm rounded-3 mb-4 border-start border-warning border-4 border-top-0 border-end-0 border-bottom-0" role="alert">
                <div class="d-flex align-items-start gap-2">
                    <i class="bi bi-exclamation-circle-fill fs-5 text-warning flex-shrink-0 mt-1" aria-hidden="true"></i>
                    <div class="flex-grow-1">
                        <strong class="d-block mb-1">Mohon periksa kembali formulir berikut:</strong>
                        <ul class="mb-0 ps-3 small">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <button type="button" class="btn-close flex-shrink-0" data-bs-dismiss="alert" aria-label="Tutup pesan error"></button>
                </div>
            </div>
        @endif

        {{-- Slot Konten Halaman --}}
        {{ $slot }}
    </main>

    {{-- Footer Resmi --}}
    <footer class="footer-del">
        <div class="container-fluid px-3 px-md-4 px-xl-5 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-book text-del" aria-hidden="true"></i>
                <span class="fw-semibold text-dark">SIPUS-Del</span>
                <span>&copy; {{ date('Y') }} Institut Teknologi Del &bull; Laguboti, Toba</span>
            </div>
            <div class="text-muted small">
                Praktikum Pemrograman &amp; Pengujian Web (12S3101) &bull; Laravel {{ app()->version() }}
            </div>
        </div>
    </footer>

    <!-- ===== MODAL KONFIRMASI GLOBAL (Bootstrap, menggantikan window.confirm) ===== -->
    <div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-3 border-0 shadow-lg">
                <div class="modal-header modal-del-confirm border-0 rounded-top-3">
                    <h5 class="modal-title fw-bold" id="confirmModalLabel">
                        <i class="bi bi-question-circle-fill me-2" aria-hidden="true"></i>
                        <span id="confirmModalTitle">Konfirmasi Aksi</span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Batal"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    <p class="mb-0 fs-6" id="confirmModalMessage">Apakah Anda yakin?</p>
                    <p class="text-muted small mt-2 mb-0" id="confirmModalSubtext"></p>
                </div>
                <div class="modal-footer border-0 justify-content-center gap-2 pb-4">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal" id="confirmCancelBtn">
                        <i class="bi bi-x-circle me-1" aria-hidden="true"></i> Batal
                    </button>
                    <button type="button" class="btn btn-del rounded-pill px-4" id="confirmOkBtn">
                        <i class="bi bi-check-circle me-1" aria-hidden="true"></i> <span id="confirmOkText">Ya, Lanjutkan</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== MODAL KONFIRMASI HAPUS (Danger) ===== -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-3 border-0 shadow-lg">
                <div class="modal-header modal-del-danger border-0 rounded-top-3">
                    <h5 class="modal-title fw-bold" id="deleteModalLabel">
                        <i class="bi bi-trash3-fill me-2" aria-hidden="true"></i> Konfirmasi Hapus Data
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Batal hapus"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    <div class="mb-3">
                        <i class="bi bi-exclamation-triangle-fill text-danger" style="font-size: 3rem;" aria-hidden="true"></i>
                    </div>
                    <p class="fw-semibold mb-1 fs-6" id="deleteModalMessage">Hapus data ini?</p>
                    <p class="text-muted small" id="deleteModalSubtext">Tindakan ini <strong>tidak dapat dibatalkan</strong>.</p>
                </div>
                <div class="modal-footer border-0 justify-content-center gap-2 pb-4">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">
                        <i class="bi bi-arrow-left me-1" aria-hidden="true"></i> Batalkan
                    </button>
                    <button type="button" class="btn btn-danger rounded-pill px-4 fw-semibold" id="deleteConfirmBtn">
                        <i class="bi bi-trash3 me-1" aria-hidden="true"></i> Ya, Hapus Sekarang
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    // ──────────────────────────────────────────────
    // SIPUS-Del Global JS Enhancements
    // ──────────────────────────────────────────────

    document.addEventListener('DOMContentLoaded', function () {

        // 1. Auto-init all Bootstrap Toasts
        document.querySelectorAll('.toast').forEach(function (el) {
            var toast = bootstrap.Toast.getOrCreateInstance(el);
            toast.show();
        });

        // 2. Delete Modal Handler (menggantikan window.confirm pada form DELETE)
        //    Usage: tambahkan data-delete-form="formId" data-delete-title="Nama Item" ke trigger button
        var deleteModal = document.getElementById('deleteModal');
        if (deleteModal) {
            var deleteModalBS = new bootstrap.Modal(deleteModal);
            var deleteConfirmBtn = document.getElementById('deleteConfirmBtn');
            var pendingDeleteForm = null;

            document.querySelectorAll('[data-delete-trigger]').forEach(function (btn) {
                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    var formId   = btn.getAttribute('data-delete-form');
                    var title    = btn.getAttribute('data-delete-title') || 'data ini';
                    var subtext  = btn.getAttribute('data-delete-subtext') || 'Tindakan ini tidak dapat dibatalkan.';

                    document.getElementById('deleteModalMessage').textContent = 'Hapus "' + title + '"?';
                    document.getElementById('deleteModalSubtext').innerHTML   = subtext;
                    pendingDeleteForm = document.getElementById(formId);
                    deleteModalBS.show();
                });
            });

            if (deleteConfirmBtn) {
                deleteConfirmBtn.addEventListener('click', function () {
                    if (pendingDeleteForm) {
                        deleteModalBS.hide();
                        pendingDeleteForm.submit();
                    }
                });
            }
        }

        // 3. Confirm Modal Handler (menggantikan window.confirm pada form non-DELETE)
        //    Usage: tambahkan data-confirm-trigger data-confirm-form="formId"
        //           data-confirm-message="..." data-confirm-subtext="..."
        var confirmModal = document.getElementById('confirmModal');
        if (confirmModal) {
            var confirmModalBS = new bootstrap.Modal(confirmModal);
            var confirmOkBtn   = document.getElementById('confirmOkBtn');
            var pendingConfirmForm = null;

            document.querySelectorAll('[data-confirm-trigger]').forEach(function (btn) {
                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    var formId   = btn.getAttribute('data-confirm-form');
                    var title    = btn.getAttribute('data-confirm-title')   || 'Konfirmasi Aksi';
                    var message  = btn.getAttribute('data-confirm-message') || 'Apakah Anda yakin melanjutkan?';
                    var subtext  = btn.getAttribute('data-confirm-subtext') || '';
                    var okText   = btn.getAttribute('data-confirm-ok')      || 'Ya, Lanjutkan';

                    document.getElementById('confirmModalTitle').textContent   = title;
                    document.getElementById('confirmModalMessage').textContent = message;
                    document.getElementById('confirmModalSubtext').innerHTML   = subtext;
                    document.getElementById('confirmOkText').textContent       = okText;
                    pendingConfirmForm = document.getElementById(formId);
                    confirmModalBS.show();
                });
            });

            if (confirmOkBtn) {
                confirmOkBtn.addEventListener('click', function () {
                    if (pendingConfirmForm) {
                        confirmModalBS.hide();
                        pendingConfirmForm.submit();
                    }
                });
            }
        }

        // 4. Show/Hide Password Toggle
        document.querySelectorAll('[data-password-toggle]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var targetId = btn.getAttribute('data-password-toggle');
                var input    = document.getElementById(targetId);
                var icon     = btn.querySelector('i');
                if (!input) return;

                if (input.type === 'password') {
                    input.type = 'text';
                    if (icon) { icon.classList.remove('bi-eye'); icon.classList.add('bi-eye-slash'); }
                    btn.setAttribute('aria-label', 'Sembunyikan kata sandi');
                } else {
                    input.type = 'password';
                    if (icon) { icon.classList.remove('bi-eye-slash'); icon.classList.add('bi-eye'); }
                    btn.setAttribute('aria-label', 'Tampilkan kata sandi');
                }
            });
        });

        // 5. Loading state for submit buttons
        document.querySelectorAll('form').forEach(function (form) {
            form.addEventListener('submit', function () {
                var btn = form.querySelector('[type="submit"]');
                if (btn && !btn.hasAttribute('data-no-loading')) {
                    var originalHtml = btn.innerHTML;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Memproses...';
                    btn.classList.add('btn-loading');
                    btn.disabled = true;
                    // Re-enable after 8 seconds as safety fallback
                    setTimeout(function () {
                        btn.innerHTML = originalHtml;
                        btn.classList.remove('btn-loading');
                        btn.disabled = false;
                    }, 8000);
                }
            });
        });

    });
    </script>
</body>
</html>
