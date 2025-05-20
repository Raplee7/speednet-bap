@extends('layouts.app') {{-- Sesuaikan dengan layout admin utama Anda --}}

@section('title', $pageTitle ?? 'Laporan Keuangan Pendapatan')

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
        <div class="card-header p-3 rounded-top-4">
            <div class="row g-3">
                <!-- Judul Laporan -->
                <div class="col-lg-12">
                    @if (isset($reportPeriodLabel) && !empty($reportPeriodLabel))
                        <h4 class="text-muted mb-0">
                            <i class="fa fa-calendar-check me-2"></i>
                            Laporan Periode: <strong>{{ $reportPeriodLabel }}</strong>
                        </h4>
                    @else
                        <h5 class="text-muted mb-0">
                            <i class="fa fa-info-circle me-2"></i>
                            Silakan pilih filter periode untuk menampilkan laporan
                        </h5>
                    @endif
                </div>

                <!-- Form Filter -->
                <div class="col-lg-12">
                    <form action="{{ route('reports.financial') }}" method="GET" class="filter-form">
                        <div class="row g-2 align-items-center">
                            <!-- Jenis Periode -->
                            <div class="col-md-3 col-sm-4">
                                <div class="form-group">
                                    <label for="period_type" class="form-label small mb-1">Jenis Periode</label>
                                    <select name="period_type" id="period_type"
                                        class="form-select form-select-sm rounded-3">
                                        <option value="monthly"
                                            {{ ($periodType ?? 'monthly') == 'monthly' ? 'selected' : '' }}>Bulanan
                                        </option>
                                        <option value="daily" {{ ($periodType ?? '') == 'daily' ? 'selected' : '' }}>
                                            Harian</option>
                                        <option value="weekly" {{ ($periodType ?? '') == 'weekly' ? 'selected' : '' }}>
                                            Mingguan</option>
                                        <option value="yearly" {{ ($periodType ?? '') == 'yearly' ? 'selected' : '' }}>
                                            Tahunan</option>
                                        <option value="custom" {{ ($periodType ?? '') == 'custom' ? 'selected' : '' }}>
                                            Rentang Kustom</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Filter Harian -->
                            <div id="filter_daily" class="col-md-3 col-sm-4"
                                style="display: {{ ($periodType ?? '') == 'daily' ? 'block' : 'none' }};">
                                <div class="form-group">
                                    <label for="selected_date" class="form-label small mb-1">Tanggal</label>
                                    <input type="date" name="selected_date" id="selected_date"
                                        class="form-control form-control-sm rounded-3" value="{{ $selectedDate ?? '' }}">
                                </div>
                            </div>

                            <!-- Filter Bulanan -->
                            <div id="filter_monthly" class="col-md-3 col-sm-4"
                                style="display: {{ ($periodType ?? 'monthly') == 'monthly' ? 'block' : 'none' }};">
                                <div class="form-group">
                                    <label for="selected_month_year" class="form-label small mb-1">Tahun & Bulan
                                        (YYYY-MM)</label>
                                    <input type="month" name="selected_month_year" id="selected_month_year"
                                        class="form-control form-control-sm rounded-3"
                                        value="{{ $selectedMonthYear ?? '' }}">
                                </div>
                            </div>

                            <!-- Filter Tahunan -->
                            <div id="filter_yearly" class="col-md-3 col-sm-4"
                                style="display: {{ ($periodType ?? '') == 'yearly' ? 'block' : 'none' }};">
                                <div class="form-group">
                                    <label for="selected_year_only" class="form-label small mb-1">Tahun</label>
                                    <input type="number" name="selected_year_only" id="selected_year_only"
                                        class="form-control form-control-sm rounded-3" placeholder="YYYY"
                                        value="{{ $selectedYearOnly ?? '' }}" min="2020" max="{{ date('Y') + 1 }}">
                                </div>
                            </div>

                            <!-- Filter Rentang Kustom -->
                            <div id="filter_custom_range" class="col-md-6 col-sm-8"
                                style="display: {{ ($periodType ?? '') == 'custom' ? 'block' : 'none' }};">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label for="custom_start_date" class="form-label small mb-1">Dari
                                                Tanggal</label>
                                            <input type="date" name="custom_start_date" id="custom_start_date"
                                                class="form-control form-control-sm rounded-3"
                                                value="{{ $customStartDate ?? '' }}">
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label for="custom_end_date" class="form-label small mb-1">Sampai
                                                Tanggal</label>
                                            <input type="date" name="custom_end_date" id="custom_end_date"
                                                class="form-control form-control-sm rounded-3"
                                                value="{{ $customEndDate ?? '' }}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Tombol Aksi -->
                            <div class="col-md-3 col-sm-12 d-flex gap-2 align-items-end mt-2">
                                <button type="submit"
                                    class="btn btn-primary btn-sm flex-grow-0 d-flex align-items-center gap-1">
                                    <i class="fa fa-filter"></i>

                                    <span>Tampilkan</span>
                                </button>

                                <div class="dropdown">
                                    <button type="button" id="mainFinancialExportButton"
                                        class="btn btn-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown"
                                        aria-expanded="false"
                                        {{ !(isset($reportPeriodLabel) && !empty($reportPeriodLabel) && ($totalIncome ?? 0) > 0) ? 'disabled' : '' }}>
                                        <i class="fa fa-download me-1"></i> Export
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item {{ !(isset($reportPeriodLabel) && !empty($reportPeriodLabel) && ($totalIncome ?? 0) > 0) ? 'disabled' : '' }}"
                                                href="#" id="exportFinancialPdfButtonLink" target="_blank">
                                                <i class="fa fa-file-pdf me-2 text-danger"></i>PDF
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item {{ !(isset($reportPeriodLabel) && !empty($reportPeriodLabel) && ($totalIncome ?? 0) > 0) ? 'disabled' : '' }}"
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
            const filterDaily = document.getElementById('filter_daily');
            const filterMonthly = document.getElementById('filter_monthly');
            const filterYearly = document.getElementById('filter_yearly');
            const filterCustomRange = document.getElementById('filter_custom_range');
            const exportPdfButtonLink = document.getElementById('exportFinancialPdfButtonLink');
            const exportExcelButtonLink = document.getElementById('exportFinancialExcelButtonLink');
            const mainExportButton = document.getElementById('mainFinancialExportButton');

            function toggleDateFilters() {
                const selectedPeriod = periodTypeSelect.value;
                filterDaily.style.display = 'none';
                filterMonthly.style.display = 'none';
                filterYearly.style.display = 'none';
                filterCustomRange.style.display = 'none';

                if (selectedPeriod === 'daily') filterDaily.style.display = 'block';
                else if (selectedPeriod === 'monthly') filterMonthly.style.display = 'block';
                else if (selectedPeriod === 'yearly') filterYearly.style.display = 'block';
                else if (selectedPeriod === 'custom') filterCustomRange.style.display = 'flex';
                updateExportLinksStateFinancial();
            }

            function updateExportLinksStateFinancial() {
                const periodType = periodTypeSelect ? periodTypeSelect.value : '{{ $periodType ?? 'monthly' }}';
                let filtersAreSet = false;
                const params = new URLSearchParams({
                    period_type: periodType
                });
                const totalIncome = parseFloat({{ $totalIncome ?? 0 }}); // Ambil total income dari PHP

                if (periodType === 'daily' && document.getElementById('selected_date').value) {
                    params.append('selected_date', document.getElementById('selected_date').value);
                    filtersAreSet = true;
                } else if (periodType === 'monthly' && document.getElementById('selected_month_year').value) {
                    params.append('selected_month_year', document.getElementById('selected_month_year').value);
                    filtersAreSet = true;
                } else if (periodType === 'yearly' && document.getElementById('selected_year_only').value) {
                    params.append('selected_year_only', document.getElementById('selected_year_only').value);
                    filtersAreSet = true;
                } else if (periodType === 'custom' && document.getElementById('custom_start_date').value && document
                    .getElementById('custom_end_date').value) {
                    params.append('custom_start_date', document.getElementById('custom_start_date').value);
                    params.append('custom_end_date', document.getElementById('custom_end_date').value);
                    filtersAreSet = true;
                } else if (periodType === 'weekly') {
                    params.append('selected_date', "{{ $selectedDate ?? \Carbon\Carbon::now()->toDateString() }}");
                    filtersAreSet = true;
                }

                const enableExport = filtersAreSet && totalIncome > 0;

                if (mainExportButton) {
                    if (enableExport) mainExportButton.classList.remove('disabled');
                    else mainExportButton.classList.add('disabled');
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
                periodTypeSelect.addEventListener('change', toggleDateFilters);
                toggleDateFilters();
            }
            ['selected_date', 'selected_month_year', 'selected_year_only', 'custom_start_date', 'custom_end_date']
            .forEach(id => {
                const el = document.getElementById(id);
                if (el) el.addEventListener('change', updateExportLinksStateFinancial);
            });
            updateExportLinksStateFinancial(); // Panggil saat load juga


            if (exportPdfButtonLink) {
                exportPdfButtonLink.addEventListener('click', function(event) {
                    if (this.classList.contains('disabled')) {
                        event.preventDefault();
                        Swal.fire('Filter Belum Lengkap atau Tidak Ada Data',
                            'Silakan pilih filter periode yang valid dan pastikan ada data pendapatan untuk diexport.',
                            'warning');
                    }
                });
            }
            if (exportExcelButtonLink) {
                exportExcelButtonLink.addEventListener('click', function(event) {
                    if (this.classList.contains('disabled')) {
                        event.preventDefault();
                        Swal.fire('Filter Belum Lengkap atau Tidak Ada Data',
                            'Silakan pilih filter periode yang valid dan pastikan ada data pendapatan untuk diexport.',
                            'warning');
                    }
                });
            }
        });
    </script>
@endpush
