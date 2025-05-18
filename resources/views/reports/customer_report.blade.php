@extends('layouts.app')

@push('styles')
    <style>
        .table-report th,
        .table-report td {
            font-size: 0.78rem;
            padding: 0.35rem 0.45rem;
            vertical-align: middle;
            white-space: nowrap;
        }

        .table-report thead th {
            text-align: center;
            background-color: #f0f0f0;
            position: sticky;
            top: 0;
            z-index: 1;
        }

        .table-report tbody td.customer-name {
            min-width: 180px;
            white-space: normal;
        }

        .table-report tbody td.customer-id {
            min-width: 100px;
        }

        .table-report tbody td.customer-info {
            min-width: 120px;
            white-space: nowrap;
        }

        .table-report .month-col {
            min-width: 90px;
            text-align: center;
        }

        .table-report .month-col small {
            font-size: 0.7rem;
            display: block;
        }

        .badge.status-badge {
            font-size: 0.75rem;
            padding: .35em .65em;
            color: #fff !important;
            /* Pastikan teks badge putih agar kontras dengan bg */
        }

        .status-paid .badge {
            background-color: var(--bs-success) !important;
        }

        .status-unpaid .badge {
            background-color: var(--bs-warning) !important;
            color: #000 !important;
        }

        /* Teks hitam untuk bg kuning */
        .status-pending_confirmation .badge {
            background-color: var(--bs-info) !important;
            color: #000 !important;
        }

        /* Teks hitam untuk bg info */
        .status-failed .badge {
            background-color: var(--bs-danger) !important;
        }

        .status-cancelled .badge {
            background-color: var(--bs-secondary) !important;
        }

        .status-menunggak .badge {
            background-color: var(--bs-danger) !important;
            font-weight: bold;
        }

        .filter-form .form-select,
        .filter-form .form-control,
        .filter-form .btn {
            font-size: 0.85rem;
        }

        .table-responsive {
            overflow-x: auto;
            border-top: 1px solid #dee2e6;
            /* Garis atas untuk tabel jika card-body p-0 */
        }

        .card-header h4,
        .card-header p {
            margin-bottom: 0;
            /* Mengatur margin pada header card */
        }
    </style>
@endpush

@section('content')
    <section class="container-fluid p-4">
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-header bg-light-subtle p-3 rounded-top-4">
                <div class="row align-items-center">
                    <div class="col-md-5">
                        <h4 class="card-title mb-1 fw-semibold">{{ $pageTitle }}</h4>
                        @if ($selectedYear && $selectedStartMonth && $selectedEndMonth)
                            <p class="text-muted mb-0 small">Menampilkan laporan untuk:
                                <strong>{{ $allMonthNames[$selectedStartMonth] }} - {{ $allMonthNames[$selectedEndMonth] }}
                                    {{ $selectedYear }}</strong>
                            </p>
                        @else
                            <p class="text-muted mb-0 small">Silakan pilih tahun dan rentang bulan untuk menampilkan laporan.
                            </p>
                        @endif
                    </div>
                    <div class="col-md-7">
                        {{-- Form Filter Tahun & Rentang Bulan --}}
                        <form action="{{ route('reports.customer') }}" method="GET"
                            class="row gx-2 gy-2 align-items-center justify-content-md-end filter-form">
                            <div class="col-md-3 col-6">
                                <label for="year" class="form-labelvisually-hidden">Tahun:</label>
                                <select name="year" id="year" class="form-select form-select-sm rounded-pill">
                                    @foreach ($availableYears as $yearOption)
                                        <option value="{{ $yearOption }}"
                                            {{ $selectedYear == $yearOption ? 'selected' : '' }}>
                                            Tahun {{ $yearOption }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 col-6">
                                <label for="start_month" class="form-labelvisually-hidden">Dari Bulan:</label>
                                <select name="start_month" id="start_month" class="form-select form-select-sm rounded-pill">
                                    <option value="">Dari Bulan...</option>
                                    @foreach ($allMonthNames as $monthNumber => $monthName)
                                        <option value="{{ $monthNumber }}"
                                            {{ $selectedStartMonth == $monthNumber ? 'selected' : '' }}>
                                            {{ $monthName }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 col-6">
                                <label for="end_month" class="form-labelvisually-hidden">Sampai Bulan:</label>
                                <select name="end_month" id="end_month" class="form-select form-select-sm rounded-pill">
                                    <option value="">Sampai Bulan...</option>
                                    @foreach ($allMonthNames as $monthNumber => $monthName)
                                        <option value="{{ $monthNumber }}"
                                            {{ $selectedEndMonth == $monthNumber ? 'selected' : '' }}>
                                            {{ $monthName }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div
                                class="col-lg-auto col-md-12 col-sm-6 col-12 d-flex gap-2 mt-md-0 mt-2 justify-content-md-start justify-content-stretch">
                                <button type="submit"
                                    class="btn btn-primary btn-sm rounded-pill flex-grow-1 flex-md-grow-0">
                                    <i class="fa fa-filter me-1"></i> Tampilkan
                                </button>
                                <div class="btn-group flex-grow-1 flex-md-grow-0">
                                    <button type="button" id="mainExportButton" {{-- Tambahkan ID untuk JS --}}
                                        class="btn btn-secondary btn-sm rounded-pill dropdown-toggle w-100"
                                        data-bs-toggle="dropdown" aria-expanded="false" {{-- Tombol utama dropdown akan di-disable/enable oleh JS --}}
                                        {{ !$selectedYear || !$selectedStartMonth || !$selectedEndMonth ? 'disabled' : '' }}>
                                        <i class="fa fa-download me-1"></i> Export
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item {{ !$selectedYear || !$selectedStartMonth || !$selectedEndMonth ? 'disabled' : '' }}"
                                                href="#" {{-- Href akan diisi oleh JS --}} id="exportPdfButtonLink"
                                                target="_blank"> {{-- Buka PDF di tab baru --}}
                                                <i class="fa fa-file-pdf-o me-2 text-danger"></i>Ke PDF
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item {{ !$selectedYear || !$selectedStartMonth || !$selectedEndMonth ? 'disabled' : '' }}"
                                                href="#" {{-- Href akan diisi oleh JS --}} id="exportExcelButtonLink"
                                                target="_blank"> {{-- Buka Excel (download) di tab baru --}}
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
            <div class="card-body p-0">
                @if ($selectedYear && $selectedStartMonth && $selectedEndMonth)
                    @if ($reportData->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-striped table-report mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th rowspan="2" class="align-middle">ID Pel.</th>
                                        <th rowspan="2" class="align-middle">Nama Pelanggan</th>
                                        <th rowspan="2" class="align-middle text-center">Status</th>
                                        <th rowspan="2" class="align-middle">Paket</th>
                                        <th rowspan="2" class="align-middle text-center">Tgl Aktivasi</th>
                                        <th rowspan="2" class="align-middle text-center">Layanan Habis Terakhir</th>
                                        {{-- Kolom Bulan Dinamis berdasarkan $displayedMonths --}}
                                        @foreach ($displayedMonths as $monthNumber => $monthName)
                                            <th colspan="1" class="month-col">{{ $monthName }}</th>
                                        @endforeach
                                    </tr>
                                    <tr>
                                        {{-- Sub-header untuk Tgl Bayar & Status di setiap bulan --}}
                                        @foreach ($displayedMonths as $monthName)
                                            <th class="month-col small" style="font-size:0.65rem;">Tgl Bayar | Status | Inv
                                            </th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($reportData as $data)
                                        <tr>
                                            <td class="customer-id">{{ $data['customer']->id_customer }}</td>
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
                                                @if ($data['tgl_layanan_habis_sebenarnya'])
                                                    <small class="d-block text-muted" style="font-size:0.7em;">(s/d
                                                        {{ $data['tgl_layanan_habis_sebenarnya'] }})</small>
                                                @endif
                                            </td>
                                            @foreach ($displayedMonths as $monthNameKey => $monthDisplayName)
                                                {{-- Gunakan $monthNameKey untuk akses array --}}
                                                @php $monthData = $data['monthly_status'][$monthDisplayName] ?? ['text' => '-', 'class' => 'text-muted', 'tgl_bayar' => null, 'invoice_no' => null, 'payment_id' => null]; @endphp
                                                <td class="month-col {{ $monthData['class'] }}">
                                                    @if ($monthData['tgl_bayar'])
                                                        <span class="d-block small"
                                                            title="Tanggal Pembayaran">{{ $monthData['tgl_bayar'] }}</span>
                                                    @endif
                                                    <span class="badge status-badge">{{ $monthData['text'] }}</span>

                                                    @if (!empty($monthData['invoice_no']) && $monthData['text'] != '-')
                                                        @if ($monthData['payment_id'])
                                                            <a href="{{ route('payments.show', $monthData['payment_id']) }}"
                                                                class="d-block small text-decoration-none text-primary"
                                                                title="Lihat Invoice {{ $monthData['invoice_no'] }}"
                                                                style="font-size: 0.7em;">
                                                                {{ Str::limit($monthData['invoice_no'], 10, '...') }}
                                                            </a>
                                                        @else
                                                            <span class="d-block small text-muted" title="No. Invoice"
                                                                style="font-size: 0.7em;">{{ Str::limit($monthData['invoice_no'], 10, '...') }}</span>
                                                        @endif
                                                    @elseif($monthData['text'] == '-' && empty($monthData['tgl_bayar']))
                                                        <span class="d-block small">&nbsp;</span>
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
    </section>
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
                            let pdfUrl = "{{ route('reports.customer.pdf') }}";
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
                            let excelUrl = "{{ route('reports.customer.excel') }}";
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
