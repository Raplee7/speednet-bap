@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-12">
            {{-- Notifikasi --}}
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show rounded-4" role="alert">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                        class="bi bi-check-circle-fill flex-shrink-0 me-2" viewBox="0 0 16 16" role="img"
                        aria-label="Success:">
                        <path
                            d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z" />
                    </svg>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show rounded-4" role="alert">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                        class="bi bi-exclamation-triangle-fill flex-shrink-0 me-2" viewBox="0 0 16 16" role="img"
                        aria-label="Warning:">
                        <path
                            d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z" />
                    </svg>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card shadow border-0 rounded-4 mb-4">
                {{-- Card Header Utama --}}
                <div class="card-header shadow-sm text-white rounded-top-4 py-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0 fw-semibold">
                                Invoice: {{ $payment->nomor_invoice }}
                            </h3>
                        </div>
                        <div>
                            @php
                                $statusClass = '';
                                $statusText = Str::title(str_replace('_', ' ', $payment->status_pembayaran));
                                $statusIcon = '';
                                switch ($payment->status_pembayaran) {
                                    case 'unpaid':
                                        $statusClass = 'bg-white text-warning border border-warning';
                                        $statusText = 'Belum Bayar';
                                        $statusIcon =
                                            '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-hourglass-split me-1" viewBox="0 0 16 16"><path d="M2.5 15a.5.5 0 1 1 0-1h1v-1a4.5 4.5 0 0 1 2.557-4.06c.29-.139.443-.377.443-.59v-.7c0-.213-.154-.451-.443-.59A4.5 4.5 0 0 1 3.5 3V2h-1a.5.5 0 0 1 0-1h11a.5.5 0 0 1 0 1h-1v1a4.5 4.5 0 0 1-2.557 4.06c-.29.139-.443.377-.443.59v.7c0 .213.154.451.443.59A4.5 4.5 0 0 1 12.5 13v1h1a.5.5 0 0 1 0 1h-11zm2-13v1c0 .537.12 1.045.337 1.5h6.326c.216-.455.337-.963.337-1.5V2h-7zm3 6.35c0 .701-.478 1.236-1.011 1.492A3.5 3.5 0 0 0 4.5 13V9.25c0-.807.501-1.492 1.153-1.818a.5.5 0 0 1 .592.092l.062.078a.5.5 0 0 1 .092.592A3.5 3.5 0 0 0 7.5 9.25v.1z"/></svg>';
                                        break;
                                    case 'pending_confirmation':
                                        $statusClass = 'bg-white text-info border border-info';
                                        $statusText = 'Menunggu Konfirmasi';
                                        $statusIcon =
                                            '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-clockwise me-1" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2v1z"/><path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466z"/></svg>';
                                        break;
                                    case 'paid':
                                        $statusClass = 'bg-white text-success border border-success';
                                        $statusText = 'Lunas';
                                        $statusIcon =
                                            '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check-circle-fill me-1" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/></svg>';
                                        break;
                                    case 'failed':
                                        $statusClass = 'bg-white text-danger border border-danger';
                                        $statusText = 'Gagal';
                                        $statusIcon =
                                            '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x-octagon-fill me-1" viewBox="0 0 16 16"><path d="M11.46.146A.5.5 0 0 0 11.107 0H4.893a.5.5 0 0 0-.353.146L.146 4.54A.5.5 0 0 0 0 4.893v6.214a.5.5 0 0 0 .146.353l4.394 4.394a.5.5 0 0 0 .353.146h6.214a.5.5 0 0 0 .353-.146l4.394-4.394a.5.5 0 0 0 .146-.353V4.893a.5.5 0 0 0-.146-.353L11.46.146zm-6.106 4.5L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 1 1 .708-.708z"/></svg>';
                                        break;
                                    case 'cancelled':
                                        $statusClass = 'bg-white text-secondary border border-secondary';
                                        $statusText = 'Dibatalkan';
                                        $statusIcon =
                                            '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-slash-circle-fill me-1" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-4.646-2.646a.5.5 0 0 0-.708-.708l-6 6a.5.5 0 0 0 .708.708l6-6z"/></svg>';
                                        break;
                                }
                            @endphp
                            <span
                                class="{{ $statusClass }} rounded-pill px-3 py-2 text-capitalize fw-bold shadow-sm d-inline-flex align-items-center">
                                {!! $statusIcon !!}
                                {{ $statusText }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="card-body p-lg-5 p-4">
                    <div class="row gx-lg-5">
                        {{-- Kolom Kiri: Detail Informasi --}}
                        <div class="col-lg-7">
                            <h4 class="mb-4 pb-3 border-bottom fw-light text-muted">Rincian Tagihan</h4>

                            {{-- Card: Informasi Pelanggan Terkait --}}
                            <div class="card shadow-sm border-light rounded-3 mb-4">
                                <div class="card-body p-4">
                                    <h6 class="card-title mb-3 text-primary fw-bold d-flex align-items-center">
                                        Informasi Pelanggan
                                    </h6>
                                    <dl class="row mb-0">
                                        <dt class="col-sm-4 text-muted small">Nama Pelanggan</dt>
                                        <dd class="col-sm-8 fw-semibold">{{ $payment->customer->nama_customer ?? '-' }}</dd>

                                        <dt class="col-sm-4 text-muted small">ID Pelanggan</dt>
                                        <dd class="col-sm-8 fw-semibold">{{ $payment->customer->id_customer ?? '-' }}</dd>

                                        <dt class="col-sm-4 text-muted small">Paket Terpasang</dt>
                                        <dd class="col-sm-8 fw-semibold">
                                            {{ $payment->customer->paket->nama_paket ?? ($payment->customer->paket->kecepatan_paket ?? '-') }}
                                            @if ($payment->customer->paket)
                                                <span
                                                    class="badge bg-light text-dark ms-1">Rp{{ number_format($payment->customer->paket->harga_paket ?? 0, 0, ',', '.') }}</span>
                                            @endif
                                        </dd>
                                    </dl>
                                </div>
                            </div>

                            {{-- Card: Detail Tagihan Ini --}}
                            <div class="card shadow-sm border-light rounded-3 mb-4">
                                <div class="card-body p-4">
                                    <h6 class="card-title mb-3 text-primary fw-bold d-flex align-items-center">
                                        Rincian Tagihan Ini
                                    </h6>
                                    <dl class="row mb-0">
                                        <dt class="col-sm-5 text-muted small">Periode Layanan</dt>
                                        <dd class="col-sm-7 fw-semibold">
                                            {{-- Menampilkan periode dengan tanggal selesai +1 hari secara visual --}}
                                            {{ \Carbon\Carbon::parse($payment->periode_tagihan_mulai)->locale('id')->setTimezone('Asia/Pontianak')->translatedFormat('d M Y') }}
                                            &mdash;
                                            {{ \Carbon\Carbon::parse($payment->periode_tagihan_selesai)->addDay()->translatedFormat('d M Y') }}
                                        </dd>

                                        <dt class="col-sm-5 text-muted small">Durasi</dt>
                                        <dd class="col-sm-7 fw-semibold">{{ $payment->durasi_pembayaran_bulan }} bulan</dd>

                                        @if ($payment->status_pembayaran == 'paid')
                                            <dt class="col-sm-5 text-muted small">Layanan Aktif Hingga</dt>
                                            <dd class="col-sm-7 fw-semibold text-success">
                                                {{-- Menampilkan layanan aktif hingga +1 hari secara visual --}}
                                                {{ \Carbon\Carbon::parse($payment->periode_tagihan_selesai)->locale('id')->setTimezone('Asia/Pontianak')->addDay()->translatedFormat('d F Y') }}

                                            </dd>
                                        @endif
                                    </dl>
                                    <hr class="my-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-muted fw-bold">TOTAL TAGIHAN</span>
                                        <span class="fw-bolder fs-4 text-danger">Rp
                                            {{ number_format($payment->jumlah_tagihan, 0, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Card: Informasi Pembayaran (Jika ada) --}}
                            @if (
                                $payment->status_pembayaran == 'paid' ||
                                    $payment->bukti_pembayaran ||
                                    ($payment->status_pembayaran == 'failed' && $payment->pengonfirmasiPembayaran) ||
                                    ($payment->status_pembayaran == 'cancelled' && $payment->pengonfirmasiPembayaran))
                                <div class="card shadow-sm border-light rounded-3 mb-4">
                                    <div class="card-body p-4">
                                        <h6 class="card-title mb-3 text-primary fw-bold d-flex align-items-center">
                                            Detail Pembayaran & Proses
                                        </h6>
                                        @if ($payment->status_pembayaran == 'paid')
                                            <dl class="row mb-0">
                                                <dt class="col-sm-5 text-muted small">Tanggal Pembayaran</dt>
                                                <dd class="col-sm-7 fw-semibold">
                                                    {{ $payment->tanggal_pembayaran ? \Carbon\Carbon::parse($payment->tanggal_pembayaran)->locale('id')->setTimezone('Asia/Pontianak')->translatedFormat('d M Y, H:i') : '-' }}
                                                </dd>
                                                <dt class="col-sm-5 text-muted small">Metode</dt>
                                                <dd class="col-sm-7 fw-semibold">
                                                    {{ $payment->metode_pembayaran ? Str::title($payment->metode_pembayaran) : '-' }}
                                                </dd>
                                                @if ($payment->metode_pembayaran == 'transfer' && $payment->ewallet)
                                                    <dt class="col-sm-5 text-muted small">Dibayar ke</dt>
                                                    <dd class="col-sm-7 fw-semibold">{{ $payment->ewallet->nama_ewallet }}
                                                        ({{ $payment->ewallet->no_ewallet }} a/n
                                                        {{ $payment->ewallet->atas_nama }})</dd>
                                                @endif
                                                <dt class="col-sm-5 text-muted small">Dikonfirmasi Oleh</dt>
                                                <dd class="col-sm-7 fw-semibold">
                                                    {{ $payment->pengonfirmasiPembayaran->nama_user ?? '-' }}</dd>
                                            </dl>
                                        @endif

                                        @if ($payment->bukti_pembayaran)
                                            <div class="mt-3">
                                                <label class="text-muted small mb-1 d-block">Bukti Pembayaran
                                                    Pelanggan:</label>
                                                <a href="#" data-bs-toggle="modal"
                                                    data-bs-target="#modalBuktiBayar_{{ $payment->id_payment }}">
                                                    <img src="{{ asset('storage/' . $payment->bukti_pembayaran) }}"
                                                        alt="Bukti Pembayaran" class="img-thumbnail rounded shadow-sm"
                                                        style="max-height: 120px; cursor: zoom-in;">
                                                </a>
                                            </div>
                                            <div class="modal fade" id="modalBuktiBayar_{{ $payment->id_payment }}"
                                                tabindex="-1"
                                                aria-labelledby="modalBuktiBayarLabel_{{ $payment->id_payment }}"
                                                aria-hidden="true">
                                                <div class="modal-dialog modal-lg modal-dialog-centered">
                                                    <div class="modal-content rounded-4">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Bukti - {{ $payment->nomor_invoice }}
                                                            </h5><button type="button" class="btn-close"
                                                                data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body text-center p-0"><img
                                                                src="{{ asset('storage/' . $payment->bukti_pembayaran) }}"
                                                                alt="Bukti Pembayaran" class="img-fluid"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                        @if (
                                            ($payment->status_pembayaran == 'failed' || $payment->status_pembayaran == 'cancelled') &&
                                                $payment->pengonfirmasiPembayaran)
                                            <dl class="row mb-0 mt-2">
                                                <dt class="col-sm-5 text-muted small">Diproses Oleh</dt>
                                                <dd class="col-sm-7 fw-semibold">
                                                    {{ $payment->pengonfirmasiPembayaran->nama_user ?? '-' }}</dd>
                                                <dt class="col-sm-5 text-muted small">Tanggal Proses</dt>
                                                <dd class="col-sm-7 fw-semibold">
                                                    {{ $payment->updated_at->translatedFormat('d M Y, H:i') }}</dd>
                                            </dl>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            @if ($payment->catatan_admin)
                                <div class="card shadow-sm border-light rounded-3">
                                    <div class="card-body p-4">
                                        <h6 class="card-title mb-3 text-primary fw-bold d-flex align-items-center">
                                            Catatan Admin
                                        </h6>
                                        <p class="text-muted mb-0" style="white-space: pre-wrap;">
                                            {{ $payment->catatan_admin }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- Kolom Kanan: Aksi Administrator --}}
                        <div class="col-lg-5">
                            <h5 class="border-bottom pb-2 mb-4">Panel Tindakan</h5>
                            <div class="card shadow-sm border-light rounded-3 sticky-top" style="top: 20px;">
                                <div class="card-body p-4">
                                    @if ($payment->status_pembayaran == 'pending_confirmation')
                                        <p class="text-info small mb-3">Pelanggan telah mengupload bukti. Silakan
                                            verifikasi.</p>
                                        <form action="{{ route('payments.processVerification', $payment->id_payment) }}"
                                            method="POST" id="formVerifikasi">
                                            @csrf
                                            <div class="mb-3">
                                                <label for="catatan_admin_verifikasi"
                                                    class="form-label text-muted small">Catatan Verifikasi
                                                    (Opsional):</label>
                                                <textarea name="catatan_admin_verifikasi" id="catatan_admin_verifikasi"
                                                    class="form-control form-control-sm rounded-3" rows="3"
                                                    placeholder="Misal: Transfer dari rekening X, sudah sesuai."></textarea>
                                            </div>
                                            <div class="d-grid gap-2">
                                                <button type="submit" name="aksi_konfirmasi" value="lunas"
                                                    class="btn btn-success rounded-pill fw-semibold py-2">
                                                    Setujui & LUNAS
                                                </button>
                                                <button type="button"
                                                    class="btn btn-danger rounded-pill fw-semibold py-2 btn-tolak-verifikasi-alt">
                                                    Tolak Pembayaran
                                                </button>
                                            </div>
                                            <input type="hidden" name="aksi_konfirmasi_hidden_alt"
                                                id="aksi_konfirmasi_hidden_alt">
                                        </form>
                                    @elseif ($payment->status_pembayaran == 'unpaid')
                                        <p class="text-warning small mb-3">Tagihan ini belum dibayar.</p>
                                        <div class="d-grid gap-2">
                                            <form
                                                action="{{ route('payments.processCashPayment', $payment->id_payment) }}"
                                                method="POST" class="d-grid form-process-cash">
                                                @csrf
                                                <button type="button"
                                                    class="btn btn-success rounded-pill fw-semibold py-2 btn-process-cash"
                                                    data-invoice="{{ $payment->nomor_invoice }}">
                                                    Proses Pembayaran Tunai
                                                </button>
                                            </form>
                                            <form action="{{ route('payments.cancelInvoice', $payment->id_payment) }}"
                                                method="POST" class="d-grid form-cancel-invoice mt-2">
                                                @csrf
                                                <button type="button"
                                                    class="btn btn-outline-danger rounded-pill fw-semibold py-2 btn-cancel-invoice"
                                                    data-invoice="{{ $payment->nomor_invoice }}">
                                                    Batalkan Tagihan Ini
                                                </button>
                                            </form>
                                        </div>
                                    @elseif ($payment->status_pembayaran == 'paid')
                                        <div class="alert alert-success d-flex align-items-center shadow-sm border-0 rounded-3"
                                            role="alert">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                fill="currentColor" class="bi bi-patch-check-fill flex-shrink-0 me-2"
                                                viewBox="0 0 16 16" role="img" aria-label="Success:">
                                                <path
                                                    d="M10.067.87a2.89 2.89 0 0 0-4.134 0l-.622.638-.89-.011a2.89 2.89 0 0 0-2.924 2.924l.01.89-.636.622a2.89 2.89 0 0 0 0 4.134l.637.622-.011.89a2.89 2.89 0 0 0 2.924 2.924l.89-.01.622.636a2.89 2.89 0 0 0 4.134 0l.622-.637.89.011a2.89 2.89 0 0 0 2.924-2.924l-.01-.89.636-.622a2.89 2.89 0 0 0 0-4.134l-.637-.622.011-.89a2.89 2.89 0 0 0-2.924-2.924l-.89.01-.622-.636zm.287 5.984-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7 8.793l2.646-2.647a.5.5 0 0 1 .708.708z" />
                                            </svg>
                                            <div>Tagihan ini sudah <strong>LUNAS</strong>.</div>
                                        </div>
                                        <a href="{{ route('payments.print_invoice_admin', $payment->id_payment) }}"
                                            target="_blank"
                                            class="btn btn-outline-primary d-block rounded-pill mt-2 fw-semibold py-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                fill="currentColor" class="bi bi-printer-fill me-1" viewBox="0 0 16 16">
                                                <path
                                                    d="M5 1a2 2 0 0 0-2 2v1h10V3a2 2 0 0 0-2-2H5zm6 8H5a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1v-3a1 1 0 0 0-1-1z" />
                                                <path
                                                    d="M0 7a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2h-1v-2a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v2H2a2 2 0 0 1-2-2V7zm2.5 1a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z" />
                                            </svg>
                                            Cetak Struk Pembayaran
                                        </a>
                                    @else
                                        <div class="alert alert-secondary shadow-sm border-0 rounded-3" role="alert">
                                            Tidak ada aksi yang dapat dilakukan untuk status pembayaran ini
                                            ({{ $statusText }}).
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer bg-light-subtle p-3 rounded-bottom-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted small">
                            Dibuat: {{ $payment->created_at->translatedFormat('d M Y, H:i') }} oleh
                            {{ $payment->pembuatTagihan->nama_user ?? 'Sistem' }}
                        </span>
                        <a href="{{ route('payments.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill">
                            Kembali ke Daftar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Script SweetAlert tetap sama
        document.addEventListener('DOMContentLoaded', function() {
            const cashPaymentButtons = document.querySelectorAll('.btn-process-cash');
            cashPaymentButtons.forEach(button => {
                button.addEventListener('click', function(event) {
                    event.preventDefault();
                    const form = this.closest('form');
                    const invoice = this.getAttribute('data-invoice');
                    Swal.fire({
                        title: 'Konfirmasi Pembayaran Tunai',
                        html: `Yakin ingin memproses pembayaran tunai untuk Invoice <strong>"${invoice}"</strong>? <br>Status akan menjadi LUNAS.`,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#198754',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Ya, Proses!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });

            const cancelInvoiceButtons = document.querySelectorAll('.btn-cancel-invoice');
            cancelInvoiceButtons.forEach(button => {
                button.addEventListener('click', function(event) {
                    event.preventDefault();
                    const form = this.closest('form');
                    const invoice = this.getAttribute('data-invoice');
                    Swal.fire({
                        title: 'Yakin ingin membatalkan tagihan?',
                        html: `Tagihan <strong>"${invoice}"</strong> akan dibatalkan. <br><small class="text-danger">Aksi ini mungkin tidak bisa diurungkan sepenuhnya.</small>`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#dc3545',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Ya, batalkan!',
                        cancelButtonText: 'Tidak Jadi'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });

            const tolakVerifikasiButtonsAlt = document.querySelectorAll('.btn-tolak-verifikasi-alt');
            tolakVerifikasiButtonsAlt.forEach(button => {
                button.addEventListener('click', function(event) {
                    event.preventDefault();
                    const form = this.closest('form');
                    const hiddenInputAksi = form.querySelector('#aksi_konfirmasi_hidden_alt');
                    const catatanTextarea = form.querySelector('#catatan_admin_verifikasi');
                    Swal.fire({
                        title: 'Tolak Verifikasi Pembayaran?',
                        text: "Anda akan menolak bukti pembayaran ini. Status akan menjadi 'Gagal'.",
                        icon: 'warning',
                        input: 'textarea',
                        inputLabel: 'Alasan Penolakan (Wajib diisi jika menolak)',
                        inputPlaceholder: 'Masukkan alasan mengapa pembayaran ini ditolak...',
                        inputAttributes: {
                            'aria-label': 'Ketik alasan penolakan di sini'
                        },
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Tolak!',
                        cancelButtonText: 'Batal',
                        confirmButtonColor: '#dc3545',
                        cancelButtonColor: '#6c757d',
                        inputValidator: (value) => {
                            if (!value) {
                                return 'Anda harus mengisi alasan penolakan!'
                            }
                        },
                        preConfirm: (alasan) => {
                            return alasan;
                        }
                    }).then((result) => {
                        if (result.isConfirmed && result.value) {
                            if (hiddenInputAksi) {
                                hiddenInputAksi.name = 'aksi_konfirmasi';
                                hiddenInputAksi.value = 'tolak';
                            }
                            if (catatanTextarea) {
                                const catatanSebelumnya = catatanTextarea.value.trim();
                                const alasanPenolakan = result.value.trim();
                                catatanTextarea.value = catatanSebelumnya ?
                                    `${catatanSebelumnya}\n[Alasan Penolakan]: ${alasanPenolakan}` :
                                    `[Alasan Penolakan]: ${alasanPenolakan}`;
                            }
                            form.submit();
                        }
                    });
                });
            });
        });
    </script>
@endpush
