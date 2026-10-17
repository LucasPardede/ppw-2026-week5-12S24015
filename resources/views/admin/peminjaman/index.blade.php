<x-layout title="Kelola Transaksi Peminjaman — SIPUS-Del Admin">
    {{-- Breadcrumb --}}
    <nav aria-label="Breadcrumb" class="mb-3">
        <ol class="breadcrumb breadcrumb-del mb-0" style="font-size: 0.82rem;">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Transaksi Peminjaman</li>
        </ol>
    </nav>

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h1 class="fw-bold mb-1 text-dark fs-3">
                <i class="bi bi-arrow-left-right text-del me-2" aria-hidden="true"></i>Transaksi Peminjaman & Sirkulasi
            </h1>
            <p class="text-muted mb-0 small">Pantau sirkulasi peminjaman, catat pengembalian buku, dan kelola denda keterlambatan.</p>
        </div>
    </div>

    {{-- Filter Status dan Pencarian --}}
    <div class="card card-del border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                {{-- Tombol Filter Status --}}
                <div class="btn-group btn-group-sm" role="group" aria-label="Filter status peminjaman">
                    <a href="{{ route('admin.peminjaman.index', array_merge(request()->except('status'), [])) }}" 
                       class="btn {{ !request('status') ? 'btn-del' : 'btn-outline-secondary' }}">
                        Semua ({{ \App\Models\Peminjaman::count() }})
                    </a>
                    <a href="{{ route('admin.peminjaman.index', array_merge(request()->except('status'), ['status' => 'dipinjam'])) }}" 
                       class="btn {{ request('status') === 'dipinjam' ? 'btn-del' : 'btn-outline-secondary' }}">
                        Aktif / Dipinjam
                    </a>
                    <a href="{{ route('admin.peminjaman.index', array_merge(request()->except('status'), ['status' => 'terlambat'])) }}" 
                       class="btn {{ request('status') === 'terlambat' ? 'btn-danger' : 'btn-outline-danger' }}">
                        Terlambat
                    </a>
                    <a href="{{ route('admin.peminjaman.index', array_merge(request()->except('status'), ['status' => 'dikembalikan'])) }}" 
                       class="btn {{ request('status') === 'dikembalikan' ? 'btn-del' : 'btn-outline-secondary' }}">
                        Dikembalikan
                    </a>
                </div>

                {{-- Form Pencarian --}}
                <form method="GET" action="{{ route('admin.peminjaman.index') }}" class="d-flex gap-2">
                    @if(request('status'))
                        <input type="hidden" name="status" value="{{ request('status') }}">
                    @endif
                    <div class="input-group input-group-sm">
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}" 
                               class="form-control" 
                               placeholder="Cari mahasiswa, judul, ISBN..."
                               aria-label="Cari transaksi">
                        <button class="btn btn-del" type="submit" aria-label="Cari">
                            <i class="bi bi-search" aria-hidden="true"></i>
                        </button>
                    </div>
                    @if(request('search'))
                        <a href="{{ route('admin.peminjaman.index', request()->except('search')) }}" class="btn btn-outline-secondary btn-sm rounded-circle d-flex align-items-center justify-content-center" title="Hapus Pencarian" aria-label="Reset pencarian" style="width: 31px; height: 31px;">
                            <i class="bi bi-x-lg" aria-hidden="true"></i>
                        </a>
                    @endif
                </form>
            </div>
        </div>
    </div>

    {{-- Tabel Peminjaman --}}
    <div class="card card-del border-0 shadow-sm overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h2 class="h6 card-title mb-0 fw-semibold text-secondary">
                <i class="bi bi-journal-text me-2" aria-hidden="true"></i>Daftar Peminjaman
            </h2>
            <span class="badge badge-del px-3 py-2 rounded-pill" style="font-size: 0.72rem;">
                <i class="bi bi-arrow-repeat me-1"></i>{{ $peminjamans->total() }} transaksi
            </span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light text-secondary text-uppercase" style="font-size: 0.75rem;">
                    <tr>
                        <th class="ps-4">No</th>
                        <th>Mahasiswa Peminjam</th>
                        <th>Buku Yang Dipinjam</th>
                        <th>Tgl Pinjam</th>
                        <th>Jatuh Tempo</th>
                        <th>Tgl Kembali</th>
                        <th>Status</th>
                        <th>Denda</th>
                        <th class="text-end pe-4">Aksi Kelola</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($peminjamans as $index => $pinjam)
                        <tr>
                            <td class="ps-4 text-muted fw-semibold">{{ $peminjamans->firstItem() + $index }}</td>
                            <td>
                                <a href="{{ route('admin.mahasiswa.show', $pinjam->user) }}" class="fw-bold text-dark text-decoration-none hover-del">
                                    {{ $pinjam->user->name ?? 'Mahasiswa' }}
                                </a>
                                <div class="text-muted" style="font-size: 0.75rem;">
                                    <code>{{ $pinjam->user->nim ?? '-' }}</code> • {{ $pinjam->user->email }}
                                </div>
                            </td>
                            <td>
                                <a href="{{ route('buku.show', $pinjam->buku) }}" class="fw-semibold text-dark text-decoration-none hover-del">
                                    {{ $pinjam->buku->judul }}
                                </a>
                                <div class="text-muted" style="font-size: 0.75rem;">
                                    ISBN: <code>{{ $pinjam->buku->isbn }}</code>
                                </div>
                            </td>
                            <td>{{ $pinjam->tanggal_pinjam->format('d/m/Y') }}</td>
                            <td class="fw-semibold {{ $pinjam->is_terlambat ? 'text-danger' : 'text-dark' }}">
                                {{ $pinjam->tanggal_jatuh_tempo->format('d/m/Y') }}
                            </td>
                            <td>
                                {{ $pinjam->tanggal_kembali ? $pinjam->tanggal_kembali->format('d/m/Y') : '-' }}
                            </td>
                            <td>
                                @if($pinjam->status === 'dipinjam')
                                    @if($pinjam->is_terlambat)
                                        <span class="badge bg-danger rounded-pill px-2 py-1">
                                            <i class="bi bi-clock me-1" aria-hidden="true"></i> Terlambat {{ $pinjam->hari_terlambat }} hr
                                        </span>
                                    @else
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2 py-1">
                                            <i class="bi bi-arrow-repeat me-1" aria-hidden="true"></i> Dipinjam
                                        </span>
                                    @endif
                                @else
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1">
                                        <i class="bi bi-check-all me-1" aria-hidden="true"></i> Selesai
                                    </span>
                                @endif
                            </td>
                            <td class="font-monospace">
                                @if($pinjam->denda_efektif > 0)
                                    <span class="text-danger fw-bold">
                                        Rp{{ number_format($pinjam->denda_efektif, 0, ',', '.') }}
                                    </span>
                                    <div>
                                        @if($pinjam->denda_lunas)
                                            <span class="badge bg-success-subtle text-success rounded-pill" style="font-size: 0.65rem;">Lunas</span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger rounded-pill" style="font-size: 0.65rem;">Belum Lunas</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                @if($pinjam->status === 'dipinjam')
                                    <form action="{{ route('admin.peminjaman.kembali', $pinjam) }}" method="POST" class="d-inline" id="kembaliForm{{ $pinjam->id }}">
                                        @csrf
                                        @method('PATCH')
                                    </form>
                                    <button type="button" 
                                            class="btn btn-sm btn-success rounded-pill px-3 py-1 shadow-sm" 
                                            title="Tandai buku telah dikembalikan"
                                            aria-label="Kembalikan buku {{ $pinjam->buku->judul }}"
                                            data-confirm-trigger
                                            data-confirm-form="kembaliForm{{ $pinjam->id }}"
                                            data-confirm-title="Pengembalian Buku"
                                            data-confirm-message="Konfirmasi penerimaan pengembalian buku '{{ $pinjam->buku->judul }}'?"
                                            data-confirm-subtext="Stok buku akan bertambah 1 dan denda keterlambatan akan dihitung jika melewati jatuh tempo."
                                            data-confirm-ok="Ya, Kembalikan">
                                        <i class="bi bi-box-arrow-in-down me-1" aria-hidden="true"></i> Kembali
                                    </button>
                                @elseif($pinjam->status === 'dikembalikan' && $pinjam->denda > 0 && !$pinjam->denda_lunas)
                                    <form action="{{ route('admin.peminjaman.lunas', $pinjam) }}" method="POST" class="d-inline" id="lunasForm{{ $pinjam->id }}">
                                        @csrf
                                        @method('PATCH')
                                    </form>
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-success rounded-pill px-3 py-1" 
                                            title="Tandai denda telah lunas"
                                            aria-label="Pelunasan denda Rp{{ number_format($pinjam->denda, 0, ',', '.') }}"
                                            data-confirm-trigger
                                            data-confirm-form="lunasForm{{ $pinjam->id }}"
                                            data-confirm-title="Pelunasan Denda"
                                            data-confirm-message="Konfirmasi pelunasan denda sebesar Rp{{ number_format($pinjam->denda, 0, ',', '.') }}?"
                                            data-confirm-subtext="Peminjam: {{ $pinjam->user->name ?? 'Mahasiswa' }} ({{ $pinjam->user->nim ?? '-' }})."
                                            data-confirm-ok="Tandai Lunas">
                                        <i class="bi bi-cash-coin me-1" aria-hidden="true"></i> Tandai Lunas
                                    </button>
                                @else
                                    <span class="text-muted small"><i class="bi bi-check2-circle text-success" aria-hidden="true"></i> Tuntas</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary" aria-hidden="true"></i>
                                Tidak ada data peminjaman yang cocok dengan filter saat ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($peminjamans->hasPages())
            <div class="card-footer bg-white py-3 border-top d-flex justify-content-between align-items-center">
                <div class="small text-muted">
                    Menampilkan {{ $peminjamans->firstItem() }} - {{ $peminjamans->lastItem() }} dari {{ $peminjamans->total() }} peminjaman
                </div>
                <div>
                    {{ $peminjamans->links() }}
                </div>
            </div>
        @endif
    </div>
</x-layout>
