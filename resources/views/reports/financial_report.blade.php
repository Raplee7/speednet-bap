@extends('layouts.app')

@push('styles')
    <style>
        .report-summary-card {
            background-color: var(--bs-primary-bg-subtle, #cfe2ff);
            border-left: 5px solid var(--bs-primary, #0D6EFD);
            border-radius: .5rem;
            /* rounded-3 */
        }

        .report-summary-card .display-5 {
            font-weight: 700;
            color: var(--bs-primary, #0D6EFD);
        }

        .table-report th,
        .table-report td {
            font-size: 0.875rem;
            /* Sedikit diperbesar dari sebelumnya */
            padding: 0.5rem 0.75rem;
            /* Padding standar tabel */
            vertical-align: middle;
        }

        .table-report thead th {
            background-color: #e9ecef;
            /* Warna header tabel lebih standar */
            font-weight: 600;
            /* fw-semibold */
            white-space: nowrap;
        }

        .table-report td.amount,
        .table-report th.amount {
            text-align: right;
        }

        .filter-form .form-select,
        .filter-form .form-control,
        .filter-form .btn {
            font-size: 0.875rem;
            /* Ukuran font filter konsisten */
        }

        /* Sembunyikan input tanggal custom secara default */
        .custom-date-range-picker {
            display: none;
        }

        .card-header h4,
        .card-header p.small {
            margin-bottom: 0;
        }

        .card-body h5.section-title {
            /* Judul untuk setiap section tabel */
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #dee2e6;
        }
    </style>
@endpush

@section('content')
    <div class="card shadow-sm border-0 rounded-4">
        {{-- Pendapatan --}}
        <div class="card-header p-4 rounded-top-4">
            <div class="row g-3">
                <!-- Judul Laporan -->
                <div class="col-lg-12 mb-2">
                    <h4 class="text-muted mb-0">
                        <i class="fa fa-chart-line me-2"></i>
                        {{ $pageTitle ?? 'Laporan Keuangan Pendapatan' }}
                    </h4>
                    {{-- Menampilkan label periode yang aktif --}}
                    @if (isset($reportPeriodLabel) && !empty($reportPeriodLabel))
                        <p class="text-muted mb-0 small mt-1">
                            Periode: <strong>{{ $reportPeriodLabel }}</strong>
                        </p>
                    @else
                        <p class="text-muted mb-0 small mt-1">
                            Silakan pilih filter periode untuk menampilkan laporan
                        </p>
                    @endif
                </div>

                <!-- Form Filter -->
                <div class="col-lg-12">
                    {{-- Form Filter Periode --}}
                    <form action="{{ route('reports.financial') }}" method="GET" class="filter-form">
                        <div class="row g-2 align-items-end flex-wrap">
                            {{-- Include Partial Filter Periode --}}
                            @include('reports.partials._period_filter', [
                                'periodPrefix' => '',
                                'periodData' => $request->all(),
                                'availableYears' => $availableYears ?? [],
                                'allMonthNames' => $allMonthNames ?? [],
                            ])

                            <!-- Tombol Aksi -->
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6 d-flex align-items-end gap-2">
                                <button type="submit"
                                    class="btn btn-primary btn-sm flex-grow-1 d-flex align-items-center justify-content-center">
                                    <i class="fa fa-filter me-1"></i>
                                    <span>Tampilkan</span>
                                </button>
                                <div class="dropdown flex-grow-1">
                                    <button type="button" id="mainFinancialExportButton"
                                        class="btn btn-success btn-sm dropdown-toggle w-100" data-bs-toggle="dropdown"
                                        aria-expanded="false"
                                        {{ !(isset($reportPeriodLabel) && !empty($reportPeriodLabel) && ($totalIncome ?? 0) > 0 && $periodType !== 'all' && ($periodType === 'custom' ? $customStartDate && $customEndDate : true)) ? 'disabled' : '' }}>
                                        <i class="fa fa-download me-1"></i> Export
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end w-100">
                                        <li>
                                            <a class="dropdown-item {{ !(isset($reportPeriodLabel) && !empty($reportPeriodLabel) && ($totalIncome ?? 0) > 0 && $periodType !== 'all' && ($periodType === 'custom' ? $customStartDate && $customEndDate : true)) ? 'disabled' : '' }}"
                                                href="#" id="exportFinancialPdfButtonLink" target="_blank">
                                                <i class="fa fa-file-pdf me-2 text-danger"></i>PDF
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item {{ !(isset($reportPeriodLabel) && !empty($reportPeriodLabel) && ($totalIncome ?? 0) > 0 && $periodType !== 'all' && ($periodType === 'custom' ? $customStartDate && $customEndDate : true)) ? 'disabled' : '' }}"
                                                href="#" id="exportFinancialExcelButtonLink" target="_blank">
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


        <div class="card-body p-4">
            @if (isset($reportPeriodLabel) && !empty($reportPeriodLabel))
                <div class="row g-4">
                    {{-- Total Pendapatan --}}
                    <div class="col-12 mb-3">
                        <div class="card report-summary-card rounded-3 shadow-sm">
                            <div class="card-body text-center p-4">
                                <h6 class="text-uppercase text-muted mb-2">Total Pendapatan</h6>
                                <p class="display-5 mb-0">Rp {{ number_format($totalIncome ?? 0, 0, ',', '.') }}</p>
                                <small class="text-muted">Untuk periode: {{ $reportPeriodLabel }}</small>
                                @if (isset($previousPeriodIncome) && $previousPeriodIncome !== null)
                                    @php
                                        $percentageChange = 0;
                                        if ($previousPeriodIncome > 0) {
                                            $percentageChange =
                                                (($totalIncome - $previousPeriodIncome) / $previousPeriodIncome) * 100;
                                        } elseif ($totalIncome > 0) {
                                            $percentageChange = 100;
                                        }
                                    @endphp
                                    <p class="mb-0 mt-2 small text-muted">
                                        @if ($percentageChange > 0)
                                            <span class="text-success"><i class="fa fa-arrow-up"></i>
                                                {{ number_format($percentageChange, 1) }}%</span>
                                        @elseif ($percentageChange < 0)
                                            <span class="text-danger"><i class="fa fa-arrow-down"></i>
                                                {{ number_format(abs($percentageChange), 1) }}%</span>
                                        @else
                                            <span class="text-muted">Tidak ada perubahan signifikan</span>
                                        @endif
                                        dari periode sebelumnya (Rp
                                        {{ number_format($previousPeriodIncome, 0, ',', '.') }})
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Pendapatan per Paket --}}
                    <div class="col-md-6">
                        <h5 class="mb-3 fw-semibold text-primary section-title">
                            <i class="fa-solid fa-wifi me-2"></i>Pendapatan per Paket
                        </h5>
                        @if (isset($incomeByPaket) && $incomeByPaket->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-sm table-striped table-hover table-report">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Nama Paket</th>
                                            <th class="text-end">Jml. Transaksi</th>
                                            <th class="text-end">Total Pendapatan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($incomeByPaket as $item)
                                            <tr>
                                                <td>{{ $item->kecepatan_paket }}</td>
                                                <td class="text-end">{{ $item->transaction_count }}</td>
                                                <td class="text-end">Rp {{ number_format($item->total, 0, ',', '.') }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted">Tidak ada data pendapatan per paket untuk periode ini.</p>
                        @endif
                    </div>

                    {{-- Pendapatan per Metode Pembayaran --}}
                    <div class="col-md-6">
                        <h5 class="mb-3 fw-semibold text-primary section-title">
                            <i class="fa fa-credit-card me-2"></i>Pendapatan per Metode Pembayaran
                        </h5>
                        @if (isset($incomeByMethod) && $incomeByMethod->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-sm table-striped table-hover table-report">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Metode Pembayaran</th>
                                            <th class="text-end">Jml. Transaksi</th>
                                            <th class="text-end">Total Pendapatan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($incomeByMethod as $metode => $data)
                                            <tr>
                                                <td>{{ Str::title($metode) }}</td>
                                                <td class="text-end">{{ $data['count'] }}</td>
                                                <td class="text-end">Rp
                                                    {{ number_format($data['total'], 0, ',', '.') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted">Tidak ada data pendapatan per metode pembayaran untuk periode ini.
                            </p>
                        @endif
                    </div>
                </div>
            @else
                <div class="text-center p-5">
                    <p class="mb-0 text-muted fs-5">
                        <i class="fa fa-filter fa-3x mb-3 d-block"></i>
                        Silakan pilih jenis periode dan filter yang sesuai untuk menampilkan laporan.
                    </p>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const periodTypeSelect = document.getElementById('period_type');
            const selectedDateInput = document.getElementById('selected_date');
            const selectedMonthYearInput = document.getElementById('selected_month_year');
            const selectedYearOnlyInput = document.getElementById('selected_year_only');
            const customStartDateInput = document.getElementById('custom_start_date');
            const customEndDateInput = document.getElementById('custom_end_date');

            const exportPdfButtonLink = document.getElementById('exportFinancialPdfButtonLink');
            const exportExcelButtonLink = document.getElementById('exportFinancialExcelButtonLink');
            const mainExportButton = document.getElementById('mainFinancialExportButton');

            function updateExportLinksStateFinancial() {
                const periodType = periodTypeSelect ? periodTypeSelect.value : '{{ $periodType ?? 'all' }}';
                let filtersAreSet = false;
                const params = new URLSearchParams();
                if (periodTypeSelect) params.append('period_type', periodType);

                const totalIncomeValue = parseFloat('{{ $totalIncome ?? 0 }}');

                if (periodType === 'daily' && selectedDateInput && selectedDateInput.value) {
                    params.append('selected_date', selectedDateInput.value);
                    filtersAreSet = true;
                } else if (periodType === 'monthly' && selectedMonthYearInput && selectedMonthYearInput.value) {
                    params.append('selected_month_year', selectedMonthYearInput.value);
                    filtersAreSet = true;
                } else if (periodType === 'yearly' && selectedYearOnlyInput && selectedYearOnlyInput.value) {
                    params.append('selected_year_only', selectedYearOnlyInput.value);
                    filtersAreSet = true;
                } else if (periodType === 'custom' && customStartDateInput && customStartDateInput.value &&
                    customEndDateInput && customEndDateInput.value) {
                    params.append('custom_start_date', customStartDateInput.value);
                    params.append('custom_end_date', customEndDateInput.value);
                    filtersAreSet = true;
                } else if (periodType === 'weekly') {
                    if (selectedDateInput && selectedDateInput.value) {
                        params.append('selected_date', selectedDateInput.value);
                    } else {
                        params.append('selected_date',
                            "{{ $selectedDate ?? \Carbon\Carbon::now()->toDateString() }}");
                    }
                    filtersAreSet = true;
                } else if (periodType === 'all') {
                    filtersAreSet = true; // "Semua Periode" dianggap filter yang valid
                }

                // PERUBAHAN LOGIKA UNTUK enableExport
                // Tombol export aktif jika filter periode valid (termasuk 'all'),
                // DAN (jika bukan 'all', harus ada income ATAU jika 'all', income bisa 0)
                const enableExport = filtersAreSet && (periodType === 'all' || totalIncomeValue > 0);

                if (mainExportButton) {
                    if (enableExport) {
                        mainExportButton.classList.remove('disabled');
                        mainExportButton.removeAttribute('disabled');
                    } else {
                        mainExportButton.classList.add('disabled');
                        mainExportButton.setAttribute('disabled', 'disabled');
                    }
                }

                if (exportPdfButtonLink) {
                    if (enableExport) {
                        exportPdfButtonLink.href = "{{ route('reports.financial.pdf') }}" + '?' + params
                            .toString();
                        exportPdfButtonLink.classList.remove('disabled');
                    } else {
                        exportPdfButtonLink.href = '#';
                        exportPdfButtonLink.classList.add('disabled');
                    }
                }
                if (exportExcelButtonLink) {
                    if (enableExport) {
                        exportExcelButtonLink.href = "{{ route('reports.financial.excel') }}" + '?' + params
                            .toString();
                        exportExcelButtonLink.classList.remove('disabled');
                    } else {
                        exportExcelButtonLink.href = '#';
                        exportExcelButtonLink.classList.add('disabled');
                    }
                }
            }

            if (periodTypeSelect) {
                // toggleDateFilters() akan dipanggil oleh script di _period_filter.blade.php
                // kita hanya perlu memastikan updateExportLinksStateFinancial dipanggil setelahnya atau saat change
                periodTypeSelect.addEventListener('change', updateExportLinksStateFinancial);
            }
            [selectedDateInput, selectedMonthYearInput, selectedYearOnlyInput, customStartDateInput,
                customEndDateInput
            ].forEach(input => {
                if (input) {
                    input.addEventListener('change', updateExportLinksStateFinancial);
                }
            });
            updateExportLinksStateFinancial(); // Panggil saat load


            if (exportPdfButtonLink) {
                exportPdfButtonLink.addEventListener('click', function(event) {
                    if (mainExportButton && mainExportButton.classList.contains('disabled')) {
                        event.preventDefault();
                        Swal.fire('Filter Belum Lengkap atau Tidak Ada Data',
                            'Silakan pilih filter periode yang valid dan pastikan ada data pendapatan untuk diexport (kecuali untuk "Semua Periode").',
                            'warning');
                    }
                });
            }
            if (exportExcelButtonLink) {
                exportExcelButtonLink.addEventListener('click', function(event) {
                    if (mainExportButton && mainExportButton.classList.contains('disabled')) {
                        event.preventDefault();
                        Swal.fire('Filter Belum Lengkap atau Tidak Ada Data',
                            'Silakan pilih filter periode yang valid dan pastikan ada data pendapatan untuk diexport (kecuali untuk "Semua Periode").',
                            'warning');
                    }
                });
            }
        });
    </script>
@endpush
