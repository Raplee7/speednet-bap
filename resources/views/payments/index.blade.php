@extends('layouts.app') {{-- Sesuaikan dengan layout admin Anda --}}

@push('styles')
    <style>
        /* Efek Shine untuk Pelanggan Baru (dari kode Anda) */
        @keyframes subtleShine {
            0% {
                box-shadow: 0 0 3px rgba(52, 152, 219, 0.2);
                /* Biru muda lembut */
                background-color: rgba(224, 242, 254, 0.6);
                /* Warna dasar sedikit transparan */
            }

            50% {
                box-shadow: 0 0 8px rgba(52, 152, 219, 0.5);
                /* Biru lebih bersinar */
                background-color: rgba(186, 230, 253, 0.8);
            }

            100% {
                box-shadow: 0 0 3px rgba(52, 152, 219, 0.2);
                background-color: rgba(224, 242, 254, 0.6);
            }
        }

        tr.shine-effect>td {
            animation: subtleShine 2s infinite alternate ease-in-out;
        }

        .table-hover>tbody>tr.shine-effect:hover>td {
            animation-play-state: paused;
            background-color: #cce7ff !important;
            box-shadow: none;
        }

        /* Efek Shine BARU untuk Tagihan Pending Confirmation (Kuning/Oranye Lembut) */
        @keyframes pendingShine {
            0% {
                background-color: rgba(255, 249, 229, 0.7);
                /* Kuning sangat muda */
                box-shadow: 0 0 4px rgba(255, 193, 7, 0.3);
                /* Shadow kuning */
            }

            50% {
                background-color: rgba(255, 235, 153, 0.9);
                /* Kuning lebih terlihat */
                box-shadow: 0 0 10px rgba(255, 193, 7, 0.6);
            }

            100% {
                background-color: rgba(255, 249, 229, 0.7);
                box-shadow: 0 0 4px rgba(255, 193, 7, 0.3);
            }
        }

        tr.shine-pending>td {
            animation: pendingShine 1.8s infinite alternate ease-in-out;
        }

        .table-hover>tbody>tr.shine-pending:hover>td {
            animation-play-state: paused;
            /* Hentikan animasi saat hover */
            background-color: #fff3cd !important;
            /* Warna hover solid kuning muda */
            box-shadow: none;
        }

        .filter-card .form-select,
        .filter-card .form-control,
        .filter-card .btn {
            font-size: 0.875rem;
        }

        .table th {
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge.rounded-pill {
            padding: .45em .85em;
            font-size: 0.78em;
        }
    </style>
@endpush

@section('content')
    <div class="row">
        <div class="col-sm-12">
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

            {{-- Card untuk Filter --}}
            <div class="card shadow-sm border-0 rounded-4 mb-4 filter-card">
                <div class="card-header bg-light-subtle py-3 rounded-top-4">
                    <h5 class="mb-0 fw-semibold text-dark d-flex align-items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor"
                            class="bi bi-funnel-fill me-2 text-primary" viewBox="0 0 16 16">
                            <path
                                d="M1.5 1.5A.5.5 0 0 1 2 1h12a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.128.334L10 8.692V13.5a.5.5 0 0 1-.777.416l-2-1.5A.5.5 0 0 1 7 12V8.692L1.628 3.834A.5.5 0 0 1 1.5 3.5v-2z" />
                        </svg>
                        Filter Tagihan & Pembayaran
                    </h5>
                </div>
                <div class="card-body p-3">
                    {{-- Pastikan nama rute ini benar (payments.index atau payments.index) --}}
                    <form action="{{ route('payments.index') }}" method="GET">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label for="search_customer" class="form-label">Cari Pelanggan/Invoice:</label>
                                <input type="text" name="search_customer" id="search_customer"
                                    class="form-control form-control-sm rounded-pill"
                                    value="{{ $request->search_customer ?? old('search_customer') }}"
                                    placeholder="Nama, ID, No. Invoice...">
                            </div>
                            <div class="col-md-3">
                                <label for="status_pembayaran" class="form-label">Status Pembayaran:</label>
                                <select name="status_pembayaran" id="status_pembayaran"
                                    class="form-select form-select-sm rounded-pill">
                                    <option value="">Semua Status</option>
                                    @foreach ($statuses as $value => $label)
                                        <option value="{{ $value }}"
                                            {{ ($request->status_pembayaran ?? old('status_pembayaran')) == $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="bulan_periode" class="form-label">Periode: (YYYY-MM)</label>
                                <input type="month" name="bulan_periode" id="bulan_periode"
                                    class="form-control form-control-sm rounded-pill"
                                    value="{{ $request->bulan_periode ?? old('bulan_periode') }}">
                            </div>
                            <div class="col-md-3 d-flex flex-column">
                                <button type="submit" class="btn btn-primary btn-sm rounded-pill w-100 mb-1">
                                    Filter
                                </button>
                                @if ($request->filled('search_customer') || $request->filled('status_pembayaran') || $request->filled('bulan_periode'))
                                    <a href="{{ route('payments.index') }}"
                                        class="btn btn-outline-secondary btn-sm rounded-pill w-100">Reset</a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-header  p-3 rounded-top-4 d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">{{ 'Daftar Tagihan & Pembayaran' }}</h4>
                    {{-- Pastikan nama rute ini benar --}}
                    <a href="{{ route('payments.create') }}" class="btn btn-primary rounded-pill">
                        Buat Tagihan Baru
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="paymentDataTable">
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>No. Invoice</th>
                                    <th>Pelanggan</th>
                                    <th>Paket</th>
                                    <th>Periode</th>
                                    <th class="text-end">Jumlah</th>
                                    <th class="text-center">Status Bayar</th>
                                    <th>Metode</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($payments as $payment)
                                    {{-- Tambahkan kelas 'shine-pending' jika status 'pending_confirmation' --}}
                                    <tr
                                        class="{{ $payment->status_pembayaran == 'pending_confirmation' ? 'shine-pending' : '' }}">
                                        <td>{{ $loop->iteration + $payments->firstItem() - 1 }}</td>
                                        <td>
                                            {{-- Pastikan nama rute ini benar --}}
                                            <a
                                                href="{{ route('payments.show', $payment->id_payment) }}">{{ $payment->nomor_invoice }}</a>
                                        </td>
                                        <td>
                                            @if ($payment->customer)
                                                {{ $payment->customer->nama_customer }}
                                                <small
                                                    class="d-block text-muted">{{ $payment->customer->id_customer }}</small>
                                            @else
                                                <span class="text-danger small">Pelanggan Dihapus</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($payment->paket)
                                                {{ $payment->paket->kecepatan_paket }}
                                            @else
                                                <span class="text-danger small">Paket Dihapus</span>
                                            @endif
                                        </td>
                                        <td>
                                            {{ \Carbon\Carbon::parse($payment->periode_tagihan_mulai)->locale('id')->translatedFormat('d M Y') }}
                                            -
                                            {{ \Carbon\Carbon::parse($payment->periode_tagihan_selesai)->addDay()->locale('id')->translatedFormat('d M Y') }}
                                            <small class="d-block text-muted">({{ $payment->durasi_pembayaran_bulan }}
                                                bln)</small>
                                        </td>
                                        <td class="text-end">Rp {{ number_format($payment->jumlah_tagihan, 0, ',', '.') }}
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
                                            <span
                                                class="badge rounded-pill px-3 {{ $statusClass }}">{{ $statusText }}</span>
                                        </td>
                                        <td>{{ $payment->metode_pembayaran ? Str::title($payment->metode_pembayaran) : '-' }}
                                        </td>
                                        <td class="text-center">
                                            {{-- Pastikan nama rute ini benar --}}
                                            <a href="{{ route('payments.show', $payment->id_payment) }}"
                                                class="btn btn-sm btn-outline-info rounded-pill py-1 px-2"
                                                title="Detail & Verifikasi">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                                                    fill="currentColor" class="bi bi-eye-fill" viewBox="0 0 16 16">
                                                    <path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0z" />
                                                    <path
                                                        d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8zm8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7z" />
                                                </svg>
                                            </a>
                                            {{-- Tambahkan tombol aksi lain jika perlu, misal edit atau hapus (dengan hati-hati) --}}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-4">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48"
                                                fill="currentColor" class="bi bi-folder-x text-muted mb-2"
                                                viewBox="0 0 16 16">
                                                <path
                                                    d="M.54 3.87.5 3a2 2 0 0 1 2-2h3.672a2 2 0 0 1 1.414.586l.828.828A2 2 0 0 0 9.828 3h3.982a2 2 0 0 1 1.992 2.181L15.546 8H14.54l.265-2.91A1 1 0 0 0 13.81 4H2.19a1 1 0 0 0-.996 1.09l.637 7a1 1 0 0 0 .995.91H9v1H2.826a2 2 0 0 1-1.991-1.819l-.637-7a2 2 0 0 1 .342-1.31zm6.339-1.577A1 1 0 0 0 6.172 2H2.5a1 1 0 0 0-1 .981l.006.139q.323-.119.684-.12h5.396z" />
                                                <path
                                                    d="M11.854 10.146a.5.5 0 0 0-.707.708L12.293 12l-1.146 1.146a.5.5 0 0 0 .707.708L13 12.707l1.146 1.147a.5.5 0 0 0 .708-.708L13.707 12l1.147-1.146a.5.5 0 0 0-.707-.708L13 11.293z" />
                                            </svg>
                                            <p class="mb-0">Data tagihan tidak ditemukan.</p>
                                            @if ($request->filled('search_customer') || $request->filled('status_pembayaran') || $request->filled('bulan_periode'))
                                                <small>Coba ubah atau reset filter Anda.</small>
                                            @endif
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if ($payments->hasPages())
                        <div class="card-footer bg-light-subtle p-3 rounded-bottom-4">
                            {{ $payments->appends(request()->query())->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    {{-- Script SweetAlert untuk konfirmasi hapus (jika Anda punya tombol hapus di sini) --}}
    {{-- Saya asumsikan tombol hapus ada di halaman detail atau edit --}}
    <script>
        // Jika Anda menggunakan DataTables JS dan ingin menonaktifkannya untuk tabel ini
        // karena kita sudah pakai filter server-side + pagination Laravel:
        // document.addEventListener('DOMContentLoaded', function() {
        //     var table = $('#paymentDataTable'); // Gunakan ID yang unik
        //     if (table.length && $.fn.DataTable.isDataTable(table)) {
        //         table.DataTable().destroy(); 
        //     }
        // });
    </script>
@endpush
