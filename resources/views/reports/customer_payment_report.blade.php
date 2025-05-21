@extends('layouts.app')

@push('styles')
    <style>
        /* Table styling improvements */
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

        /* Status Badge Improvements */
        .badge.status-badge {
            font-size: 0.72rem;
            padding: 0.4em 0.8em;
            font-weight: 500;
            letter-spacing: 0.3px;
            border-radius: 4px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }

        /* Status Colors */
        .status-paid .badge {
            background: linear-gradient(45deg, #28a745, #34ce57);
        }

        .status-unpaid .badge {
            background: linear-gradient(45deg, #ffc107, #ffdb4a);
        }

        .status-pending_confirmation .badge {
            background: linear-gradient(45deg, #17a2b8, #1fc8e3);
        }

        .status-failed .badge {
            background: linear-gradient(45deg, #dc3545, #f05565);
        }

        .status-cancelled .badge {
            background: linear-gradient(45deg, #6c757d, #868e96);
        }

        .status-menunggak .badge {
            background: linear-gradient(45deg, #dc3545, #c82333);
            font-weight: 600;
        }

        /* Invoice Number Link */
        .invoice-link {
            color: #3490dc;
            text-decoration: none;
            font-size: 0.75rem;
            padding: 0.2rem 0.5rem;
            border-radius: 3px;
            transition: all 0.2s ease;
        }

        .invoice-link:hover {
            background-color: rgba(52, 144, 220, 0.1);
            color: #2779bd;
        }

        /* Table Container */
        .table-responsive {
            border-radius: 0.5rem;
            border: 1px solid #e5e7eb;
            background: #fff;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        /* Pagination Improvements */
        .pagination {
            margin: 0;
            gap: 0.25rem;
        }

        .page-link {
            border-radius: 0.375rem;
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
            transition: all 0.2s ease;
        }

        /* Empty State Styling */
        .empty-state {
            padding: 3rem 1.5rem;
            text-align: center;
            background: linear-gradient(180deg, #f8f9fa 0%, #fff 100%);
        }

        .empty-state i {
            font-size: 3rem;
            color: #cbd5e0;
            margin-bottom: 1rem;
        }

        .empty-state p {
            color: #718096;
            font-size: 1rem;
            max-width: 20rem;
            margin: 0 auto;
        }
    </style>
@endpush

@section('content')
    <div class="card shadow-sm border-0 rounded-4">
        {{-- Status Pembayaran --}}
        <div class="card-header p-4 rounded-top-4">
            <div class="row g-3">
                <!-- Judul Laporan -->
                <div class="col-lg-12 mb-2">
                    @if ($selectedYear && $selectedStartMonth && $selectedEndMonth)
                        <h4 class="text-muted mb-0">
                            <i class="fa fa-calendar-check me-2"></i>
                            Laporan Periode: <strong>{{ $allMonthNames[$selectedStartMonth] }} -
                                {{ $allMonthNames[$selectedEndMonth] }} {{ $selectedYear }}</strong>
                        </h4>
                    @else
                        <h5 class="text-muted mb-0">
                            <i class="fa fa-info-circle me-2"></i>
                            Silakan pilih tahun dan rentang bulan untuk menampilkan laporan
                        </h5>
                    @endif
                </div>

                <!-- Form Filter -->
                <div class="col-lg-12">
                    <form action="{{ route('reports.customer_payment') }}" method="GET" class="filter-form">
                        <div class="row g-2 align-items-end flex-wrap">
                            <!-- Filter Tahun -->
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6">
                                <div class="form-group mb-0">
                                    <label for="year" class="form-label small mb-1">Tahun</label>
                                    <select name="year" id="year" class="form-select form-select-sm rounded-3">
                                        <option value="">Pilih Tahun</option>
                                        @foreach ($availableYears as $yearOption)
                                            <option value="{{ $yearOption }}"
                                                {{ $selectedYear == $yearOption ? 'selected' : '' }}>
                                                {{ $yearOption }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- Filter Bulan Awal -->
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6">
                                <div class="form-group mb-0">
                                    <label for="start_month" class="form-label small mb-1">Dari Bulan</label>
                                    <select name="start_month" id="start_month"
                                        class="form-select form-select-sm rounded-3">
                                        <option value="">Pilih Bulan</option>
                                        @foreach ($allMonthNames as $monthNumber => $monthName)
                                            <option value="{{ $monthNumber }}"
                                                {{ $selectedStartMonth == $monthNumber ? 'selected' : '' }}>
                                                {{ $monthName }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- Filter Bulan Akhir -->
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6">
                                <div class="form-group mb-0">
                                    <label for="end_month" class="form-label small mb-1">Sampai Bulan</label>
                                    <select name="end_month" id="end_month" class="form-select form-select-sm rounded-3">
                                        <option value="">Pilih Bulan</option>
                                        @foreach ($allMonthNames as $monthNumber => $monthName)
                                            <option value="{{ $monthNumber }}"
                                                {{ $selectedEndMonth == $monthNumber ? 'selected' : '' }}>
                                                {{ $monthName }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- Tombol Aksi -->
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6 d-flex align-items-end gap-2">
                                <button type="submit"
                                    class="btn btn-primary btn-sm flex-grow-1 d-flex align-items-center justify-content-center">
                                    <i class="fa fa-filter me-1"></i>
                                    <span>Tampilkan</span>
                                </button>

                                <div class="dropdown flex-grow-1">
                                    <button type="button" id="mainExportButton"
                                        class="btn btn-secondary btn-sm dropdown-toggle w-100" data-bs-toggle="dropdown"
                                        aria-expanded="false"
                                        {{ !$selectedYear || !$selectedStartMonth || !$selectedEndMonth ? 'disabled' : '' }}>
                                        <i class="fa fa-download me-1"></i> Export
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end w-100">
                                        <li>
                                            <a class="dropdown-item {{ !$selectedYear || !$selectedStartMonth || !$selectedEndMonth ? 'disabled' : '' }}"
                                                href="#" id="exportPdfButtonLink" target="_blank">
                                                <i class="fa fa-file-pdf me-2 text-danger"></i>PDF
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item {{ !$selectedYear || !$selectedStartMonth || !$selectedEndMonth ? 'disabled' : '' }}"
                                                href="#" id="exportExcelButtonLink" target="_blank">
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
        <div class="card-body p-0">
            @if ($selectedYear && $selectedStartMonth && $selectedEndMonth)
                @if ($reportData->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table table-report mb-0">
                            <thead>
                                <tr>
                                    <th rowspan="2" class="align-middle">ID Pel.</th>
                                    <th rowspan="2" class="align-middle">Nama Pelanggan</th>
                                    <th rowspan="2" class="align-middle text-center">Status</th>
                                    <th rowspan="2" class="align-middle">Paket</th>
                                    <th rowspan="2" class="align-middle text-center">Tgl Aktivasi</th>
                                    <th rowspan="2" class="align-middle text-center">Layanan Habis Terakhir</th>
                                    @foreach ($displayedMonths as $monthNumber => $monthName)
                                        <th colspan="1" class="month-col">
                                            <span class="d-block">{{ $monthName }}</span>
                                            <small class="text-muted d-block mt-1" style="font-size:0.7rem;">
                                                Status & Pembayaran
                                            </small>
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($reportData as $data)
                                    <tr>
                                        <td class="customer-id">
                                            <span class="text-monospace">#{{ $data['customer']->id_customer }}</span>
                                        </td>
                                        <td class="customer-name">{{ $data['customer']->nama_customer }}</td>
                                        <td class="text-center">
                                            @php
                                                $statusPelClass = match ($data['customer']->status) {
                                                    'baru' => 'bg-info',
                                                    'belum' => 'bg-secondary',
                                                    'proses' => 'bg-warning text-dark',
                                                    'terpasang' => 'bg-success',
                                                    'nonaktif' => 'bg-danger',
                                                    default => 'bg-light text-dark',
                                                };
                                            @endphp
                                            <span
                                                class="badge rounded-pill {{ $statusPelClass }}">{{ Str::title(str_replace('_', ' ', $data['customer']->status)) }}</span>
                                        </td>
                                        <td class="customer-info">{{ $data['paket_info'] }}</td>
                                        <td class="text-center customer-info">{{ $data['tgl_aktivasi'] }}</td>
                                        <td class="text-center customer-info">
                                            {{ $data['tgl_layanan_habis_terakhir_visual'] }}
                                            {{-- @if ($data['tgl_layanan_habis_sebenarnya'])
                                                <small class="d-block text-muted" style="font-size:0.7em;">(s/d
                                                    {{ $data['tgl_layanan_habis_sebenarnya'] }})</small>
                                            @endif --}}
                                        </td>
                                        @foreach ($displayedMonths as $monthNameKey => $monthDisplayName)
                                            @php
                                                $monthData = $data['monthly_status'][$monthDisplayName] ?? [
                                                    'text' => '-',
                                                    'class' => 'text-muted',
                                                    'tgl_bayar' => null,
                                                    'invoice_no' => null,
                                                    'payment_id' => null,
                                                ];
                                            @endphp
                                            <td class="month-col {{ $monthData['class'] }}">
                                                @if ($monthData['tgl_bayar'])
                                                    <div class="mb-1">
                                                        <small
                                                            class="text-muted d-block">{{ $monthData['tgl_bayar'] }}</small>
                                                    </div>
                                                @endif

                                                <div class="mb-1">
                                                    <span class="badge status-badge">{{ $monthData['text'] }}</span>
                                                </div>


                                                @if (!empty($monthData['invoice_no']) && $monthData['text'] != '-')
                                                    @if ($monthData['payment_id'])
                                                        <a href="{{ route('payments.show', $monthData['payment_id']) }}"
                                                            class="invoice-link"
                                                            title="Lihat Invoice {{ $monthData['invoice_no'] }}">
                                                            {{ Str::limit($monthData['invoice_no'], 10, '...') }}
                                                        </a>
                                                    @else
                                                        <small class="text-muted d-block">
                                                            {{ Str::limit($monthData['invoice_no'], 10, '...') }}
                                                        </small>
                                                    @endif
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if ($customersPaginator->hasPages())
                        <div class="card-footer bg-light-subtle p-3">
                            {{ $customersPaginator->appends(request()->query())->links() }} {{-- Memastikan filter terbawa saat paginasi --}}
                        </div>
                    @endif
                @elseif($selectedYear && $selectedStartMonth && $selectedEndMonth)
                    <div class="text-center p-5">
                        <p class="mb-0 text-muted fs-5">
                            <i class="fa fa-folder-open-o fa-3x mb-3 d-block"></i>
                            Tidak ada data pelanggan untuk periode yang dipilih.
                        </p>
                    </div>
                @else
                    <div class="text-center p-5">
                        <p class="mb-0 text-muted fs-5">
                            <i class="fa fa-filter fa-3x mb-3 d-block"></i>
                            Silakan pilih Tahun, Dari Bulan, dan Sampai Bulan untuk menampilkan laporan.
                        </p>
                    </div>
                @endif
            @endif
        </div>
    </div>
    </div>
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const exportPdfButtonLink = document.getElementById('exportPdfButtonLink');
                const exportExcelButtonLink = document.getElementById('exportExcelButtonLink');
                const yearSelect = document.getElementById('year');
                const startMonthSelect = document.getElementById('start_month');
                const endMonthSelect = document.getElementById('end_month');
                const mainExportButton = document.getElementById('mainExportButton'); // Tombol dropdown Export utama

                function updateExportLinksState() {
                    const year = yearSelect.value;
                    const startMonth = startMonthSelect.value;
                    const endMonth = endMonthSelect.value;
                    const filtersSelected = year && startMonth && endMonth;

                    // Update state tombol dropdown utama
                    if (mainExportButton) {
                        if (filtersSelected) {
                            mainExportButton.classList.remove('disabled');
                        } else {
                            mainExportButton.classList.add('disabled');
                        }
                    }

                    // Update PDF Link
                    if (exportPdfButtonLink) {
                        if (filtersSelected) {
                            let pdfUrl = "{{ route('reports.customer_payment.pdf') }}";
                            const params = new URLSearchParams({
                                year: year,
                                start_month: startMonth,
                                end_month: endMonth
                            });
                            // Menyertakan parameter filter lain yang mungkin ada dari request() saat ini
                            // Ini berguna jika Anda menambahkan filter lain di masa depan (misal status pelanggan, paket, dll.)
                            // Hati-hati jika ada parameter yang tidak ingin disertakan
                            @foreach (request()->except(['year', 'start_month', 'end_month', '_token', '_method', 'page']) as $key => $value)
                                @if (is_array($value))
                                    @foreach ($value as $subValue)
                                        if ('{{ $subValue }}'.trim() !== '') params.append(
                                            '{{ $key }}[]', '{{ $subValue }}');
                                    @endforeach
                                @elseif ($value !== null && trim((string) $value) !== '')
                                    params.append('{{ $key }}', '{{ $value }}');
                                @endif
                            @endforeach
                            exportPdfButtonLink.href = pdfUrl + '?' + params.toString();
                            exportPdfButtonLink.classList.remove('disabled');
                        } else {
                            exportPdfButtonLink.href = '#';
                            exportPdfButtonLink.classList.add('disabled');
                        }
                    }

                    // Update Excel Link
                    if (exportExcelButtonLink) {
                        if (filtersSelected) {
                            let excelUrl = "{{ route('reports.customer_payment.excel') }}";
                            const params = new URLSearchParams({
                                year: year,
                                start_month: startMonth,
                                end_month: endMonth
                            });
                            @foreach (request()->except(['year', 'start_month', 'end_month', '_token', '_method', 'page']) as $key => $value)
                                @if (is_array($value))
                                    @foreach ($value as $subValue)
                                        if ('{{ $subValue }}'.trim() !== '') params.append(
                                            '{{ $key }}[]', '{{ $subValue }}');
                                    @endforeach
                                @elseif ($value !== null && trim((string) $value) !== '')
                                    if ('{{ $value }}'.trim() !== '') params.append('{{ $key }}',
                                        '{{ $value }}');
                                @endif
                            @endforeach
                            exportExcelButtonLink.href = excelUrl + '?' + params.toString();
                            exportExcelButtonLink.classList.remove('disabled');
                        } else {
                            exportExcelButtonLink.href = '#';
                            exportExcelButtonLink.classList.add('disabled');
                        }
                    }
                }

                // Panggil saat halaman dimuat untuk set state awal tombol export
                updateExportLinksState();

                // Panggil juga jika ada perubahan pada filter
                if (yearSelect) yearSelect.addEventListener('change', updateExportLinksState);
                if (startMonthSelect) startMonthSelect.addEventListener('change', updateExportLinksState);
                if (endMonthSelect) endMonthSelect.addEventListener('change', updateExportLinksState);

                // Event listener untuk mencegah aksi default jika item dropdown disabled (meskipun class disabled pada <a> tidak selalu mencegah klik)
                if (exportPdfButtonLink) {
                    exportPdfButtonLink.addEventListener('click', function(event) {
                        if (!yearSelect.value || !startMonthSelect.value || !endMonthSelect.value) {
                            event.preventDefault();
                            Swal.fire('Filter Belum Lengkap',
                                'Silakan pilih Tahun, Dari Bulan, dan Sampai Bulan terlebih dahulu untuk export.',
                                'warning');
                        }
                    });
                }
                if (exportExcelButtonLink) {
                    exportExcelButtonLink.addEventListener('click', function(event) {
                        if (!yearSelect.value || !startMonthSelect.value || !endMonthSelect.value) {
                            event.preventDefault();
                            Swal.fire('Filter Belum Lengkap',
                                'Silakan pilih Tahun, Dari Bulan, dan Sampai Bulan terlebih dahulu untuk export.',
                                'warning');
                        }
                    });
                }
            });
        </script>
    @endpush

@endsection
