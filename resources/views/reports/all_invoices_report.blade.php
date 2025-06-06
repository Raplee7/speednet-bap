@extends('layouts.app')
@push('styles')
    <style>
        .table-report {
            border-collapse: separate;
            border-spacing: 0;
        }

        .table-report th,
        .table-report td {
            font-size: 0.82rem;
            padding: 0.75rem 1rem;
            vertical-align: middle;
            border: 1px solid #e5e7eb;
        }

        .table-report thead th {
            background: linear-gradient(180deg, #f8f9fa 0%, #e9ecef 100%);
            color: #2d3748;
            font-weight: 600;
            text-align: center;
            position: sticky;
            top: 0;
            z-index: 2;
            border-bottom: 2px solid #dee2e6;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .table-report tbody tr:hover {
            background-color: rgba(249, 250, 251, 0.8);
            transition: all 0.2s ease;
        }

        .table-report tbody td {
            transition: all 0.2s ease;
        }

        .table-report tbody td.customer-name {
            min-width: 200px;
            white-space: normal;
            font-weight: 500;
        }

        .table-report tbody td.customer-id {
            min-width: 110px;
            font-family: 'Consolas', monospace;
            color: #666;
        }

        .table-report tbody td.customer-info {
            min-width: 130px;
            white-space: nowrap;
        }

        .table-report .month-col {
            min-width: 120px;
            text-align: center;
            background-color: rgba(255, 255, 255, 0.8);
        }

        .badge.status-badge {
            font-size: 0.72rem;
            padding: 0.4em 0.8em;
            font-weight: 500;
            letter-spacing: 0.3px;
            border-radius: 4px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }

        /* Status Colors */
        .status-paid {
            background: linear-gradient(45deg, #28a745, #34ce57);
        }

        .status-unpaid {
            background: linear-gradient(45deg, #ffc107, #ffdb4a);
        }

        .status-pending_confirmation {
            background: linear-gradient(45deg, #17a2b8, #1fc8e3);
        }

        .status-failed {
            background: linear-gradient(45deg, #dc3545, #f05565);
        }

        .status-cancelled {
            background: linear-gradient(45deg, #6c757d, #868e96);
        }

        .status-menunggak {
            background: linear-gradient(45deg, #dc3545, #c82333);
            font-weight: 600;
        }

        .filter-form .form-select,
        .filter-form .form-control,
        .filter-form .btn {
            font-size: 0.875rem;
        }

        .summary-box {
            border: 1px solid #e0e0e0;
            padding: 1rem;
            border-radius: .375rem;
            /* rounded-3 */
            margin-bottom: 0.5rem;
            background-color: #f9f9f9;
            height: 100%;
        }

        .summary-box h6 {
            font-size: 0.85rem;
            /* Ukuran font judul summary dikecilkan sedikit */
            color: #555;
            margin-bottom: 0.25rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .summary-box p {
            font-size: 1.1rem;
            /* Ukuran angka summary sedikit dikecilkan */
            font-weight: bold;
            margin-bottom: 0;
        }

        .summary-box p small.sub-text {
            font-size: 0.75rem;
            font-weight: normal;
            color: #6c757d;
        }

        .badge.status-badge {
            font-size: 0.75em;
            padding: .4em .7em;
        }

        .card-header h4 {
            margin-bottom: 0;
        }

        .btn-group .btn {
            height: calc(1.5em + .5rem + 2px);
            /* Menyamakan tinggi dengan form-select-sm */
        }

        .info-tooltip {
            cursor: help;
        }

        .table-responsive {
            overflow-x: auto;
        }

        .card-body.p-0 .table-responsive {
            /* Menghilangkan border atas tabel jika card-body p-0 */
            border-top: none;
        }
    </style>
    <style>
        .summary-card,
        .growth-card,
        .status-card,
        .paket-card {
            transition: all 0.3s ease;
            border: 1px solid !important;
        }

        .summary-card:hover,
        .growth-card:hover,
        .status-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .paket-card:hover {
            background-color: #f8f9fa !important;
        }

        .summary-icon,
        .growth-icon,
        .status-icon {
            opacity: 0.8;
        }

        .small-text {
            font-size: 1.1rem !important;
        }

        @media (max-width: 768px) {
            .small-text {
                font-size: 0.9rem !important;
            }

            .summary-card,
            .growth-card,
            .status-card {
                margin-bottom: 1rem;
            }
        }
    </style>
@endpush

@section('content')
    <div class="card shadow-sm border-0 rounded-4">
        {{-- Semua Tagihan --}}
        <div class="card-header p-4 rounded-top-4">
            <div class="row g-3">
                <!-- Judul Laporan -->
                <div class="col-lg-12 mb-2">
                    <h4 class="text-muted mb-0">
                        <i class="fa fa-file-text me-2"></i>
                        {{ $pageTitle ?? 'Laporan Semua Tagihan' }}
                    </h4>
                    {{-- Menampilkan keterangan filter periode pembuatan invoice --}}
                    @if (isset($creation_reportPeriodLabel) &&
                            !empty($creation_reportPeriodLabel) &&
                            ($creation_periodType ?? 'all') !== 'all')
                        <p class="text-muted mb-0 small mt-1">
                            Tgl. Pembuatan: <strong>{{ $creation_reportPeriodLabel }}</strong>
                        </p>
                    @elseif(isset($creation_periodType) && $creation_periodType === 'all')
                        <p class="text-muted mb-0 small mt-1">
                            Tgl. Pembuatan: <strong>Semua Periode</strong>
                        </p>
                    @else
                        <p class="text-muted mb-0 small mt-1">
                            Silakan pilih filter untuk menampilkan laporan
                        </p>
                    @endif
                </div>

                <!-- Form Filter -->
                <div class="col-lg-12">
                    <form action="{{ route('reports.invoices.all') }}" method="GET" class="filter-form">
                        <div class="row g-2 align-items-end flex-wrap">
                            {{-- Include Partial Filter Periode untuk Tanggal Pembuatan Invoice --}}
                            @include('reports.partials._period_filter', [
                                'periodPrefix' => 'creation_',
                                'periodData' => [
                                    'creation_period_type' => $creation_periodType ?? 'all',
                                    'creation_selected_date' =>
                                        $creation_selected_date ??
                                        old('creation_selected_date', \Carbon\Carbon::now()->toDateString()),
                                    'creation_selected_month_year' =>
                                        $creation_selectedMonthYear ??
                                        old('creation_selected_month_year', \Carbon\Carbon::now()->format('Y-m')),
                                    'creation_selected_year_only' =>
                                        $creation_selectedYearOnly ??
                                        old('creation_selected_year_only', \Carbon\Carbon::now()->year),
                                    'creation_custom_start_date' =>
                                        $creation_custom_start_date ?? old('creation_custom_start_date'),
                                    'creation_custom_end_date' =>
                                        $creation_custom_end_date ?? old('creation_custom_end_date'),
                                ],
                                'availableYears' => $availableYears ?? [],
                                'allMonthNames' => $allMonthNames ?? [],
                            ])

                            <!-- Filter Status Pembayaran -->
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6">
                                <div class="form-group mb-0">
                                    <label for="status_pembayaran_all_inv" class="form-label small mb-1">Status
                                        Bayar</label>
                                    <select name="status_pembayaran" id="status_pembayaran_all_inv"
                                        class="form-select form-select-sm rounded-3">
                                        <option value="">Semua Status</option>
                                        @foreach ($paymentStatuses as $value => $label)
                                            <option value="{{ $value }}"
                                                {{ $request->status_pembayaran == $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- Filter Pelanggan -->
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6">
                                <div class="form-group mb-0">
                                    <label for="customer_id_all_inv" class="form-label small mb-1">Pelanggan</label>
                                    <select name="customer_id" id="customer_id_all_inv"
                                        class="form-select form-select-sm rounded-3">
                                        <option value="">Semua Pelanggan</option>
                                        @foreach ($customers as $customer)
                                            <option value="{{ $customer->id_customer }}"
                                                {{ $request->customer_id == $customer->id_customer ? 'selected' : '' }}>
                                                {{ Str::limit($customer->nama_customer, 20) }}
                                                ({{ $customer->id_customer }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- Search Query -->
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6">
                                <div class="form-group mb-0">
                                    <label for="search_query_all_inv" class="form-label small mb-1">Cari
                                        Invoice/Pelanggan</label>
                                    <input type="text" name="search_query" id="search_query_all_inv"
                                        class="form-control form-control-sm rounded-3"
                                        value="{{ $request->search_query ?? '' }}" placeholder="No. Inv/Nama/ID Pel...">
                                </div>
                            </div>

                            <!-- Tombol Aksi -->
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6 d-flex align-items-end gap-2">
                                <button type="submit"
                                    class="btn btn-primary btn-sm flex-grow-1 d-flex align-items-center justify-content-center">
                                    <i class="fa fa-filter me-1"></i>
                                    <span>Filter</span>
                                </button>
                                <div class="dropdown flex-grow-1">
                                    <button type="button" id="mainAllInvoicesExportButton"
                                        class="btn btn-success btn-sm dropdown-toggle w-100" data-bs-toggle="dropdown"
                                        aria-expanded="false"
                                        {{ $payments->isEmpty() && !$request->hasAny(array_keys(array_filter($request->except('page')))) ? 'disabled' : '' }}>
                                        <i class="fa fa-download me-1"></i> Export
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end w-100">
                                        <li>
                                            <a class="dropdown-item {{ $payments->isEmpty() && !$request->hasAny(array_keys(array_filter($request->except('page')))) ? 'disabled' : '' }}"
                                                href="#" id="exportAllInvoicesPdfButtonLink" target="_blank">
                                                <i class="fa fa-file-pdf me-2 text-danger"></i>PDF
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item {{ $payments->isEmpty() && !$request->hasAny(array_keys(array_filter($request->except('page')))) ? 'disabled' : '' }}"
                                                href="#" id="exportAllInvoicesExcelButtonLink" target="_blank">
                                                <i class="fa fa-file-excel me-2 text-success"></i>Excel
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Summary Section Semua Tagihan/Pendapatan --}}
        @if (
            $request->hasAny(['start_date', 'end_date', 'status_pembayaran', 'customer_id', 'paket_id', 'search_query']) ||
                $payments->isNotEmpty())
            <div class="card-body pt-3 px-4 pb-0">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="mb-0 fw-semibold text-primary">
                        <i class="fa fa-chart-pie me-2"></i>
                        Ringkasan Laporan Tagihan
                    </h5>
                    @if ($request->hasAny(['start_date', 'end_date', 'status_pembayaran', 'customer_id', 'paket_id', 'search_query']))
                        <span class="badge bg-light text-muted">Berdasarkan Filter</span>
                    @else
                        <span class="badge bg-light text-muted">Keseluruhan Data</span>
                    @endif
                </div>

                <!-- Statistik Utama -->
                <div class="row g-3 mb-4">
                    <!-- Total Invoice -->
                    <div class="col-lg-3 col-md-6 col-sm-6">
                        <div
                            class="summary-card border rounded-3 p-3 text-center bg-primary bg-opacity-10 border-primary border-opacity-25">
                            <div class="summary-icon mb-2">
                                <i class="fa fa-file-text fa-2x text-primary"></i>
                            </div>
                            <h4 class="fw-bold text-primary mb-1">{{ number_format($totalInvoices) }}</h4>
                            <p class="text-muted mb-0 small">Total Invoice</p>
                        </div>
                    </div>

                    <!-- Total Nilai Tagihan -->
                    <div class="col-lg-3 col-md-6 col-sm-6">
                        <div
                            class="summary-card border rounded-3 p-3 text-center bg-info bg-opacity-10 border-info border-opacity-25">
                            <div class="summary-icon mb-2">
                                <i class="fa fa-money fa-2x text-info"></i>
                            </div>
                            <h4 class="fw-bold text-info mb-1 small-text">Rp
                                {{ number_format($totalAmountAll, 0, ',', '.') }}</h4>
                            <p class="text-muted mb-0 small d-flex align-items-center justify-content-center">
                                Nilai Tagihan Dibuat
                                <i class="fa fa-info-circle ms-1 text-muted" data-bs-toggle="tooltip"
                                    data-bs-placement="top"
                                    title="Total nilai semua tagihan yang cocok dengan filter (termasuk semua status)."></i>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Statistik per Status -->
                <div class="mb-4">
                    <h6 class="fw-semibold text-dark mb-3">
                        <i class="fa fa-tasks me-2 text-secondary"></i>
                        Detail Status Pembayaran
                    </h6>
                    <div class="row g-3">
                        @php
                            $statusOrder = ['paid', 'unpaid', 'pending_confirmation', 'cancelled', 'failed'];
                            $statusConfig = [
                                'paid' => ['color' => 'success', 'icon' => 'fa-check-circle', 'label' => 'Lunas'],
                                'unpaid' => ['color' => 'warning', 'icon' => 'fa-clock', 'label' => 'Belum Bayar'],
                                'pending_confirmation' => [
                                    'color' => 'info',
                                    'icon' => 'fa-hourglass-half',
                                    'label' => 'Menunggu Konfirmasi',
                                ],
                                'cancelled' => [
                                    'color' => 'secondary',
                                    'icon' => 'fa-times-circle',
                                    'label' => 'Dibatalkan',
                                ],
                                'failed' => [
                                    'color' => 'danger',
                                    'icon' => 'fa-exclamation-circle',
                                    'label' => 'Gagal',
                                ],
                            ];
                        @endphp

                        @foreach ($statusOrder as $statusKey)
                            @if (isset($summaryByStatus[$statusKey]))
                                @php
                                    $statusData = $summaryByStatus[$statusKey];
                                    $config = $statusConfig[$statusKey];
                                @endphp
                                <div class="col-lg-4 col-md-6 col-sm-6">
                                    <div
                                        class="status-card border rounded-3 p-3 bg-{{ $config['color'] }} bg-opacity-10 border-{{ $config['color'] }} border-opacity-25">
                                        <div class="d-flex align-items-center">
                                            <div class="status-icon me-3">
                                                <i class="fa {{ $config['icon'] }} fa-2x text-{{ $config['color'] }}"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="fw-semibold text-{{ $config['color'] }} mb-1">
                                                    {{ $statusData->label }}</h6>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span
                                                        class="fw-bold text-{{ $config['color'] }}">{{ number_format($statusData->count) }}</span>
                                                    <small class="text-muted">Invoice</small>
                                                </div>
                                                <div class="mt-1">
                                                    <small class="text-muted d-block">
                                                        <i class="fa fa-money me-1"></i>
                                                        Rp {{ number_format($statusData->total_amount, 0, ',', '.') }}
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>

                <hr class="my-4 border-2 border-light">
            </div>
        @endif

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-report mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>No. Invoice</th>
                            <th>Tgl Buat</th>
                            <th>Pelanggan</th>
                            <th>Paket</th>
                            <th>Periode</th>
                            <th class="text-end">Jumlah</th>
                            <th class="text-center">Status</th>
                            <th>Tgl Bayar</th>
                            <th>Metode</th>
                            {{-- <th>Dibuat Oleh</th> --}}
                            {{-- <th>Dikonfirmasi Oleh</th> --}}
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($payments as $index => $payment)
                            <tr>
                                <td>{{ $payments->firstItem() + $index }}</td>
                                <td>
                                    <a href="{{ route('payments.show', $payment->id_payment) }}"
                                        title="Lihat Detail Tagihan">{{ $payment->nomor_invoice }}</a>
                                </td>
                                <td>{{ $payment->created_at->locale('id')->translatedFormat('d M Y, H:i') }}</td>
                                <td>
                                    @if ($payment->customer)
                                        {{ Str::limit($payment->customer->nama_customer, 25) }}
                                        <small class="d-block text-muted">{{ $payment->customer->id_customer }}</small>
                                    @else
                                        <span class="text-danger small">- Pelanggan Dihapus -</span>
                                    @endif
                                </td>
                                <td>{{ $payment->paket->kecepatan_paket ?? '-' }}</td>
                                <td>
                                    {{ \Carbon\Carbon::parse($payment->periode_tagihan_mulai)->locale('id')->translatedFormat('d M Y') }}
                                    <small class="d-block text-muted">s/d
                                        {{ \Carbon\Carbon::parse($payment->periode_tagihan_selesai)->addDay()->locale('id')->translatedFormat('d M Y') }}</small>
                                </td>
                                <td class="text-end">Rp {{ number_format($payment->jumlah_tagihan, 0, ',', '.') }}
                                </td>
                                <td class="text-center">
                                    @php
                                        $statusClass = '';
                                        $statusText = Str::title(str_replace('_', ' ', $payment->status_pembayaran));
                                        switch ($payment->status_pembayaran) {
                                            case 'unpaid':
                                                $statusClass = 'status-unpaid';
                                                $statusText = 'Belum Bayar';
                                                break;
                                            case 'pending_confirmation':
                                                $statusClass = 'status-pending_confirmation';
                                                $statusText = 'Pending';
                                                break;
                                            case 'paid':
                                                $statusClass = 'status-paid';
                                                $statusText = 'Lunas';
                                                break;
                                            case 'failed':
                                                $statusClass = 'status-failed';
                                                $statusText = 'Gagal';
                                                break;
                                            case 'cancelled':
                                                $statusClass = 'status-cancelled';
                                                $statusText = 'Dibatalkan';
                                                break;
                                        }
                                    @endphp
                                    <span class="badge status-badge {{ $statusClass }}">{{ $statusText }}</span>
                                </td>
                                <td>{{ $payment->tanggal_pembayaran ? \Carbon\Carbon::parse($payment->tanggal_pembayaran)->locale('id')->translatedFormat('d M Y') : '-' }}
                                </td>
                                <td>{{ $payment->metode_pembayaran ? Str::title($payment->metode_pembayaran) : '-' }}
                                </td>
                                {{-- <td>{{ $payment->pembuatTagihan->nama_user ?? 'Sistem' }}</td> --}}
                                {{-- <td>{{ $payment->pengonfirmasiPembayaran->nama_user ?? '-' }}</td> --}}
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-5">
                                    <p class="mb-0 text-muted fs-5"><i
                                            class="fa fa-folder-open-o fa-3x mb-3 d-block"></i>Tidak ada data tagihan
                                        yang cocok dengan filter Anda.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($payments->hasPages())
                <div class="card-footer bg-light-subtle p-3">
                    {{ $payments->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Inisialisasi Tooltip Bootstrap
            var tooltipTriggerList = [].slice.call(document.querySelectorAll(
                '[data-bs-toggle="tooltip"], .info-tooltip'));
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            const exportPdfButtonLink = document.getElementById('exportAllInvoicesPdfButtonLink');
            const exportExcelButtonLink = document.getElementById('exportAllInvoicesExcelButtonLink');
            const mainExportButton = document.getElementById('mainAllInvoicesExportButton');

            const filterInputs = [
                document.getElementById('start_date_all_inv'),
                document.getElementById('end_date_all_inv'),
                document.getElementById('status_pembayaran_all_inv'),
                document.getElementById('customer_id_all_inv'),
                document.querySelector('input[name="search_query"]') // Tambahkan input search_query jika ada
            ];

            function updateAllInvoicesExportLinksState() {
                const params = new URLSearchParams();
                let hasActiveFilterValue = false;

                filterInputs.forEach(input => {
                    if (input && input.value && input.value.trim() !== "") { // Cek jika value tidak kosong
                        params.append(input.name, input.value);
                        hasActiveFilterValue = true;
                    }
                });

                const hasData = {{ $payments->total() > 0 ? 'true' : 'false' }};
                // Tombol export aktif jika ada data ATAU ada filter yang diterapkan dengan nilai
                const enableExport = hasData || hasActiveFilterValue;


                if (mainExportButton) {
                    if (enableExport) mainExportButton.classList.remove('disabled');
                    else mainExportButton.classList.add('disabled');
                }

                const queryString = params.toString() ? '?' + params.toString() : '';

                if (exportPdfButtonLink) {
                    if (enableExport) {
                        exportPdfButtonLink.href = "{{ route('reports.invoices.all.pdf') }}" + queryString;
                        exportPdfButtonLink.classList.remove('disabled');
                    } else {
                        exportPdfButtonLink.href = '#';
                        exportPdfButtonLink.classList.add('disabled');
                    }
                }
                if (exportExcelButtonLink) {
                    if (enableExport) {
                        exportExcelButtonLink.href = "{{ route('reports.invoices.all.excel') }}" + queryString;
                        exportExcelButtonLink.classList.remove('disabled');
                    } else {
                        exportExcelButtonLink.href = '#';
                        exportExcelButtonLink.classList.add('disabled');
                    }
                }
            }

            updateAllInvoicesExportLinksState();
            filterInputs.forEach(input => {
                if (input) {
                    // Gunakan 'input' untuk text, 'change' untuk select/date
                    const eventType = (input.type === 'text' || input.type === 'search') ? 'input' :
                        'change';
                    input.addEventListener(eventType, updateAllInvoicesExportLinksState);
                }
            });

            if (exportPdfButtonLink) {
                exportPdfButtonLink.addEventListener('click', function(event) {
                    if (this.classList.contains('disabled')) {
                        event.preventDefault();
                        Swal.fire('Info',
                            'Tidak ada data untuk diexport atau filter belum diterapkan dengan benar.',
                            'info');
                    }
                });
            }
            if (exportExcelButtonLink) {
                exportExcelButtonLink.addEventListener('click', function(event) {
                    if (this.classList.contains('disabled')) {
                        event.preventDefault();
                        Swal.fire('Info',
                            'Tidak ada data untuk diexport atau filter belum diterapkan dengan benar.',
                            'info');
                    }
                });
            }
        });
    </script>
@endpush
