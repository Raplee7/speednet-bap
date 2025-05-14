@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-sm-12">
            {{-- Notifikasi Toastr (dari template kamu) --}}
            @if (session('success'))
                @push('scripts')
                    <script>
                        toastr.success("{{ session('success') }}");
                    </script>
                @endpush
            @endif
            @if (session('error'))
                @push('scripts')
                    <script>
                        toastr.error("{{ session('error') }}");
                    </script>
                @endpush
            @endif
            @if (session('info'))
                @push('scripts')
                    <script>
                        toastr.info("{{ session('info') }}");
                    </script>
                @endpush
            @endif

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title">{{ 'Daftar Tagihan & Pembayaran' }}</h4>
                    <a href="{{ route('payments.create') }}" class="btn btn-primary">
                        Buat Tagihan Baru
                    </a>
                </div>

                {{-- Bagian Filter --}}
                <div class="card-body border-bottom">
                    <form action="{{ route('payments.index') }}" method="GET">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label for="search_customer" class="form-label">Cari Pelanggan/Invoice:</label>
                                <input type="text" name="search_customer" id="search_customer"
                                    class="form-control form-control-sm" value="{{ request('search_customer') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="status_pembayaran" class="form-label">Status Pembayaran:</label>
                                <select name="status_pembayaran" id="status_pembayaran" class="form-select form-select-sm">
                                    <option value="">Semua Status</option>
                                    @foreach ($statuses as $value => $label)
                                        <option value="{{ $value }}"
                                            {{ request('status_pembayaran') == $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="bulan_periode" class="form-label">Periode: (YYYY-MM)</label>
                                <input type="month" name="bulan_periode" id="bulan_periode"
                                    class="form-control form-control-sm" value="{{ request('bulan_periode') }}">
                                {{-- Format YYYY-MM --}}
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-sm btn-info">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                        fill="currentColor" class="bi bi-funnel-fill me-1" viewBox="0 0 16 16">
                                        <path
                                            d="M1.5 1.5A.5.5 0 0 1 2 1h12a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.128.334L10 8.692V13.5a.5.5 0 0 1-.777.416l-2-1.5A.5.5 0 0 1 7 12V8.692L1.628 3.834A.5.5 0 0 1 1.5 3.5v-2z" />
                                    </svg>Filter
                                </button>
                                <a href="{{ route('payments.index') }}" class="btn btn-sm btn-secondary ms-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                        fill="currentColor" class="bi bi-arrow-counterclockwise me-1" viewBox="0 0 16 16">
                                        <path fill-rule="evenodd"
                                            d="M8 3a5 5 0 1 1-4.546 2.914.5.5 0 0 0-.908-.417A6 6 0 1 0 8 2z" />
                                        <path
                                            d="M8 4.466V.534a.25.25 0 0 0-.41-.192L5.23 2.308a.25.25 0 0 0 0 .384l2.36 1.966A.25.25 0 0 0 8 4.466" />
                                    </svg>Reset
                                </a>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive mt-0"> {{-- mt-4 dihapus atau dikurangi jika filter sudah ada margin --}}
                        <table class="table table-hover mb-0"> {{-- Pastikan ID datatable sesuai jika pakai JS Datatables --}}
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>No. Invoice</th>
                                    <th>Pelanggan</th>
                                    <th>Paket</th>
                                    <th>Periode</th>
                                    <th class="text-end">Jumlah Tagihan</th>
                                    <th>Jatuh Tempo</th>
                                    <th class="text-center">Status Bayar</th>
                                    <th>Metode Bayar</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($payments as $index => $payment)
                                    <tr>
                                        <td>{{ $payments->firstItem() + $index }}</td>
                                        <td>
                                            <a
                                                href="{{ route('payments.show', $payment->id_payment) }}">{{ $payment->nomor_invoice }}</a>
                                        </td>
                                        <td>
                                            @if ($payment->customer)
                                                {{ $payment->customer->nama_customer }}
                                                <small
                                                    class="d-block text-muted">{{ $payment->customer->id_customer }}</small>
                                            @else
                                                <span class="text-danger">Pelanggan tidak ditemukan</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($payment->paket)
                                                {{ $payment->paket->nama_paket ?? $payment->paket->kecepatan_paket }}
                                                {{-- Sesuaikan field nama paket --}}
                                            @else
                                                <span class="text-danger">Paket tidak ditemukan</span>
                                            @endif
                                        </td>
                                        <td>
                                            {{ Carbon\Carbon::parse($payment->periode_tagihan_mulai)->translatedFormat('d M Y') }}
                                            s/d
                                            {{ Carbon\Carbon::parse($payment->periode_tagihan_selesai)->addDay()->translatedFormat('d M Y') }}
                                            <small class="d-block text-muted">({{ $payment->durasi_pembayaran_bulan }}
                                                bulan)</small>
                                        </td>
                                        <td class="text-end">Rp {{ number_format($payment->jumlah_tagihan, 0, ',', '.') }}
                                        </td>
                                        <td>{{ Carbon\Carbon::parse($payment->tanggal_jatuh_tempo)->translatedFormat('d M Y') }}
                                        </td>
                                        <td class="text-center">
                                            @php
                                                $statusClass = '';
                                                $statusText = Str::title(
                                                    str_replace('_', ' ', $payment->status_pembayaran),
                                                );
                                                switch ($payment->status_pembayaran) {
                                                    case 'unpaid':
                                                        $statusClass = 'bg-warning-subtle text-warning-emphasis';
                                                        $statusText = 'Belum Bayar';
                                                        break;
                                                    case 'pending_confirmation':
                                                        $statusClass = 'bg-info-subtle text-info-emphasis';
                                                        $statusText = 'Menunggu Konfirmasi';
                                                        break;
                                                    case 'paid':
                                                        $statusClass = 'bg-success-subtle text-success-emphasis';
                                                        $statusText = 'Lunas';
                                                        break;
                                                    case 'failed':
                                                        $statusClass = 'bg-danger-subtle text-danger-emphasis';
                                                        $statusText = 'Gagal';
                                                        break;
                                                    case 'cancelled':
                                                        $statusClass = 'bg-secondary-subtle text-secondary-emphasis';
                                                        $statusText = 'Dibatalkan';
                                                        break;
                                                }
                                            @endphp
                                            <span class="badge rounded-pill px-3 {{ $statusClass }}">
                                                {{ $statusText }}
                                            </span>
                                            @if ($payment->status_pembayaran == 'paid' && $payment->tanggal_pembayaran)
                                                <small class="d-block text-muted">Tgl:
                                                    {{ Carbon\Carbon::parse($payment->tanggal_pembayaran)->translatedFormat('d M Y') }}</small>
                                            @endif
                                        </td>
                                        <td>{{ $payment->metode_pembayaran ? Str::title($payment->metode_pembayaran) : '-' }}
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="{{ route('payments.show', $payment->id_payment) }}"
                                                    class="btn btn-sm btn-info" title="Lihat Detail">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                        fill="currentColor" class="bi bi-eye-fill" viewBox="0 0 16 16">
                                                        <path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0z" />
                                                        <path
                                                            d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8zm8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7z" />
                                                    </svg>
                                                </a>

                                                @if ($payment->status_pembayaran == 'pending_confirmation')
                                                    {{-- Tombol Verifikasi bisa langsung di halaman show, atau modal dari sini --}}
                                                @elseif ($payment->status_pembayaran == 'unpaid')
                                                    {{-- Form untuk proses bayar tunai --}}
                                                    <form
                                                        action="{{ route('payments.processCashPayment', $payment->id_payment) }}"
                                                        method="POST" class="d-inline form-process-cash">
                                                        @csrf
                                                        <button type="button"
                                                            class="btn btn-sm btn-success btn-process-cash"
                                                            title="Proses Bayar Tunai"
                                                            data-invoice="{{ $payment->nomor_invoice }}">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="16"
                                                                height="16" fill="currentColor" class="bi bi-cash-coin"
                                                                viewBox="0 0 16 16">
                                                                <path fill-rule="evenodd"
                                                                    d="M11 15a4 4 0 1 0 0-8 4 4 0 0 0 0 8m5-4a5 5 0 1 1-10 0 5 5 0 0 1 10 0" />
                                                                <path
                                                                    d="M9.438 11.944c.047.596.518 1.06 1.363 1.116v.44h.375v-.443c.875-.061 1.386-.529 1.386-1.207 0-.618-.39-.936-1.09-1.1l-.296-.07v-1.2c.376.043.614.248.671.532h.658c-.047-.575-.54-1.024-1.329-1.073V8.5h-.375v.45c-.747.073-1.255.522-1.255 1.158 0 .562.378.92 1.007 1.066l.248.061v1.272c-.384-.058-.639-.27-.696-.563h-.668zm1.36-1.354c-.369-.085-.569-.26-.569-.522 0-.294.216-.514.572-.578v1.1zm.432.746c.449.104.655.272.655.569 0 .339-.257.571-.709.614v-1.195z" />
                                                                <path
                                                                    d="M1 0a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h4.083q.088-.517.258-1H3a2 2 0 0 0-2-2V3a2 2 0 0 0 2-2h10a2 2 0 0 0 2 2v3.528c.38.34.717.728 1 1.154V1a1 1 0 0 0-1-1z" />
                                                                <path
                                                                    d="M9.998 5.083 10 5a2 2 0 1 0-3.132 1.65 6 6 0 0 1 3.13-1.567" />
                                                            </svg>
                                                        </button>
                                                    </form>
                                                    <form
                                                        action="{{ route('payments.cancelInvoice', $payment->id_payment) }}"
                                                        method="POST" class="d-inline form-cancel-invoice">
                                                        @csrf
                                                        @method('POST') {{-- Atau PUT/PATCH jika prefer --}}
                                                        <button type="button"
                                                            class="btn btn-sm btn-danger btn-cancel-invoice"
                                                            title="Batalkan Tagihan"
                                                            data-invoice="{{ $payment->nomor_invoice }}">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="16"
                                                                height="16" fill="currentColor"
                                                                class="bi bi-x-circle-fill" viewBox="0 0 16 16">
                                                                <path
                                                                    d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM5.354 4.646a.5.5 0 1 0-.708.708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 8.707l2.646 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.646a.5.5 0 0 0-.708-.708L8 7.293 5.354 4.646z" />
                                                            </svg>
                                                        </button>
                                                    </form>
                                                @endif
                                                {{-- Tambah tombol aksi lain jika perlu --}}
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center">Data tagihan & pembayaran kosong.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{-- Pagination Links --}}
                    @if ($payments->hasPages())
                        <div class="card-footer">
                            {{ $payments->appends(request()->query())->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Script untuk konfirmasi SweetAlert Proses Bayar Tunai
            const cashPaymentButtons = document.querySelectorAll('.btn-process-cash');
            cashPaymentButtons.forEach(button => {
                button.addEventListener('click', function(event) {
                    event.preventDefault(); // Mencegah form submit langsung
                    const form = this.closest('form');
                    const invoice = this.getAttribute('data-invoice');

                    Swal.fire({
                        title: 'Konfirmasi Pembayaran Tunai',
                        text: `Yakin ingin memproses pembayaran tunai untuk Invoice "${invoice}"? Status akan menjadi LUNAS.`,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#28a745', // Warna hijau
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

            // Script untuk konfirmasi SweetAlert Batalkan Tagihan (mirip delete user kamu)
            const cancelInvoiceButtons = document.querySelectorAll('.btn-cancel-invoice');
            cancelInvoiceButtons.forEach(button => {
                button.addEventListener('click', function(event) {
                    event.preventDefault(); // Mencegah form submit langsung
                    const form = this.closest('form');
                    const invoice = this.getAttribute('data-invoice');

                    Swal.fire({
                        title: 'Yakin ingin membatalkan tagihan?',
                        text: `Tagihan "${invoice}" akan dibatalkan. Aksi ini tidak bisa diurungkan sepenuhnya jika sudah ada proses terkait.`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#e3342f', // Warna merah
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

            // Jika kamu menggunakan plugin DataTables JS, inisialisasi di sini
            // $('#datatable').DataTable();
        });
    </script>
@endpush
