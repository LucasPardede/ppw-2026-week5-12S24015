<x-layout title="Laporan Peminjaman Koleksi — SIPUS-Del Admin">
    {{-- Breadcrumb --}}
    <nav aria-label="Breadcrumb" class="mb-3">
        <ol class="breadcrumb breadcrumb-del mb-0" style="font-size: 0.82rem;">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Laporan Sirkulasi</li>
        </ol>
    </nav>

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h1 class="fw-bold mb-1 text-dark fs-3">
                <i class="bi bi-file-earmark-bar-graph text-del me-2" aria-hidden="true"></i>Laporan Sirkulasi & Audit Denda
            </h1>
            <p class="text-muted mb-0 small">Audit transaksi peminjaman, pantau kepatuhan tenggat waktu, dan ekspor data ke format CSV.</p>
        </div>
        <div>
            <a href="{{ route('admin.laporan.export', request()->all()) }}" class="btn btn-success rounded-pill px-4 shadow-sm" aria-label="Ekspor Laporan dalam format CSV">
                <i class="bi bi-file-earmark-excel me-1" aria-hidden="true"></i> Ekspor Laporan (CSV)
            </a>
        </div>
    </div>

    {{-- Filter Rentang Tanggal & Status --}}
    <div class="card card-del border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('admin.laporan.index') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="dari_tanggal" class="form-label small fw-semibold text-secondary">Dari Tanggal Pinjam</label>
                    <input type="date" id="dari_tanggal" name="dari_tanggal" value="{{ request('dari_tanggal') }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <label for="sampai_tanggal" class="form-label small fw-semibold text-secondary">Sampai Tanggal Pinjam</label>
                    <input type="date" id="sampai_tanggal" name="sampai_tanggal" value="{{ request('sampai_tanggal') }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <label for="status" class="form-label small fw-semibold text-secondary">Status Peminjaman</label>
                    <select id="status" name="status" class="form-select">
                        <option value="">-- Semua Status --</option>
                        <option value="dipinjam" {{ request('status') === 'dipinjam' ? 'selected' : '' }}>Aktif / Dipinjam</option>
                        <option value="terlambat" {{ request('status') === 'terlambat' ? 'selected' : '' }}>Terlambat</option>
                        <option value="dikembalikan" {{ request('status') === 'dikembalikan' ? 'selected' : '' }}>Dikembalikan</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-del rounded-pill flex-grow-1">
                        <i class="bi bi-funnel me-1" aria-hidden="true"></i> Terapkan Filter
                    </button>
                    @if(request('dari_tanggal') || request('sampai_tanggal') || request('status'))
                        <a href="{{ route('admin.laporan.index') }}" class="btn btn-outline-secondary rounded-pill" title="Reset Filter" aria-label="Reset Filter">
                            <i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Tabel Laporan --}}
    <div class="card card-del border-0 shadow-sm overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h2 class="h6 card-title mb-0 fw-semibold text-secondary">
                <i class="bi bi-table me-2" aria-hidden="true"></i>Hasil Laporan Transaksi ({{ $peminjamans->total() }} Data)
            </h2>
            <span class="badge badge-del px-3 py-2 rounded-pill" style="font-size: 0.72rem;">
                <i class="bi bi-cash-stack me-1"></i>Total Denda: Rp{{ number_format($totalDenda, 0, ',', '.') }}
            </span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light text-secondary text-uppercase" style="font-size: 0.75rem;">
                    <tr>
                        <th class="ps-4">No</th>
                        <th>Mahasiswa Peminjam</th>
                        <th>Buku</th>
                        <th>Tgl Pinjam</th>
                        <th>Jatuh Tempo</th>
                        <th>Tgl Kembali</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Denda</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($peminjamans as $index => $row)
                        <tr>
                            <td class="ps-4 text-muted fw-semibold">{{ $peminjamans->firstItem() + $index }}</td>
                            <td>
                                <span class="fw-semibold text-dark">{{ $row->user->name ?? 'Mahasiswa' }}</span>
                                <div class="text-muted" style="font-size: 0.75rem;">
                                    {{ $row->user->nim ?? $row->user->email }}
                                </div>
                            </td>
                            <td>
                                <span class="fw-semibold text-dark">{{ $row->buku->judul }}</span>
                                <div class="text-muted" style="font-size: 0.75rem;">
                                    <code>{{ $row->buku->isbn }}</code>
                                </div>
                            </td>
                            <td>{{ $row->tanggal_pinjam->format('d/m/Y') }}</td>
                            <td class="{{ $row->is_terlambat ? 'text-danger fw-bold' : '' }}">
                                {{ $row->tanggal_jatuh_tempo->format('d/m/Y') }}
                            </td>
                            <td>{{ $row->tanggal_kembali ? $row->tanggal_kembali->format('d/m/Y') : '-' }}</td>
                            <td>
                                @if($row->status === 'dipinjam')
                                    @if($row->is_terlambat)
                                        <span class="badge bg-danger rounded-pill">Terlambat ({{ $row->hari_terlambat }} hr)</span>
                                    @else
                                        <span class="badge bg-primary-subtle text-primary border rounded-pill">Dipinjam</span>
                                    @endif
                                @else
                                    <span class="badge bg-success-subtle text-success border rounded-pill">Dikembalikan</span>
                                @endif
                            </td>
                            <td class="text-end pe-4 font-monospace">
                                @if($row->denda_efektif > 0)
                                    <span class="text-danger fw-bold">Rp{{ number_format($row->denda_efektif, 0, ',', '.') }}</span>
                                    <div>
                                        <span class="badge {{ $row->denda_lunas ? 'bg-success' : 'bg-danger' }} rounded-pill" style="font-size: 0.65rem;">
                                            {{ $row->denda_lunas ? 'Lunas' : 'Belum Lunas' }}
                                        </span>
                                    </div>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                Tidak ada data transaksi peminjaman yang sesuai dengan filter laporan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($peminjamans->hasPages())
            <div class="card-footer bg-white py-3 border-top d-flex justify-content-between align-items-center">
                <div class="small text-muted">
                    Menampilkan {{ $peminjamans->firstItem() }} - {{ $peminjamans->lastItem() }} dari {{ $peminjamans->total() }} data
                </div>
                <div>
                    {{ $peminjamans->links() }}
                </div>
            </div>
        @endif
    </div>
</x-layout>
