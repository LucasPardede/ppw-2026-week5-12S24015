<x-layout title="Pinjaman Saya — Portal Mahasiswa SIPUS-Del">
    {{-- Breadcrumb --}}
    <nav aria-label="Breadcrumb" class="mb-3">
        <ol class="breadcrumb breadcrumb-del mb-0" style="font-size: 0.82rem;">
            <li class="breadcrumb-item"><a href="{{ route('katalog.index') }}"><i class="bi bi-grid-fill me-1"></i>Katalog Koleksi</a></li>
            <li class="breadcrumb-item active" aria-current="page">Pinjaman Saya</li>
        </ol>
    </nav>

    <div class="page-header-del d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h1 class="fw-bold mb-2 fs-2">
                <i class="bi bi-journal-check text-warning me-2" aria-hidden="true"></i>Pinjaman Buku Saya
            </h1>
            <p class="mb-0 text-white-50">
                Pantau status peminjaman aktif, batas waktu pengembalian, perpanjangan, dan riwayat peminjaman buku.
            </p>
        </div>
        <div>
            <a href="{{ route('katalog.index') }}" class="btn btn-warning fw-semibold rounded-pill px-4 shadow-sm">
                <i class="bi bi-plus-lg me-1" aria-hidden="true"></i> Pinjam Buku Lain
            </a>
        </div>
    </div>

    {{-- FITUR 5: Peringatan Buku Jatuh Tempo Besok --}}
    @if(isset($jatuhTempoBesok) && $jatuhTempoBesok->count() > 0)
        <div class="alert alert-warning border-warning d-flex align-items-center gap-3 rounded-4 p-3 mb-4 shadow-sm" role="alert">
            <i class="bi bi-bell-fill fs-3 text-warning-emphasis flex-shrink-0"></i>
            <div>
                <strong class="d-block text-warning-emphasis fs-6">Peringatan: Masa Peminjaman Berakhir Besok!</strong>
                <span class="small text-dark">
                    Anda memiliki <strong>{{ $jatuhTempoBesok->count() }} buku</strong> dengan batas waktu pengembalian besok ({{ \Carbon\Carbon::parse($jatuhTempoBesok->first()->tanggal_jatuh_tempo)->format('d M Y') }}):
                    @foreach($jatuhTempoBesok as $jtb)
                        <span class="badge bg-warning-subtle text-dark border border-warning">"{{ $jtb->buku->judul }}"</span>{{ !$loop->last ? ' ' : '' }}
                    @endforeach.
                    Harap segera lakukan perpanjangan waktu atau kembalikan buku ke loket perpustakaan untuk menghindari denda keterlambatan Rp1.000/hari.
                </span>
            </div>
        </div>
    @endif

    {{-- Bagian 1: Peminjaman Aktif --}}
    <div class="card card-del border-0 shadow-sm mb-5 overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h2 class="h6 card-title mb-0 fw-semibold text-secondary">
                <i class="bi bi-clock-history me-2 text-del" aria-hidden="true"></i>Peminjaman Aktif ({{ $pinjamanAktif->count() }} / {{ config('perpustakaan.maksimal_pinjam_buku', 3) }} Buku)
            </h2>
            <span class="badge badge-del px-3 py-2 rounded-pill" style="font-size: 0.72rem;">
                <i class="bi bi-shield-check me-1"></i>Batas Kuota: Maks 3 Buku
            </span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-secondary text-uppercase small" style="font-size: 0.8rem;">
                    <tr>
                        <th class="ps-4">No</th>
                        <th>Judul Buku</th>
                        <th>Kategori</th>
                        <th>Tanggal Pinjam</th>
                        <th>Jatuh Tempo</th>
                        <th>Status Waktu</th>
                        <th>Denda Berjalan</th>
                        <th class="text-end pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pinjamanAktif as $index => $pinjam)
                        <tr>
                            <td class="ps-4 text-muted fw-semibold">{{ $index + 1 }}</td>
                            <td>
                                <a href="{{ route('katalog.show', $pinjam->buku) }}" class="fw-bold text-dark text-decoration-none hover-del">
                                    {{ $pinjam->buku->judul }}
                                </a>
                                <div class="text-muted small">
                                    <code>{{ $pinjam->buku->isbn }}</code> • {{ $pinjam->buku->penulis }}
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-del px-2 py-1 small">
                                    {{ $pinjam->buku->kategori->nama_kategori ?? '-' }}
                                </span>
                            </td>
                            <td>{{ $pinjam->tanggal_pinjam->format('d M Y') }}</td>
                            <td class="fw-semibold {{ $pinjam->is_terlambat ? 'text-danger' : 'text-dark' }}">
                                {{ $pinjam->tanggal_jatuh_tempo->format('d M Y') }}
                            </td>
                            <td>
                                @if($pinjam->is_terlambat)
                                    <span class="badge bg-danger px-3 py-2 rounded-pill">
                                        <i class="bi bi-exclamation-triangle-fill me-1" aria-hidden="true"></i> Terlambat {{ $pinjam->hari_terlambat }} Hari
                                    </span>
                                @else
                                    @php
                                        $sisaHari = now()->startOfDay()->diffInDays($pinjam->tanggal_jatuh_tempo->startOfDay(), false);
                                    @endphp
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill">
                                        <i class="bi bi-check-circle me-1" aria-hidden="true"></i> Sisa {{ (int) $sisaHari }} Hari Lagi
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($pinjam->hitungDendaBerjalan() > 0)
                                    <span class="text-danger fw-bold font-monospace">
                                        Rp{{ number_format($pinjam->hitungDendaBerjalan(), 0, ',', '.') }}
                                    </span>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                @if($pinjam->perpanjangan === 0 && !$pinjam->is_terlambat)
                                    <form action="{{ route('pinjaman.perpanjang', $pinjam) }}" method="POST" class="d-inline" id="perpanjangForm{{ $pinjam->id }}">
                                        @csrf
                                        @method('PATCH')
                                    </form>
                                    <button type="button" 
                                            class="btn btn-outline-del btn-sm rounded-pill px-3" 
                                            title="Perpanjang 7 hari"
                                            aria-label="Perpanjang peminjaman {{ $pinjam->buku->judul }}"
                                            data-confirm-trigger
                                            data-confirm-form="perpanjangForm{{ $pinjam->id }}"
                                            data-confirm-title="Perpanjangan Peminjaman"
                                            data-confirm-message="Perpanjang masa peminjaman buku '{{ $pinjam->buku->judul }}' selama 7 hari kalender?"
                                            data-confirm-subtext="Perpanjangan hanya dapat dilakukan 1 kali per peminjaman."
                                            data-confirm-ok="Ya, Perpanjang">
                                        <i class="bi bi-arrow-repeat me-1" aria-hidden="true"></i> Perpanjang
                                    </button>
                                @elseif($pinjam->perpanjangan >= 1)
                                    <span class="badge bg-light text-muted border rounded-pill px-2 py-1 small">
                                        Sudah Diperpanjang 1x
                                    </span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger rounded-pill px-2 py-1 small">
                                        Segera Kembalikan
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-journal-x fs-1 d-block mb-2 text-secondary"></i>
                                    <p class="mb-2 fw-semibold">Anda sedang tidak memiliki peminjaman buku aktif.</p>
                                    <a href="{{ route('katalog.index') }}" class="btn btn-del btn-sm rounded-pill px-4">
                                        Jelajahi Katalog Buku
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Bagian 2: Riwayat Peminjaman Selesai --}}
    <div class="card card-del border-0 shadow-sm overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom">
            <h5 class="card-title mb-0 fw-semibold text-secondary">
                <i class="bi bi-archive me-2 text-muted"></i>Riwayat Pengembalian Buku
            </h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light text-secondary text-uppercase" style="font-size: 0.75rem;">
                    <tr>
                        <th class="ps-4">No</th>
                        <th>Judul Buku</th>
                        <th>Tanggal Pinjam</th>
                        <th>Tanggal Kembali</th>
                        <th>Denda Keterlambatan</th>
                        <th class="text-end pe-4">Status Denda</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($riwayat as $index => $item)
                        <tr>
                            <td class="ps-4 text-muted fw-semibold">{{ $riwayat->firstItem() + $index }}</td>
                            <td>
                                <span class="fw-semibold text-dark">{{ $item->buku->judul }}</span>
                                <div class="text-muted" style="font-size: 0.75rem;">{{ $item->buku->penulis }}</div>
                            </td>
                            <td>{{ $item->tanggal_pinjam->format('d/m/Y') }}</td>
                            <td>{{ $item->tanggal_kembali ? $item->tanggal_kembali->format('d/m/Y') : '-' }}</td>
                            <td class="font-monospace">
                                @if($item->denda > 0)
                                    <span class="text-danger fw-semibold">
                                        Rp{{ number_format($item->denda, 0, ',', '.') }}
                                    </span>
                                @else
                                    <span class="text-muted">Rp0</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                @if($item->denda > 0)
                                    @if($item->denda_lunas)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">
                                            <i class="bi bi-check-all me-1"></i> Lunas
                                        </span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill">
                                            <i class="bi bi-clock me-1"></i> Belum Lunas
                                        </span>
                                    @endif
                                @else
                                    <span class="badge bg-light text-muted border rounded-pill">Bebas Denda</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                Belum ada riwayat pengembalian buku sebelumnya.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($riwayat->hasPages())
            <div class="card-footer bg-white py-3 border-top d-flex justify-content-end">
                {{ $riwayat->links() }}
            </div>
        @endif
    </div>
</x-layout>
