<x-layout title="Data Mahasiswa Peminjam — SIPUS-Del Admin">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h2 class="fw-bold mb-1 text-dark">
                <i class="bi bi-people text-del me-2"></i>Data Mahasiswa & Pengguna
            </h2>
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
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}" 
                               class="form-control border-start-0 ps-0" 
                               placeholder="Cari berdasarkan nama, NIM, atau email mahasiswa...">
                    </div>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-del w-100">
                        <i class="bi bi-search me-1"></i> Cari Data
                    </button>
                </div>
                @if(request('search'))
                    <div class="col-md-3">
                        <a href="{{ route('admin.mahasiswa.index') }}" class="btn btn-outline-secondary w-100">
                            Reset Pencarian
                        </a>
                    </div>
                @endif
            </form>
        </div>
    </div>

    {{-- Tabel Mahasiswa --}}
    <div class="card card-del border-0 shadow-sm overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom">
            <h5 class="card-title mb-0 fw-semibold text-secondary">
                <i class="bi bi-person-lines-fill me-2"></i>Daftar Mahasiswa ({{ $mahasiswas->total() }} Mahasiswa)
            </h5>
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
