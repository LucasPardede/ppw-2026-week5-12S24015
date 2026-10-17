<x-layout title="Riwayat Mahasiswa: {{ $user->name }} — SIPUS-Del Admin">
    {{-- Breadcrumb --}}
    <nav aria-label="Breadcrumb" class="mb-3">
        <ol class="breadcrumb breadcrumb-del mb-0" style="font-size: 0.82rem;">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.mahasiswa.index') }}">Data Mahasiswa</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $user->name }}</li>
        </ol>
    </nav>

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge badge-del px-2 py-1 font-monospace">{{ $user->nim ?? 'NIM -' }}</span>
                <h1 class="fw-bold mb-0 text-dark fs-3">{{ $user->name }}</h1>
            </div>
            <p class="text-muted small mb-0">{{ $user->email }} • Riwayat aktivitas transaksi perpustakaan.</p>
        </div>
        <div>
            <a href="{{ route('admin.mahasiswa.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="bi bi-arrow-left me-1" aria-hidden="true"></i> Kembali ke Daftar Mahasiswa
            </a>
        </div>
    </div>

    {{-- Ringkasan Mahasiswa --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card card-del border-0 shadow-sm p-3">
                <span class="text-muted small text-uppercase fw-semibold">Pinjaman Aktif</span>
                <h3 class="fw-bold text-dark mb-0">{{ $user->peminjamans->where('status', 'dipinjam')->count() }} Buku</h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-del border-0 shadow-sm p-3">
                <span class="text-muted small text-uppercase fw-semibold">Total Pernah Pinjam</span>
                <h3 class="fw-bold text-primary mb-0">{{ $user->peminjamans->count() }} Kali</h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-del border-0 shadow-sm p-3">
                <span class="text-muted small text-uppercase fw-semibold">Total Tunggakan Denda</span>
                <h3 class="fw-bold text-danger mb-0 font-monospace">Rp{{ number_format($user->totalDendaBelumLunas(), 0, ',', '.') }}</h3>
            </div>
        </div>
    </div>

    {{-- Tabel Riwayat Seluruh Transaksi --}}
    <div class="card card-del border-0 shadow-sm overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom">
            <h5 class="card-title mb-0 fw-semibold text-secondary">
                <i class="bi bi-clock-history me-2"></i>Seluruh Rekam Transaksi Peminjaman ({{ $user->peminjamans->count() }} Catatan)
            </h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light text-secondary text-uppercase" style="font-size: 0.75rem;">
                    <tr>
                        <th class="ps-4">No</th>
                        <th>Buku</th>
                        <th>Kategori</th>
                        <th>Tanggal Pinjam</th>
                        <th>Jatuh Tempo</th>
                        <th>Tanggal Kembali</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Denda</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($user->peminjamans as $index => $tx)
                        <tr>
                            <td class="ps-4 text-muted fw-semibold">{{ $index + 1 }}</td>
                            <td>
                                <a href="{{ route('buku.show', $tx->buku) }}" class="fw-bold text-dark text-decoration-none">
                                    {{ $tx->buku->judul }}
                                </a>
                                <div class="text-muted" style="font-size: 0.75rem;">ISBN: <code>{{ $tx->buku->isbn }}</code></div>
                            </td>
                            <td>
                                <span class="badge badge-del">{{ $tx->buku->kategori->nama_kategori ?? '-' }}</span>
                            </td>
                            <td>{{ $tx->tanggal_pinjam->format('d/m/Y') }}</td>
                            <td class="{{ $tx->is_terlambat ? 'text-danger fw-bold' : '' }}">{{ $tx->tanggal_jatuh_tempo->format('d/m/Y') }}</td>
                            <td>{{ $tx->tanggal_kembali ? $tx->tanggal_kembali->format('d/m/Y') : '-' }}</td>
                            <td>
                                @if($tx->status === 'dipinjam')
                                    @if($tx->is_terlambat)
                                        <span class="badge bg-danger rounded-pill">Terlambat ({{ $tx->hari_terlambat }} hr)</span>
                                    @else
                                        <span class="badge bg-primary-subtle text-primary border rounded-pill">Dipinjam</span>
                                    @endif
                                @else
                                    <span class="badge bg-success-subtle text-success border rounded-pill">Dikembalikan</span>
                                @endif
                            </td>
                            <td class="text-end pe-4 font-monospace">
                                @if($tx->denda_efektif > 0)
                                    <span class="text-danger fw-bold">Rp{{ number_format($tx->denda_efektif, 0, ',', '.') }}</span>
                                    @if($tx->status === 'dikembalikan')
                                        <span class="badge {{ $tx->denda_lunas ? 'bg-success' : 'bg-danger' }} ms-1" style="font-size: 0.65rem;">
                                            {{ $tx->denda_lunas ? 'Lunas' : 'Belum Lunas' }}
                                        </span>
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                Mahasiswa ini belum pernah melakukan peminjaman buku.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layout>
