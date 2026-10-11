<x-layout title="Data Mahasiswa Peminjam — SIPUS-Del Admin">
    {{-- Breadcrumb --}}
    <nav aria-label="Breadcrumb" class="mb-3">
        <ol class="breadcrumb breadcrumb-del mb-0" style="font-size: 0.82rem;">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Data Mahasiswa</li>
        </ol>
    </nav>

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h1 class="fw-bold mb-1 text-dark fs-3">
                <i class="bi bi-people text-del me-2" aria-hidden="true"></i>Data Mahasiswa & Pengguna
            </h1>
            <p class="text-muted mb-0 small">Pantau keaktifan peminjaman, status tunggakan denda, dan riwayat sirkulasi mahasiswa.</p>
        </div>
    </div>

    {{-- Filter Pencarian --}}
    <div class="card card-del border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('admin.mahasiswa.index') }}" class="row g-2 align-items-center">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted border-end-0">
                            <i class="bi bi-search" aria-hidden="true"></i>
                        </span>
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}" 
                               class="form-control border-start-0 ps-0" 
                               placeholder="Cari berdasarkan nama, NIM, atau email mahasiswa..."
                               aria-label="Cari mahasiswa">
                    </div>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-del rounded-pill w-100">
                        <i class="bi bi-search me-1" aria-hidden="true"></i> Cari Data
                    </button>
                </div>
                @if(request('search'))
                    <div class="col-md-3">
                        <a href="{{ route('admin.mahasiswa.index') }}" class="btn btn-outline-secondary rounded-pill w-100">
                            <i class="bi bi-arrow-counterclockwise me-1" aria-hidden="true"></i> Reset Pencarian
                        </a>
                    </div>
                @endif
            </form>
        </div>
    </div>

    {{-- Tabel Mahasiswa --}}
    <div class="card card-del border-0 shadow-sm overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h2 class="h6 card-title mb-0 fw-semibold text-secondary">
                <i class="bi bi-person-lines-fill me-2" aria-hidden="true"></i>Daftar Mahasiswa Terdaftar
            </h2>
            <span class="badge badge-del px-3 py-2 rounded-pill" style="font-size: 0.72rem;">
                <i class="bi bi-people me-1"></i>{{ $mahasiswas->total() }} mahasiswa
            </span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-secondary text-uppercase small" style="font-size: 0.8rem;">
                    <tr>
                        <th class="ps-4">No</th>
                        <th>Identitas Mahasiswa</th>
                        <th>NIM</th>
                        <th>Surel Del</th>
                        <th class="text-center">Pinjaman Aktif</th>
                        <th class="text-center">Total Riwayat</th>
                        <th>Tunggakan Denda</th>
                        <th class="text-end pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($mahasiswas as $index => $mhs)
                        @php
                            $dendaMhs = $mhs->totalDendaBelumLunas();
                        @endphp
                        <tr>
                            <td class="ps-4 text-muted fw-semibold">{{ $mahasiswas->firstItem() + $index }}</td>
                            <td>
                                <a href="{{ route('admin.mahasiswa.show', $mhs) }}" class="fw-bold text-dark text-decoration-none">
                                    {{ $mhs->name }}
                                </a>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border font-monospace">{{ $mhs->nim ?? '-' }}</span>
                            </td>
                            <td class="text-secondary small">{{ $mhs->email }}</td>
                            <td class="text-center">
                                @if($mhs->pinjaman_aktif_count > 0)
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-3 py-1">
                                        {{ $mhs->pinjaman_aktif_count }} Buku
                                    </span>
                                @else
                                    <span class="badge bg-light text-muted border rounded-pill px-3 py-1">0</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark border rounded-pill px-3 py-1">
                                    {{ $mhs->total_pinjam_count }} Kali
                                </span>
                            </td>
                            <td>
                                @if($dendaMhs > 0)
                                    <span class="text-danger fw-bold font-monospace">
                                        Rp{{ number_format($dendaMhs, 0, ',', '.') }}
                                    </span>
                                    <span class="badge bg-danger ms-1 small">Tertunggak</span>
                                @else
                                    <span class="text-success small"><i class="bi bi-check-circle me-1"></i> Bersih</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <a href="{{ route('admin.mahasiswa.show', $mhs) }}" class="btn btn-outline-del btn-sm rounded-pill px-3" title="Lihat Riwayat Lengkap">
                                    <i class="bi bi-clock-history me-1"></i> Rincian
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-person-x fs-2 d-block mb-2 text-secondary"></i>
                                Tidak ditemukan data mahasiswa yang sesuai.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($mahasiswas->hasPages())
            <div class="card-footer bg-white py-3 border-top d-flex justify-content-between align-items-center">
                <div class="small text-muted">
                    Menampilkan {{ $mahasiswas->firstItem() }} - {{ $mahasiswas->lastItem() }} dari {{ $mahasiswas->total() }} mahasiswa
                </div>
                <div>
                    {{ $mahasiswas->links() }}
                </div>
            </div>
        @endif
    </div>
</x-layout>
