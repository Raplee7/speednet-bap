@extends('layouts.app') {{-- Sesuaikan dengan layout admin Anda --}}

@section('title', $pageTitle ?? 'Laporan Data Pelanggan')

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

        .filter-form .form-select,
        .filter-form .form-control,
        .filter-form .btn {
            font-size: 0.85rem;
        }

        .table-responsive {
            overflow-x: auto;
        }

        .card-header h4 {
            margin-bottom: 0;
        }

        .badge.status-badge {
            font-size: 0.75em;
            padding: .4em .7em;
        }

        .filter-form .form-label {
            font-size: 0.8rem;
            margin-bottom: .25rem;
            display: block;
        }

        .summary-section {
            background-color: #f9f9f9;
            padding: 1.5rem;
            border-radius: .5rem;
            margin-bottom: 1.5rem;
            border: 1px solid #e0e0e0;
        }

        .summary-section h5.main-summary-title {
            margin-bottom: 1rem;
            font-weight: 600;
            color: var(--bs-primary);
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #dee2e6;
        }

        .summary-item {
            background-color: #fff;
            padding: 1rem;
            border-radius: .375rem;
            box-shadow: 0 .125rem .25rem rgba(0, 0, 0, .075);
            text-align: center;
            margin-bottom: 1rem;
            height: 100%;
        }

        .summary-item .count {
            font-size: 1.75rem;
            font-weight: 700;
            display: block;
            color: var(--bs-primary);
        }

        .summary-item .label {
            font-size: 0.85rem;
            color: #6c757d;
        }

        .summary-item ul {
            font-size: 0.8rem;
            text-align: left;
            padding-left: 0;
            list-style-position: inside;
        }

        .summary-item ul li {
            margin-bottom: 0.25rem;
        }

        .action-buttons .btn-sm {
            padding: 0.2rem 0.4rem;
            font-size: 0.75rem;
        }

        .custom-date-range-picker {
            display: none;
        }

        .btn-group .btn {
            height: calc(1.5em + .5rem + 2px);
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
    <section class="container-fluid p-4">
        <div class="card shadow-sm border-0 rounded-4">
            {{-- Data Pelanggan --}}
            <div class="card-header p-4 rounded-top-4">
                <div class="row g-3">
                    <!-- Judul Laporan -->
                    <div class="col-lg-12 mb-2">
                        <h4 class="text-muted mb-0">
                            <i class="fa fa-users me-2"></i>
                            {{ $pageTitle }}
                        </h4>
                        {{-- PERBAIKAN: Menggunakan camelCase activation_periodType dan activation_reportPeriodLabel --}}
                        @if (isset($activation_reportPeriodLabel) &&
                                !empty($activation_reportPeriodLabel) &&
                                ($activation_periodType ?? 'all') !== 'all')
                            <p class="text-muted mb-0 small mt-1">
                                Periode Aktivasi: <strong>{{ $activation_reportPeriodLabel }}</strong>
                            </p>
                        @elseif(isset($activation_periodType) && $activation_periodType === 'all')
                            <p class="text-muted mb-0 small mt-1">
                                Periode Aktivasi: <strong>Semua Periode</strong>
                            </p>
                        @else
                            <p class="text-muted mb-0 small mt-1">
                                Silakan pilih filter untuk menampilkan laporan
                            </p>
                        @endif
                    </div>

                    <!-- Form Filter -->
                    <div class="col-lg-12">
                        <form action="{{ route('reports.customer_profile') }}" method="GET" class="filter-form">
                            <div class="row g-2 align-items-end flex-wrap">
                                {{-- Include Partial Filter Periode --}}
                                @include('reports.partials._period_filter', [
                                    'periodPrefix' => 'activation_',
                                    'periodData' => [
                                        'activation_period_type' => $activation_periodType ?? 'all',
                                        'activation_selected_date' =>
                                            $activation_selectedDate ??
                                            old('activation_selected_date', \Carbon\Carbon::now()->toDateString()),
                                        'activation_selected_month_year' =>
                                            $activation_selectedMonthYear ??
                                            old(
                                                'activation_selected_month_year',
                                                \Carbon\Carbon::now()->format('Y-m')),
                                        'activation_selected_year_only' =>
                                            $activation_selectedYearOnly ??
                                            old('activation_selected_year_only', \Carbon\Carbon::now()->year),
                                        'activation_custom_start_date' =>
                                            $activation_customStartDate ?? old('activation_custom_start_date'),
                                        'activation_custom_end_date' =>
                                            $activation_customEndDate ?? old('activation_custom_end_date'),
                                    ],
                                    'availableYears' => $availableYears ?? [],
                                    'allMonthNames' => $allMonthNames ?? [],
                                ])

                                <!-- Status Pelanggan -->
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6">
                                    <div class="form-group mb-0">
                                        <label for="status_pelanggan_profile" class="form-label small mb-1">Status</label>
                                        <select name="status_pelanggan" id="status_pelanggan_profile"
                                            class="form-select form-select-sm rounded-3">
                                            <option value="">Semua Status</option>
                                            @foreach ($statuses as $value => $label)
                                                <option value="{{ $value }}"
                                                    {{ ($request->status_pelanggan ?? '') == $value ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <!-- Paket -->
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6">
                                    <div class="form-group mb-0">
                                        <label for="paket_id_profile" class="form-label small mb-1">Paket</label>
                                        <select name="paket_id" id="paket_id_profile"
                                            class="form-select form-select-sm rounded-3">
                                            <option value="">Semua Paket</option>
                                            @foreach ($pakets as $paket)
                                                <option value="{{ $paket->id_paket }}"
                                                    {{ ($request->paket_id ?? '') == $paket->id_paket ? 'selected' : '' }}>
                                                    {{ $paket->kecepatan_paket }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <!-- Search Query -->
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6">
                                    <div class="form-group mb-0">
                                        <label for="search_query_profile" class="form-label small mb-1">Cari</label>
                                        <input type="text" name="search_query" id="search_query_profile"
                                            class="form-control form-control-sm rounded-3"
                                            value="{{ $request->search_query ?? '' }}" placeholder="Nama, ID, NIK...">
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
                                        <button type="button" id="mainCustomerProfileExportButton"
                                            class="btn btn-success btn-sm dropdown-toggle w-100" data-bs-toggle="dropdown"
                                            aria-expanded="false" disabled>
                                            <i class="fa fa-download me-1"></i> Export
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end w-100">
                                            <li>
                                                <a class="dropdown-item disabled" href="#"
                                                    id="exportCustomerProfilePdfButtonLink" target="_blank">
                                                    <i class="fa fa-file-pdf me-2 text-danger"></i>PDF
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item disabled" href="#"
                                                    id="exportCustomerProfileExcelButtonLink" target="_blank">
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

            {{-- Summary Section Data Pelanggan --}}
            @if (
                $request->hasAny(['search_query', 'status_pelanggan', 'paket_id', 'activation_period_type']) ||
                    $customers->isNotEmpty())
                <div class="card-body pt-3 px-4 pb-0">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h5 class="mb-0 fw-semibold text-primary">
                            <i class="fa fa-chart-bar me-2"></i>
                            Ringkasan Data Pelanggan
                        </h5>
                        @if ($request->hasAny(['search_query', 'status_pelanggan', 'paket_id', 'activation_period_type']))
                            <span class="badge bg-light text-muted">Berdasarkan Filter</span>
                        @elseif($customers->isNotEmpty())
                            <span class="badge bg-light text-muted">Keseluruhan Data</span>
                        @endif
                    </div>

                    <!-- Statistik Utama -->
                    <div class="row g-3 mb-4">
                        <div class="col-lg-3 col-md-4 col-sm-6">
                            <div
                                class="summary-card border rounded-3 p-3 text-center bg-primary bg-opacity-10 border-primary border-opacity-25">
                                <div class="summary-icon mb-2">
                                    <i class="fa fa-users  text-primary"></i>
                                </div>
                                <h4 class="fw-bold text-primary mb-1">{{ number_format($totalCustomersFiltered) }}</h4>
                                <p class="text-muted mb-0 small">Total Pelanggan</p>
                            </div>
                        </div>

                        @foreach ($statuses as $statusKey => $statusLabel)
                            @php
                                $statusData = $summaryByStatus->get($statusKey);
                                $statusColor = match ($statusKey) {
                                    'terpasang' => 'success',
                                    'nonaktif' => 'danger',
                                    'proses' => 'warning',
                                    'baru' => 'info',
                                    'belum' => 'secondary',
                                    default => 'secondary',
                                };
                                $statusIcon = match ($statusKey) {
                                    'terpasang' => 'fa-check-circle',
                                    'nonaktif' => 'fa-times-circle',
                                    'proses' => 'fa-clock',
                                    'baru' => 'fa-plus-circle',
                                    'belum' => 'fa-minus-circle',
                                    default => 'fa-circle',
                                };
                            @endphp
                            <div class="col-lg-3 col-md-4 col-sm-6">
                                <div
                                    class="summary-card border rounded-3 p-3 text-center bg-{{ $statusColor }} bg-opacity-10 border-{{ $statusColor }} border-opacity-25">
                                    <div class="summary-icon mb-2">
                                        <i class="fa {{ $statusIcon }} fa-lg text-{{ $statusColor }}"></i>
                                    </div>
                                    <h5 class="fw-bold text-{{ $statusColor }} mb-1">{{ $statusData['total'] ?? 0 }}</h5>
                                    <p class="text-muted mb-0 small">{{ $statusLabel }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if (isset($summaryByPaket) && $summaryByPaket->isNotEmpty())
                        <!-- Sebaran per Paket -->
                        <div class="mb-4">
                            <h6 class="fw-semibold text-dark mb-3">
                                <i class="fa fa-wifi me-2 text-info"></i>
                                Sebaran Pelanggan per Paket
                            </h6>
                            <div class="row g-2">
                                @foreach ($summaryByPaket as $paketData)
                                    <div class="col-lg-3 col-md-4 col-sm-6">
                                        <div class="paket-card border rounded-3 p-2 bg-light">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div class="flex-grow-1">
                                                    <p class="mb-1 fw-medium text-dark">{{ $paketData->kecepatan_paket }}
                                                    </p>
                                                    <small class="text-muted">Paket Internet</small>
                                                </div>
                                                <div class="text-end">
                                                    <span
                                                        class="badge bg-primary rounded-pill">{{ $paketData->total }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Pertumbuhan Pelanggan --}}
                    @if (isset($customerGrowth) &&
                            (isset($activation_periodType) && $activation_periodType !== 'all' && !empty($activation_reportPeriodLabel)))
                        <div class="mb-4">
                            <h6 class="fw-semibold text-dark mb-3">
                                <i class="fa fa-chart-line me-2 text-success"></i>
                                Pertumbuhan Pelanggan Baru ({{ $activation_reportPeriodLabel }})
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-4 col-sm-6">
                                    <div
                                        class="growth-card border rounded-3 p-3 text-center bg-success bg-opacity-10 border-success border-opacity-25">
                                        <div class="growth-icon mb-2">
                                            <i class="fa fa-user-plus fa-lg text-success"></i>
                                        </div>
                                        <h5 class="fw-bold text-success mb-1">
                                            {{ number_format($customerGrowth['count']) }}</h5>
                                        <p class="text-muted mb-0 small">Pelanggan Baru Periode Ini</p>
                                    </div>
                                </div>
                                <div class="col-md-4 col-sm-6">
                                    <div
                                        class="growth-card border rounded-3 p-3 text-center bg-info bg-opacity-10 border-info border-opacity-25">
                                        <div class="growth-icon mb-2">
                                            <i class="fa fa-history fa-lg text-info"></i>
                                        </div>
                                        <h5 class="fw-bold text-info mb-1">
                                            {{ number_format($customerGrowth['previous_count']) }}</h5>
                                        <p class="text-muted mb-0 small">{{ $customerGrowth['label'] }}</p>
                                    </div>
                                </div>
                                <div class="col-md-4 col-sm-12">
                                    <div
                                        class="growth-card border rounded-3 p-3 text-center 
                            @if ($customerGrowth['percentage'] !== null) {{ $customerGrowth['percentage'] >= 0 ? 'bg-success bg-opacity-10 border-success border-opacity-25' : 'bg-danger bg-opacity-10 border-danger border-opacity-25' }}
                            @else
                                bg-light border-secondary border-opacity-25 @endif">
                                        <div class="growth-icon mb-2">
                                            @if ($customerGrowth['percentage'] !== null)
                                                <i
                                                    class="fa {{ $customerGrowth['percentage'] >= 0 ? 'fa-arrow-up' : 'fa-arrow-down' }} fa-lg 
                                        {{ $customerGrowth['percentage'] >= 0 ? 'text-success' : 'text-danger' }}"></i>
                                            @else
                                                <i class="fa fa-minus fa-lg text-muted"></i>
                                            @endif
                                        </div>
                                        @if ($customerGrowth['percentage'] !== null)
                                            <h5
                                                class="fw-bold mb-1 {{ $customerGrowth['percentage'] >= 0 ? 'text-success' : 'text-danger' }}">
                                                {{ $customerGrowth['percentage'] >= 0 ? '+' : '' }}{{ number_format($customerGrowth['percentage'], 1) }}%
                                            </h5>
                                        @else
                                            <h5 class="fw-bold text-muted mb-1">-</h5>
                                        @endif
                                        <p class="text-muted mb-0 small">Persentase Pertumbuhan</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <hr class="my-4 border-2 border-light">
                </div>
            @endif

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-report mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="small">No</th>
                                <th class="small">ID</th>
                                <th class="small">Nama</th>
                                <th class="small">NIK</th>
                                <th class="small wrap-text">Alamat</th>
                                <th class="small">No. WA</th>
                                <th class="small">KTP</th>
                                <th class="small">Rumah</th>
                                <th class="small">Paket</th>
                                <th class="small">User Aktif</th>
                                <th class="small">Model Perangkat</th>
                                <th class="small">SN Perangkat</th>
                                <th class="small">IP PPPoE</th>
                                <th class="small">IP ONU</th>
                                <th class="small">Tgl Aktivasi</th>
                                <th class="small text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            @forelse ($customers as $index => $customer)
                                <tr>
                                    <td class="py-2">{{ $customers->firstItem() + $index }}</td>
                                    <td class="py-2"><a href="{{ route('customers.show', $customer->id_customer) }}"
                                            title="Lihat Detail Pelanggan">{{ $customer->id_customer }}</a></td>
                                    <td class="py-2 customer-name">{{ $customer->nama_customer }}</td>
                                    <td class="py-2">{{ $customer->nik_customer ?? '-' }}</td>
                                    <td class="py-2 wrap-text">{{ $customer->alamat_customer ?? '-' }}</td>
                                    <td class="py-2">
                                        @if ($customer->wa_customer)
                                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $customer->wa_customer) }}"
                                                target="_blank" class="text-success"><i
                                                    class="fa-brands fa-whatsapp"></i>
                                                {{ $customer->wa_customer }}</a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="py-2">
                                        @if ($customer->foto_ktp_customer)
                                            <a href="{{ asset('storage/' . $customer->foto_ktp_customer) }}"
                                                target="_blank" class="btn btn-sm btn-outline-secondary p-1"
                                                style="font-size: 0.7rem;" title="Lihat KTP">
                                                <i class="fa fa-id-card"></i>
                                            </a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="py-2">
                                        @if ($customer->foto_timestamp_rumah)
                                            <a href="{{ asset('storage/' . $customer->foto_timestamp_rumah) }}"
                                                target="_blank" class="btn btn-sm btn-outline-secondary p-1"
                                                style="font-size: 0.7rem;" title="Lihat Foto Rumah">
                                                <i class="fa fa-home"></i>
                                            </a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="py-2">{{ $customer->paket->kecepatan_paket ?? '-' }}
                                        <small class="text-muted d-block" style="font-size: 0.7rem;">
                                            Rp{{ number_format($customer->paket->harga_paket ?? 0, 0, ',', '.') }}
                                        </small>
                                    </td>
                                    <td class="py-2">{{ $customer->active_user ?? '-' }}</td>
                                    <td class="py-2">{{ $customer->deviceSn->deviceModel->nama_model ?? '-' }}</td>
                                    <td class="py-2">{{ $customer->deviceSn->nomor ?? '-' }}</td>
                                    <td class="py-2">{{ $customer->ip_ppoe ?? '-' }}</td>
                                    <td class="py-2">{{ $customer->ip_onu ?? '-' }}</td>
                                    <td class="py-2">
                                        {{ $customer->tanggal_aktivasi ? \Carbon\Carbon::parse($customer->tanggal_aktivasi)->locale('id')->translatedFormat('d M Y') : '-' }}
                                    </td>
                                    <td class="py-2 text-center">
                                        @php
                                            $badgeClass = match ($customer->status) {
                                                'baru' => 'bg-info',
                                                'belum' => 'bg-secondary',
                                                'proses' => 'bg-warning',
                                                'terpasang' => 'bg-success',
                                                'nonaktif' => 'bg-danger',
                                                default => 'bg-light',
                                            };
                                        @endphp
                                        <span class="badge status-badge rounded-pill {{ $badgeClass }}"
                                            style="font-size: 0.7rem;">
                                            {{ Str::title(str_replace('_', ' ', $customer->status)) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="16" class="text-center py-4">
                                        <p class="mb-0 text-muted" style="font-size: 0.9rem;">
                                            <i class="fa fa-users fa-2x mb-2 d-block"></i>
                                            Data pelanggan tidak ditemukan.
                                        </p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($customers->hasPages())
                    <div class="card-footer bg-light-subtle p-2">
                        {{ $customers->appends($request->query())->links() }}
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    {{-- Script untuk partial _period_filter.blade.php akan otomatis ter-include jika Anda menggunakannya --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll(
                '[data-bs-toggle="tooltip"], .info-tooltip'));
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ... (Inisialisasi Tooltip Anda tetap di sini) ...
            var tooltipTriggerList = [].slice.call(document.querySelectorAll(
                '[data-bs-toggle="tooltip"], .info-tooltip'));
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            const exportPdfButtonLink = document.getElementById('exportCustomerProfilePdfButtonLink');
            const exportExcelButtonLink = document.getElementById('exportCustomerProfileExcelButtonLink');
            const mainExportButton = document.getElementById('mainCustomerProfileExportButton');

            // Kumpulkan semua elemen input filter yang relevan
            const filterInputs = [
                document.getElementById('search_query_profile'),
                document.getElementById('status_pelanggan_profile'),
                document.getElementById('paket_id_profile'),
                document.getElementById('activation_period_type'),
                document.getElementById('activation_selected_date'),
                document.getElementById('activation_selected_month_year'),
                document.getElementById('activation_selected_year_only'),
                document.getElementById('activation_custom_start_date'),
                document.getElementById('activation_custom_end_date')
            ];

            function updateCustomerProfileExportLinksState() {
                const params = new URLSearchParams();
                let isAnyFilterActive = false; // Flag untuk menandakan ada filter yang diterapkan pengguna

                filterInputs.forEach(input => {
                    if (input && input.name && input.value && input.value.trim() !== "") {
                        // Untuk period_type, hanya anggap filter aktif jika BUKAN 'all' ATAU jika 'all' tapi ada tanggal spesifik yang relevan diisi
                        if (input.name.endsWith('period_type')) {
                            params.append(input.name, input
                                .value); // Selalu tambahkan period_type ke params
                            if (input.value !== 'all') { // Jika bukan 'Semua Periode', anggap filter aktif
                                isAnyFilterActive = true;
                            } else {
                                // Jika 'all', kita tidak set isAnyFilterActive jadi true hanya karena ini,
                                // kecuali jika memang ingin 'all' dianggap filter aktif untuk export.
                                // Tapi biasanya 'all' berarti "tampilkan semua", jadi tidak ada "filter" spesifik.
                                // Namun, jika ada data, tombol tetap bisa aktif.
                            }
                        } else if (input.name.endsWith('_selected_date') ||
                            input.name.endsWith('_selected_month_year') ||
                            input.name.endsWith('_selected_year_only') ||
                            input.name.endsWith('_custom_start_date') ||
                            input.name.endsWith('_custom_end_date')) {
                            // Cek apakah input tanggal ini relevan dengan period_type yang dipilih
                            const prefix = input.name.substring(0, input.name.indexOf('_') + 1);
                            const periodTypeInput = document.getElementById(prefix + 'period_type');
                            if (periodTypeInput && periodTypeInput.value !== 'all') {
                                params.append(input.name, input.value);
                                isAnyFilterActive = true;
                            } else if (periodTypeInput && periodTypeInput.value === 'all') {
                                // Jika period_type adalah 'all', parameter tanggal spesifik ini tidak dikirim
                            } else if (!periodTypeInput) { // Jika tidak ada period_type select (fallback)
                                params.append(input.name, input.value);
                                isAnyFilterActive = true;
                            }
                        } else if (input.name !== 'page') { // Filter standar lainnya
                            params.append(input.name, input.value);
                            isAnyFilterActive = true;
                        }
                    }
                });

                // Ambil status data dari PHP
                const hasData = {{ isset($customers) && $customers->total() > 0 ? 'true' : 'false' }};

                // Tombol export aktif jika ada data ATAU jika ada filter aktif yang diterapkan pengguna
                const enableExport = hasData || isAnyFilterActive;

                if (mainExportButton) {
                    if (enableExport) {
                        mainExportButton.classList.remove('disabled');
                        mainExportButton.removeAttribute('disabled'); // Pastikan atribut juga dihapus
                    } else {
                        mainExportButton.classList.add('disabled');
                        mainExportButton.setAttribute('disabled', 'disabled'); // Tambah atribut juga
                    }
                }

                const queryString = params.toString() ? '?' + params.toString() : '';

                // Update link PDF
                if (exportPdfButtonLink) {
                    if (enableExport) {
                        exportPdfButtonLink.href = "{{ route('reports.customer_profile.pdf') }}" + queryString;
                        exportPdfButtonLink.classList.remove('disabled');
                    } else {
                        exportPdfButtonLink.href = '#';
                        exportPdfButtonLink.classList.add('disabled');
                    }
                }
                // Update link Excel
                if (exportExcelButtonLink) {
                    if (enableExport) {
                        exportExcelButtonLink.href = "{{ route('reports.customer_profile.excel') }}" + queryString;
                        exportExcelButtonLink.classList.remove('disabled');
                    } else {
                        exportExcelButtonLink.href = '#';
                        exportExcelButtonLink.classList.add('disabled');
                    }
                }
            }

            // Panggil saat halaman dimuat dan setiap kali filter berubah
            updateCustomerProfileExportLinksState();
            filterInputs.forEach(input => {
                if (input) {
                    const eventType = (input.tagName.toLowerCase() === 'select' || input.type === 'date' ||
                        input.type === 'month' || input.type === 'number') ? 'change' : 'input';
                    input.addEventListener(eventType, updateCustomerProfileExportLinksState);
                }
            });

            // Event listener untuk mencegah aksi default jika item dropdown disabled
            if (exportPdfButtonLink) {
                exportPdfButtonLink.addEventListener('click', function(event) {
                    if (mainExportButton && mainExportButton.classList.contains(
                            'disabled')) { // Cek tombol utama
                        event.preventDefault();
                        Swal.fire('Info',
                            'Tidak ada data untuk diexport atau filter belum diterapkan dengan benar.',
                            'info');
                    }
                });
            }
            if (exportExcelButtonLink) {
                exportExcelButtonLink.addEventListener('click', function(event) {
                    if (mainExportButton && mainExportButton.classList.contains(
                            'disabled')) { // Cek tombol utama
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
