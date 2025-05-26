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
        {{-- Pandapatan --}}
        <div class="card-header bg-light-subtle p-3 rounded-top-4">
            <div class="row align-items-center">
                <div class="col-lg-4">
                    <h4 class="card-title mb-1 fw-semibold">{{ $pageTitle ?? 'Laporan Keuangan Pendapatan' }}</h4>
                    {{-- Menampilkan label periode yang aktif --}}
                    @if (isset($reportPeriodLabel) && !empty($reportPeriodLabel))
                        <p class="text-muted mb-0 small">Periode: <strong>{{ $reportPeriodLabel }}</strong></p>
                    @else
                        <p class="text-muted mb-0 small">Pilih filter periode untuk menampilkan laporan.</p>
                    @endif
                </div>
                <div class="col-lg-8">
                    {{-- Form Filter Periode --}}
                    <form action="{{ route('reports.financial') }}" method="GET"
                        class="row gx-2 gy-2 align-items-end justify-content-lg-end filter-form">

                        {{-- Include Partial Filter Periode --}}
                        {{-- Pastikan variabel $availableYears dan $allMonthNames dikirim dari controller jika partial membutuhkannya --}}
                        @include('reports.partials._period_filter', [
                            'periodPrefix' => '', // Tidak ada prefix untuk filter utama laporan ini
                            'periodData' => $request->all(), // Mengirim semua request agar nilai filter lama terbaca oleh partial
                            // atau kirim array spesifik dari controller:
                            // 'periodData' => [
                            //    'period_type' => $periodType,
                            //    'selected_date' => $selectedDate,
                            //    // ...dst...
                            // ]
                            'availableYears' => $availableYears ?? [],
                            'allMonthNames' => $allMonthNames ?? [],
                        ])

                        <div class="col-lg-auto col-md-12 d-flex gap-2 mt-md-3 mt-lg-0 justify-content-end">
                            <button type="submit"
                                class="btn btn-primary btn-sm rounded-pill flex-grow-1 flex-md-grow-0 px-4">
                                <i class="fa fa-filter me-1"></i> Tampilkan
                            </button>
                            <a href="{{ route('reports.financial') }}" class="btn btn-secondary btn-sm rounded-pill px-4"
                                title="Reset Filter">
                                <i class="fa fa-refresh"></i> Reset
                            </a>
                            <div class="btn-group flex-grow-1 flex-md-grow-0">
                                <button type="button" id="mainFinancialExportButton"
                                    class="btn btn-dark btn-sm rounded-pill dropdown-toggle w-100 px-4"
                                    data-bs-toggle="dropdown" aria-expanded="false" {{-- Tombol export disable jika tidak ada data DAN filter belum valid --}}
                                    {{ !(isset($reportPeriodLabel) && !empty($reportPeriodLabel) && ($totalIncome ?? 0) > 0 && $periodType !== 'all' && ($periodType === 'custom' ? $customStartDate && $customEndDate : true)) ? 'disabled' : '' }}>
                                    <i class="fa fa-download me-1"></i> Export
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item {{ !(isset($reportPeriodLabel) && !empty($reportPeriodLabel) && ($totalIncome ?? 0) > 0 && $periodType !== 'all' && ($periodType === 'custom' ? $customStartDate && $customEndDate : true)) ? 'disabled' : '' }}"
                                            href="#" id="exportFinancialPdfButtonLink" target="_blank">
                                            <i class="fa fa-file-pdf-o me-2 text-danger"></i>Ke PDF
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item {{ !(isset($reportPeriodLabel) && !empty($reportPeriodLabel) && ($totalIncome ?? 0) > 0 && $periodType !== 'all' && ($periodType === 'custom' ? $customStartDate && $customEndDate : true)) ? 'disabled' : '' }}"
                                            href="#" id="exportFinancialExcelButtonLink" target="_blank">
                                            <i class="fa fa-file-excel-o me-2 text-success"></i>Ke Excel
                                        </a>
                                    </li>
                                </ul>
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
