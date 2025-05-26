@extends('layouts.app') {{-- Sesuaikan dengan layout admin Anda --}}

@section('title', $pageTitle ?? 'Laporan Data Pelanggan')

@push('styles')
    <style>
        .table-report th,
        .table-report td {
            font-size: 0.78rem;
            padding: 0.4rem;
            vertical-align: middle;
            white-space: nowrap;
        }

        .table-report thead th {
            text-align: center;
            background-color: #f8f9fa;
            position: sticky;
            top: 0;
            z-index: 1;
        }

        .table-report td.wrap-text {
            white-space: normal;
            min-width: 180px;
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
@endpush

@section('content')
    <section class="container-fluid p-4">
        <div class="card shadow-sm border-0 rounded-4">
            {{-- Data Pelanggan --}}
            <div class="card-header bg-light-subtle p-3 rounded-top-4">
                <form action="{{ route('reports.customer_profile') }}" method="GET" class="filter-form">
                    <div class="row align-items-center mb-3">
                        <div class="col-lg-12">
                            <h4 class="card-title mb-1 fw-semibold">{{ $pageTitle }}</h4>
                            {{-- PERBAIKAN: Menggunakan camelCase activation_periodType dan activation_reportPeriodLabel --}}
                            @if (isset($activation_reportPeriodLabel) &&
                                    !empty($activation_reportPeriodLabel) &&
                                    ($activation_periodType ?? 'all') !== 'all')
                                <p class="text-muted mb-0 small">Periode Aktivasi:
                                    <strong>{{ $activation_reportPeriodLabel }}</strong>
                                </p>
                            @elseif(isset($activation_periodType) && $activation_periodType === 'all')
                                <p class="text-muted mb-0 small">Periode Aktivasi: <strong>Semua Periode</strong></p>
                            @else
                                <p class="text-muted mb-0 small">Filter periode aktivasi tidak aktif.</p>
                            @endif
                        </div>
                    </div>
                    <div class="row gx-2 gy-2 align-items-end">
                        <div class="col-lg-2 col-md-6 col-sm-6">
                            <label for="search_query_profile" class="form-label">Cari:</label>
                            <input type="text" name="search_query" id="search_query_profile"
                                class="form-control form-control-sm rounded-pill" value="{{ $request->search_query ?? '' }}"
                                placeholder="Nama, ID, NIK...">
                        </div>
                        <div class="col-lg-2 col-md-6 col-sm-6">
                            <label for="status_pelanggan_profile" class="form-label">Status:</label>
                            <select name="status_pelanggan" id="status_pelanggan_profile"
                                class="form-select form-select-sm rounded-pill">
                                <option value="">Semua Status</option>
                                @foreach ($statuses as $value => $label)
                                    <option value="{{ $value }}"
                                        {{ ($request->status_pelanggan ?? '') == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-6 col-sm-6">
                            <label for="paket_id_profile" class="form-label">Paket:</label>
                            <select name="paket_id" id="paket_id_profile" class="form-select form-select-sm rounded-pill">
                                <option value="">Semua Paket</option>
                                @foreach ($pakets as $paket)
                                    <option value="{{ $paket->id_paket }}"
                                        {{ ($request->paket_id ?? '') == $paket->id_paket ? 'selected' : '' }}>
                                        {{ $paket->kecepatan_paket }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        @include('reports.partials._period_filter', [
                            'periodPrefix' => 'activation_',
                            'periodData' => [
                                'activation_period_type' => $activation_periodType ?? 'all', // PERBAIKAN: camelCase
                                'activation_selected_date' =>
                                    $activation_selectedDate ??
                                    old('activation_selected_date', \Carbon\Carbon::now()->toDateString()),
                                'activation_selected_month_year' =>
                                    $activation_selectedMonthYear ??
                                    old('activation_selected_month_year', \Carbon\Carbon::now()->format('Y-m')),
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

                        <div
                            class="col-lg-auto col-md-12 d-flex gap-2 mt-md-3 mt-lg-0 justify-content-start justify-content-lg-end">
                            <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3">
                                <i class="fa fa-filter me-1"></i> Filter
                            </button>
                            <a href="{{ route('reports.customer_profile') }}"
                                class="btn btn-secondary btn-sm rounded-pill px-3" title="Reset Filter">
                                <i class="fa fa-refresh"></i> Reset
                            </a>
                            <div class="btn-group">
                                <button type="button" id="mainCustomerProfileExportButton"
                                    class="btn btn-success btn-sm rounded-pill dropdown-toggle" data-bs-toggle="dropdown"
                                    aria-expanded="false" {{-- Kondisi disabled akan diatur oleh JavaScript --}} disabled>
                                    <i class="fa fa-download me-1"></i> Export
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item disabled" {{-- Awalnya disabled --}} href="#"
                                            id="exportCustomerProfilePdfButtonLink" target="_blank">
                                            <i class="fa fa-file-pdf-o me-2 text-danger"></i>Ke PDF
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item disabled" {{-- Awalnya disabled --}} href="#"
                                            id="exportCustomerProfileExcelButtonLink" target="_blank">
                                            <i class="fa fa-file-excel-o me-2 text-success"></i>Ke Excel
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            {{-- Summary Section --}}
            @if (
                $request->hasAny(['search_query', 'status_pelanggan', 'paket_id', 'activation_period_type']) ||
                    $customers->isNotEmpty())
                <div class="summary-section mx-3 mt-3">
                    <h5 class="main-summary-title">Ringkasan Data Pelanggan
                        @if ($request->hasAny(['search_query', 'status_pelanggan', 'paket_id', 'activation_period_type']))
                            <small class="text-muted fs-sm">(berdasarkan filter)</small>
                        @elseif($customers->isNotEmpty())
                            <small class="text-muted fs-sm">(keseluruhan data yang ditampilkan)</small>
                        @endif
                    </h5>
                    <div class="row g-3">
                        <div class="col-md-3 col-sm-6">
                            <div class="summary-item"><span class="count">{{ $totalCustomersFiltered }}</span><span
                                    class="label">Total Pelanggan</span></div>
                        </div>
                        @foreach ($statuses as $statusKey => $statusLabel)
                            @php $statusData = $summaryByStatus->get($statusKey); @endphp
                            <div class="col-md-auto col-sm-6">
                                <div class="summary-item"><span class="count">{{ $statusData['total'] ?? 0 }}</span><span
                                        class="label">{{ $statusLabel }}</span></div>
                            </div>
                        @endforeach
                    </div>
                    @if (isset($summaryByPaket) && $summaryByPaket->isNotEmpty())
                        <h6 class="mt-4 mb-2 fw-semibold">Sebaran Pelanggan per Paket:</h6>
                        <ul class="list-inline">
                            @foreach ($summaryByPaket as $paketData)
                                <li class="list-inline-item mb-2"><span
                                        class="badge bg-light text-dark p-2 border">{{ $paketData->kecepatan_paket }}:
                                        <strong class="text-primary">{{ $paketData->total }}</strong> Pelanggan</span></li>
                            @endforeach
                        </ul>
                    @endif
                    {{-- Menampilkan Ringkasan Pertumbuhan Pelanggan --}}
                    {{-- PERBAIKAN: Menggunakan camelCase activation_periodType dan activation_reportPeriodLabel --}}
                    @if (isset($customerGrowth) &&
                            (isset($activation_periodType) && $activation_periodType !== 'all' && !empty($activation_reportPeriodLabel)))
                        <h6 class="mt-4 mb-2 fw-semibold">Pertumbuhan Pelanggan Baru (Aktivasi
                            {{ $activation_reportPeriodLabel }})</h6>
                        <div class="row g-3">
                            <div class="col-md-4 col-sm-6">
                                <div class="summary-item"><span class="count">{{ $customerGrowth['count'] }}</span><span
                                        class="label">Pelanggan Baru Periode Ini</span></div>
                            </div>
                            <div class="col-md-4 col-sm-6">
                                <div class="summary-item"><span
                                        class="count">{{ $customerGrowth['previous_count'] }}</span><span
                                        class="label">Pelanggan Baru {{ $customerGrowth['label'] }}</span></div>
                            </div>
                            <div class="col-md-4 col-sm-12">
                                <div class="summary-item">
                                    @if ($customerGrowth['percentage'] !== null)
                                        <span
                                            class="count {{ $customerGrowth['percentage'] >= 0 ? 'text-success' : 'text-danger' }}">{{ $customerGrowth['percentage'] >= 0 ? '+' : '' }}{{ number_format($customerGrowth['percentage'], 1) }}%</span>
                                    @else
                                        <span class="count text-muted">-</span>
                                    @endif <span class="label">Persentase Pertumbuhan</span>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            <div class="card-body p-0">
                <div class="table-responsive">
                    {{-- ... (Kode Tabel Utama Anda tetap sama) ... --}}
                    <table class="table table-hover table-striped table-report mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>ID</th>
                                <th>Nama</th>
                                <th>NIK</th>
                                <th class="wrap-text">Alamat</th>
                                <th>No. WA</th>
                                <th>KTP</th>
                                <th>Rumah</th>
                                <th>Paket</th>
                                <th>User Aktif</th>
                                <th>Model Perangkat</th>
                                <th>SN Perangkat</th>
                                <th>IP PPPoE</th>
                                <th>IP ONU</th>
                                <th>Tgl Aktivasi</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($customers as $index => $customer)
                                <tr>
                                    <td>{{ $customers->firstItem() + $index }}</td>
                                    <td><a href="{{ route('customers.show', $customer->id_customer) }}"
                                            title="Lihat Detail Pelanggan">{{ $customer->id_customer }}</a></td>
                                    <td class="customer-name">{{ $customer->nama_customer }}</td>
                                    <td>{{ $customer->nik_customer ?? '-' }}</td>
                                    <td class="wrap-text">{{ $customer->alamat_customer ?? '-' }}</td>
                                    <td>
                                        @if ($customer->wa_customer)
                                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $customer->wa_customer) }}"
                                                target="_blank" class="text-success"><i class="fa fa-whatsapp"></i>
                                                {{ $customer->wa_customer }}</a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if ($customer->foto_ktp_customer)
                                            <a href="{{ asset('storage/' . $customer->foto_ktp_customer) }}"
                                                target="_blank" class="btn btn-sm btn-outline-secondary p-1"
                                                title="Lihat KTP"><i class="fa fa-id-card"></i></a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if ($customer->foto_timestamp_rumah)
                                            <a href="{{ asset('storage/' . $customer->foto_timestamp_rumah) }}"
                                                target="_blank" class="btn btn-sm btn-outline-secondary p-1"
                                                title="Lihat Foto Rumah"><i class="fa fa-home"></i></a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ $customer->paket->kecepatan_paket ?? '-' }} <small
                                            class="text-muted d-block">Rp{{ number_format($customer->paket->harga_paket ?? 0, 0, ',', '.') }}</small>
                                    </td>
                                    <td>{{ $customer->active_user ?? '-' }}</td>
                                    <td>{{ $customer->deviceSn->deviceModel->nama_model ?? '-' }}</td>
                                    <td>{{ $customer->deviceSn->nomor ?? '-' }}</td>
                                    <td>{{ $customer->ip_ppoe ?? '-' }}</td>
                                    <td>{{ $customer->ip_onu ?? '-' }}</td>
                                    <td>{{ $customer->tanggal_aktivasi ? \Carbon\Carbon::parse($customer->tanggal_aktivasi)->locale('id')->translatedFormat('d M Y') : '-' }}
                                    </td>
                                    <td class="text-center">
                                        @php
                                            $badgeClass = match ($customer->status) {
                                                'baru' => 'bg-info text-dark',
                                                'belum' => 'bg-secondary',
                                                'proses' => 'bg-warning text-dark',
                                                'terpasang' => 'bg-success',
                                                'nonaktif' => 'bg-danger',
                                                default => 'bg-light text-dark',
                                            };
                                        @endphp
                                        <span
                                            class="badge status-badge rounded-pill {{ $badgeClass }}">{{ Str::title(str_replace('_', ' ', $customer->status)) }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="16" class="text-center py-5">
                                        <p class="mb-0 text-muted fs-5"><i class="fa fa-users fa-3x mb-3 d-block"></i>Data
                                            pelanggan tidak ditemukan.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($customers->hasPages())
                    <div class="card-footer bg-light-subtle p-3">
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
