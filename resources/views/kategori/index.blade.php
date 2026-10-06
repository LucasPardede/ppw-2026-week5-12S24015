<x-layout title="Kelola Kategori Koleksi — SIPUS-Del">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h2 class="fw-bold mb-1 text-dark">
                <i class="bi bi-tags text-del me-2"></i>Kategori Koleksi Buku
            </h2>
            <p class="text-muted mb-0 small">Kelola klasifikasi dan pengelompokan buku perpustakaan Institut Teknologi Del.</p>
        </div>
        <div>
            <a href="{{ route('kategori.create') }}" class="btn btn-del rounded-pill px-4 shadow-sm">
                <i class="bi bi-plus-circle me-1"></i> Tambah Kategori Baru
            </a>
        </div>
    </div>

    <div class="card card-del border-0 shadow-sm overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom">
            <h5 class="card-title mb-0 fw-semibold text-secondary">
                <i class="bi bi-list-ul me-2"></i>Daftar Seluruh Kategori ({{ $kategoris->total() }} Data)
            </h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-secondary text-uppercase small" style="font-size: 0.8rem; letter-spacing: 0.5px;">
                    <tr>
                        <th class="ps-4" style="width: 5%;">No</th>
                        <th style="width: 20%;">Kode Kategori</th>
                        <th style="width: 35%;">Nama Kategori</th>
                        <th style="width: 15%;" class="text-center">Jumlah Buku</th>
                        <th style="width: 25%;" class="text-end pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($kategoris as $index => $kategori)
                        <tr>
                            <td class="ps-4 text-muted fw-semibold">{{ $kategoris->firstItem() + $index }}</td>
                            <td>
                                <span class="badge badge-del px-2 py-1 fs-7 font-monospace">
                                    {{ $kategori->kode_kategori }}
                                </span>
                            </td>
                            <td class="fw-semibold text-dark">
                                <a href="{{ route('kategori.show', $kategori) }}" class="text-decoration-none text-dark hover-del">
                                    {{ $kategori->nama_kategori }}
                                </a>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark border px-3 py-1 rounded-pill">
                                    <i class="bi bi-book me-1 text-del"></i> {{ $kategori->bukus_count }} Judul
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('kategori.show', $kategori) }}" class="btn btn-outline-secondary" title="Lihat Detail & Buku">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('kategori.edit', $kategori) }}" class="btn btn-outline-primary" title="Ubah Kategori">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <form action="{{ route('kategori.destroy', $kategori) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kategori \'{{ $kategori->nama_kategori }}\'?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Hapus Kategori">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-folder-x fs-1 d-block mb-2 text-secondary"></i>
                                    <p class="mb-2 fw-semibold">Belum ada data kategori yang terdaftar.</p>
                                    <a href="{{ route('kategori.create') }}" class="btn btn-del btn-sm rounded-pill">
                                        <i class="bi bi-plus-lg me-1"></i> Tambah Sekarang
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($kategoris->hasPages())
            <div class="card-footer bg-white py-3 border-top d-flex justify-content-between align-items-center">
                <div class="small text-muted">
                    Menampilkan {{ $kategoris->firstItem() }} - {{ $kategoris->lastItem() }} dari total {{ $kategoris->total() }} kategori
                </div>
                <div>
                    {{ $kategoris->links() }}
                </div>
            </div>
        @endif
    </div>
</x-layout>
