<x-layout title="Katalog: {{ $buku->judul }} — SIPUS-Del">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <a href="{{ route('katalog.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Katalog
                </a>
                <span class="badge badge-del px-3 py-2 rounded-pill">
                    {{ $buku->kategori->nama_kategori ?? 'Umum' }}
                </span>
            </div>

            <div class="card card-del border-0 shadow-sm overflow-hidden mb-4">
                <div class="card-header bg-del text-white p-4">
                    <span class="badge bg-white text-dark small mb-2 font-monospace">ISBN: {{ $buku->isbn }}</span>
                    <h2 class="fw-bold mb-1">{{ $buku->judul }}</h2>
                    <p class="mb-0 text-white-50 small">Oleh: {{ $buku->penulis }} • Diterbitkan oleh {{ $buku->penerbit }} ({{ $buku->tahun_terbit }})</p>
                </div>

                <div class="card-body p-4 p-md-5">
                    <div class="row g-4 mb-4">
                        <div class="col-md-7">
                            <h5 class="fw-bold text-dark mb-3">Sinopsis & Deskripsi Buku</h5>
                            <p class="text-secondary lh-lg mb-0 text-justify">
                                {{ $buku->sinopsis ?? 'Belum ada sinopsis deskriptif yang diunggah untuk buku ini.' }}
                            </p>
                        </div>

                        <div class="col-md-5">
                            <div class="bg-light p-4 rounded-3 border">
                                <h6 class="fw-bold text-dark mb-3">Informasi Ketersediaan</h6>
                                
                                <div class="mb-3">
                                    <span class="text-muted small d-block mb-1">Status Stok Fisik:</span>
                                    @if($buku->status_stok === 'tersedia')
                                        <span class="badge bg-success fs-6 px-3 py-2 rounded-pill">
                                            <i class="bi bi-check-circle me-1"></i> {{ $buku->stok }} Eksemplar Tersedia
                                        </span>
                                    @elseif($buku->status_stok === 'menipis')
                                        <span class="badge bg-warning text-dark fs-6 px-3 py-2 rounded-pill">
                                            <i class="bi bi-exclamation-triangle me-1"></i> Sisa {{ $buku->stok }} Eksemplar
                                        </span>
                                    @else
                                        <span class="badge bg-danger fs-6 px-3 py-2 rounded-pill">
                                            <i class="bi bi-x-circle me-1"></i> Stok Sedang Habis
                                        </span>
                                    @endif
                                </div>

                                <ul class="list-unstyled small text-muted mb-4 border-top pt-3">
                                    <li class="mb-2"><i class="bi bi-check2 text-success me-2"></i>Maksimal pinjam: <strong>3 buku aktif</strong></li>
                                    <li class="mb-2"><i class="bi bi-clock text-primary me-2"></i>Masa peminjaman: <strong>7 hari kalender</strong></li>
                                    <li class="mb-2"><i class="bi bi-arrow-repeat text-info me-2"></i>Dapat diperpanjang <strong>1 kali</strong></li>
                                    <li><i class="bi bi-cash text-warning me-2"></i>Tarif denda: <strong>Rp1.000 / hari</strong> jika terlambat</li>
                                </ul>

                                @if($buku->stok > 0)
                                    <form action="{{ route('katalog.pinjam', $buku) }}" method="POST" onsubmit="return confirm('Konfirmasi peminjaman buku \'{{ $buku->judul }}\'? Batas pengembalian 7 hari.')">
                                        @csrf
                                        <button type="submit" class="btn btn-del w-100 py-2 rounded-pill fw-semibold shadow-sm">
                                            <i class="bi bi-bookmark-plus me-1"></i> Ajukan Peminjaman Buku
                                        </button>
                                    </form>
                                @else
                                    <div class="alert alert-secondary text-center small mb-0 rounded-pill">
                                        Buku tidak dapat dipinjam saat ini karena stok kosong.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layout>
