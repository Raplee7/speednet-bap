@extends('layouts.app')
@push('styles')
    <style>
        .hover-shadow:hover {
            transform: translateY(-1px);
            transition: all 0.2s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1) !important;
        }

        .icon-wrapper {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .card-dashboard-table {
            min-height: 300px;
        }

        .shine-effect {
            animation: shine 2s infinite;
        }

        @keyframes shine {
            0% {
                background-color: rgba(var(--bs-info-rgb), 0.05);
            }

            50% {
                background-color: rgba(var(--bs-info-rgb), 0.15);
            }

            100% {
                background-color: rgba(var(--bs-info-rgb), 0.05);
            }
        }

        .chart-container {
            position: relative;
            margin: 0 auto;
            max-width: 100%;
        }


        .stats-card {
            height: 100%;
            min-height: 120px;
        }

        .table-card {
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .table-card .card-body {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .table-responsive {
            flex: 1;
            max-height: 400px;
            overflow-y: auto;
        }

        /* CSS untuk Stat Card Kustom dengan Bar Kiri - Diperkecil */
        .stat-card {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            position: relative;
            padding: 0;
            border-left-width: 4px;
            border-left-style: solid;
            /* min-height: 140px; */
            /* max-height: 160px; */
        }

        .stat-card-body {
            padding: 16px;
            flex-grow: 1;
            position: relative;
            z-index: 1;
        }

        .stat-card-icon-chip {
            display: inline-flex;
            align-items: center;
            padding: 4px 8px;
            border-radius: 6px;
            margin-bottom: 8px;
            font-size: 0.8rem;
        }

        .stat-card-chip-icon {
            margin-right: 6px;
            font-size: 0.9em;
        }

        .stat-card-chip-text {
            font-size: 0.75em;
            font-weight: 500;
        }

        .stat-card-main-value {
            font-size: 2rem;
            font-weight: 700;
            line-height: 1.1;
            margin-bottom: 4px;
            color: #333;
        }

        .stat-card-unit {
            font-size: 1.2rem;
            font-weight: 500;
            color: #777;
            margin-left: 2px;
        }

        .stat-card-currency {
            font-size: 1.2rem;
            font-weight: 500;
            color: #777;
        }

        .stat-card-description {
            font-size: 0.8em;
            color: #6c757d;
            margin-bottom: 8px;
            line-height: 1.3;
        }

        .stat-card-faint-icon-bg {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 3.5rem;
            color: rgba(0, 0, 0, 0.03);
            z-index: 0;
            line-height: 1;
        }

        .stat-card-bottom-line {
            height: 3px;
            width: 25px;
            border-radius: 2px;
            margin: 0 16px 8px 16px;
        }

        .stat-card-footer {
            padding: 8px 16px;
            background-color: transparent;
            text-align: center;
            border-top: 1px solid #f0f0f0;
        }

        .stat-card-footer-link {
            font-size: 0.8em;
            text-decoration: none;
            font-weight: 500;
        }

        .stat-card-footer-link i {
            transition: transform 0.2s ease-in-out;
        }

        .stat-card-footer-link:hover i {
            transform: translateX(2px);
        }

        /* Tema Warna */
        .stat-card-green {
            border-left-color: #28a745 !important;
        }

        .stat-card-green .stat-card-icon-chip {
            background-color: rgba(40, 167, 69, 0.1);
        }

        .stat-card-green .stat-card-chip-icon,
        .stat-card-green .stat-card-chip-text {
            color: #28a745;
        }

        .stat-card-green .stat-card-bottom-line {
            background-color: #28a745;
        }

        .stat-card-green .stat-card-footer-link {
            color: #28a745;
        }

        .stat-card-blue {
            border-left-color: #0d6efd !important;
        }

        .stat-card-blue .stat-card-icon-chip {
            background-color: rgba(13, 110, 253, 0.1);
        }

        .stat-card-blue .stat-card-chip-icon,
        .stat-card-blue .stat-card-chip-text {
            color: #0d6efd;
        }

        .stat-card-blue .stat-card-bottom-line {
            background-color: #0d6efd;
        }

        .stat-card-blue .stat-card-footer-link {
            color: #0d6efd;
        }

        .stat-card-orange {
            border-left-color: #fd7e14 !important;
        }

        .stat-card-orange .stat-card-icon-chip {
            background-color: rgba(253, 126, 20, 0.1);
        }

        .stat-card-orange .stat-card-chip-icon,
        .stat-card-orange .stat-card-chip-text {
            color: #fd7e14;
        }

        .stat-card-orange .stat-card-bottom-line {
            background-color: #fd7e14;
        }

        .stat-card-orange .stat-card-footer-link {
            color: #fd7e14;
        }

        .stat-card-purple {
            border-left-color: #6f42c1 !important;
        }

        .stat-card-purple .stat-card-icon-chip {
            background-color: rgba(111, 66, 193, 0.1);
        }

        .stat-card-purple .stat-card-chip-icon,
        .stat-card-purple .stat-card-chip-text {
            color: #6f42c1;
        }

        .stat-card-purple .stat-card-bottom-line {
            background-color: #6f42c1;
        }

        .stat-card-purple .stat-card-footer-link {
            color: #6f42c1;
        }

        /* Responsif untuk mobile */
        @media (max-width: 768px) {
            .stat-card-main-value {
                font-size: 1.6rem;
            }

            .stat-card-faint-icon-bg {
                font-size: 2.5rem;
            }

            .table-responsive {
                max-height: 300px;
            }
        }

        /* Card headers dengan ukuran lebih kecil */
        .card-header {
            padding: 12px 16px !important;
        }

        .card-header h5 {
            font-size: 1rem;
            margin-bottom: 0;
        }

        .card-body {
            padding: 16px;
        }

        /* Tabel lebih compact */
        .table td,
        .table th {
            padding: 8px 12px;
            font-size: 0.85rem;
        }

        .btn-sm {
            padding: 4px 8px;
            font-size: 0.75rem;
        }

        /* List group items lebih compact */
        .list-group-item {
            padding: 12px 16px !important;
        }

        /* Chart containers dengan ukuran terbatas */
        #paketUsageDonutChart {
            max-width: 220px !important;
            max-height: 220px !important;
        }
    </style>
@endpush
@section('content')

    {{-- Baris untuk Kartu Angka - Kompak --}}
    <div class="row g-3 mb-4">
        {{-- 1. Pelanggan Aktif/Total - Blue Theme --}}
        <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
            <div class="stat-card stat-card-blue h-100">
                <div class="stat-card-body">
                    <div class="stat-card-icon-chip">
                        <i class="fas fa-users stat-card-chip-icon"></i>
                        <span class="stat-card-chip-text">Pelanggan</span>
                    </div>
                    <div class="stat-card-main-value">
                        {{ $totalActiveCustomers ?? 0 }}<span class="stat-card-unit">/{{ $totalCustomers ?? 0 }}</span>
                    </div>
                    <p class="stat-card-description">Pelanggan Aktif / Total</p>
                    <div class="stat-card-faint-icon-bg">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
                <div class="stat-card-bottom-line"></div>
                <div class="stat-card-footer">
                    <a href="{{ route('customers.index') }}" class="stat-card-footer-link">
                        Lihat Detail <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>

        {{-- 2. Total Pendapatan Bulan Ini - Green Theme --}}
        <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
            <div class="stat-card stat-card-green h-100">
                <div class="stat-card-body">
                    <div class="stat-card-icon-chip">
                        <i class="fas fa-dollar-sign stat-card-chip-icon"></i>
                        <span class="stat-card-chip-text">Pendapatan</span>
                    </div>
                    <div class="stat-card-main-value">
                        <span class="stat-card-currency">Rp</span>{{ number_format($incomeThisMonth ?? 0, 0, ',', '.') }}
                    </div>
                    <p class="stat-card-description">Pendapatan Bulan Ini</p>
                    <div class="stat-card-faint-icon-bg">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
                <div class="stat-card-bottom-line"></div>
                <div class="stat-card-footer">
                    <a href="{{ route('reports.financial') }}" class="stat-card-footer-link">
                        Lihat Laporan <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>

        {{-- 3. Total Tagihan Belum Dibayar - Orange Theme --}}
        <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
            <div class="stat-card stat-card-orange h-100">
                <div class="stat-card-body">
                    <div class="stat-card-icon-chip">
                        <i class="fas fa-file-invoice-dollar stat-card-chip-icon"></i>
                        <span class="stat-card-chip-text">Tagihan</span>
                    </div>
                    <div class="stat-card-main-value">
                        <span
                            class="stat-card-currency">Rp</span>{{ number_format($totalUnpaidInvoicesAmount ?? 0, 0, ',', '.') }}
                    </div>
                    <p class="stat-card-description">Tagihan Belum Dibayar ( {{ $countUnpaidInvoices ?? 0 }} )</p>
                    <div class="stat-card-faint-icon-bg">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
                <div class="stat-card-bottom-line"></div>
                <div class="stat-card-footer">
                    <a href="{{ route('payments.index', ['status_pembayaran' => 'belum_lunas']) }}"
                        class="stat-card-footer-link">
                        Lihat Detail <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>

        {{-- 4. Total Perangkat - Purple Theme --}}
        <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
            <div class="stat-card stat-card-purple h-100">
                <div class="stat-card-body">
                    <div class="stat-card-icon-chip">
                        <i class="fas fa-hdd stat-card-chip-icon"></i>
                        <span class="stat-card-chip-text">Perangkat</span>
                    </div>
                    <div class="stat-card-main-value">
                        {{ $totalDevices ?? 0 }}<span class="stat-card-unit"> unit</span>
                    </div>
                    <p class="stat-card-description">Total Perangkat</p>
                    <div class="stat-card-faint-icon-bg">
                        <i class="fas fa-hdd"></i>
                    </div>
                </div>
                <div class="stat-card-bottom-line"></div>
                <div class="stat-card-footer">
                    <a href="{{ route('device_sns.index') }}" class="stat-card-footer-link">
                        Lihat Detail <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Baris untuk Tabel Aksi Cepat - Kompak --}}
    <div class="row g-3 mb-4">
        {{-- Pelanggan Akan Habis Masa Aktif --}}
        <div class="col-lg-6">
            <div class="card border-0 rounded-3 shadow-sm table-card" style="max-height: 450px;">
                <div class="card-header bg-gradient-danger text-white rounded-top-3 border-0">
                    <h5 class="mb-0 fw-semibold d-flex align-items-center">
                        <i class="fa fa-user-clock me-2"></i>
                        Akan Habis Masa Aktif ({{ $daysThreshold }} hari)
                        <span class="badge bg-white text-danger ms-auto">{{ count($expiringSoonCustomers) }}</span>
                    </h5>
                </div>
                <div class="card-body p-0 flex-grow-1">
                    @if (count($expiringSoonCustomers) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3 fw-semibold">Pelanggan</th>
                                        <th class="text-center fw-semibold">Berakhir</th>
                                        <th class="text-center fw-semibold">Sisa</th>
                                        <th class="text-center pe-3 fw-semibold">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($expiringSoonCustomers as $customer)
                                        <tr class="border-bottom">
                                            <td class="ps-3">
                                                <div>
                                                    <div class="fw-medium">{{ Str::limit($customer->nama_customer, 18) }}
                                                    </div>
                                                    <small class="text-muted">{{ $customer->id_customer }}</small>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-light text-dark">
                                                    {{ $customer->layanan_berakhir_pada->format('d/m') }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                @if ($customer->sisa_hari <= 0)
                                                    <span class="badge bg-danger text-white">Hari Ini</span>
                                                @elseif ($customer->sisa_hari <= 3)
                                                    <span
                                                        class="badge bg-warning-subtle text-dark">{{ $customer->sisa_hari }}h</span>
                                                @else
                                                    <span
                                                        class="badge bg-info text-white">{{ $customer->sisa_hari }}h</span>
                                                @endif
                                            </td>
                                            <td class="text-center pe-3">
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('customers.show', $customer->id_customer) }}"
                                                        class="btn btn-outline-primary btn-sm" title="Detail">
                                                        <i class="fa fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('payments.create', ['customer_id' => $customer->id_customer, 'auto_fill_next_period' => 'true']) }}"
                                                        class="btn btn-outline-success btn-sm" title="Tagihan">
                                                        <i class="fa fa-file-invoice"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="p-4 text-center text-muted">
                            <i class="fa fa-check-circle fs-2 mb-2 text-success"></i>
                            <p class="mb-0 small">Tidak ada yang akan berakhir.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Pelanggan Baru (Butuh Konfirmasi) --}}
        <div class="col-lg-6">
            <div class="card border-0 rounded-3 shadow-sm table-card" style="max-height: 450px;">
                <div class="card-header bg-gradient-info text-white rounded-top-3 border-0">
                    <h5 class="mb-0 fw-semibold d-flex align-items-center">
                        <i class="fa fa-user-plus me-2"></i>
                        Pelanggan Baru (Butuh Konfirmasi)
                        <span class="badge bg-white text-info ms-auto">{{ $countNewCustomersNeedingConfirmation }}</span>
                    </h5>
                </div>
                <div class="card-body p-0 flex-grow-1">
                    @if ($newCustomersNeedingConfirmationList->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3 fw-semibold">Pelanggan</th>
                                        <th class="text-center fw-semibold">Tanggal</th>
                                        <th class="text-center pe-3 fw-semibold">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($newCustomersNeedingConfirmationList as $customer)
                                        <tr class="{{ $customer->status == 'baru' ? 'shine-effect' : '' }} border-bottom">
                                            <td class="ps-3">
                                                <div>
                                                    <div class="fw-medium">{{ Str::limit($customer->nama_customer, 18) }}
                                                    </div>
                                                    <small class="text-muted">{{ $customer->id_customer }}</small>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-light text-dark">
                                                    {{ $customer->created_at->locale('id')->diffForHumans() }}
                                                </span>
                                            </td>
                                            <td class="text-center pe-3">
                                                <a href="{{ route('customers.show', $customer->id_customer) }}"
                                                    class="btn btn-outline-info btn-sm" title="Proses">
                                                    <i class="fa fa-arrow-right me-1"></i> Proses
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if ($countNewCustomersNeedingConfirmation > $newCustomersNeedingConfirmationList->count())
                            <div class="card-footer bg-light text-center border-0">
                                <a href="{{ route('customers.index', ['status' => 'baru']) }}"
                                    class="btn btn-info btn-sm">
                                    Lihat Semua ({{ $countNewCustomersNeedingConfirmation }}) <i
                                        class="fa fa-angle-double-right ms-1"></i>
                                </a>
                            </div>
                        @endif
                    @else
                        <div class="p-4 text-center text-muted">
                            <i class="fa fa-users fs-2 mb-2 text-info"></i>
                            <p class="mb-0 small">Tidak ada pelanggan baru.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Baris untuk Pembayaran & Chart - Kompak --}}
    <div class="row g-3 mb-4">
        {{-- Pembayaran Terbaru Menunggu Konfirmasi --}}
        <div class="col-lg-7">
            <div class="card border-0 rounded-3 shadow-sm table-card" style="max-height: 400px;">
                <div class="card-header bg-gradient-primary text-white rounded-top-3 border-0">
                    <h5 class="mb-0 fw-semibold d-flex align-items-center">
                        <i class="fa fa-hourglass-half me-2"></i>
                        Pembayaran Menunggu Konfirmasi
                        <span class="badge bg-white text-primary ms-auto">{{ $countPendingConfirmationPayments }}</span>
                    </h5>
                </div>
                <div class="card-body p-0 flex-grow-1">
                    @if ($latestPendingPayments->count() > 0)
                        <div class="list-group list-group-flush" style="max-height: 320px; overflow-y: auto;">
                            @foreach ($latestPendingPayments as $payment)
                                <a href="{{ route('payments.show', $payment->id_payment) }}"
                                    class="list-group-item list-group-item-action border-0 d-flex justify-content-between align-items-center hover-shadow">
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-start mb-1">
                                            <h6 class="mb-0 fw-semibold text-dark">{{ $payment->nomor_invoice }}</h6>
                                            <span class="badge bg-primary-subtle text-primary">
                                                {{ $payment->updated_at->locale('id')->diffForHumans() }}
                                            </span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small
                                                class="text-muted">{{ Str::limit($payment->customer->nama_customer ?? 'N/A', 20) }}</small>
                                            <span class="fw-bold text-success">Rp
                                                {{ number_format($payment->jumlah_tagihan, 0, ',', '.') }}</span>
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                        @if ($countPendingConfirmationPayments > $latestPendingPayments->count())
                            <div class="card-footer bg-light text-center border-0">
                                <a href="{{ route('payments.index', ['status_pembayaran' => 'pending_confirmation']) }}"
                                    class="btn btn-primary btn-sm">
                                    Lihat Semua ({{ $countPendingConfirmationPayments }}) <i
                                        class="fa fa-angle-double-right ms-1"></i>
                                </a>
                            </div>
                        @endif
                    @else
                        <div class="p-4 text-center text-muted">
                            <i class="fa fa-check-circle fs-2 mb-2 text-success"></i>
                            <p class="mb-0 small">Tidak ada pembayaran menunggu.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Donut Chart Paket Internet Terpopuler --}}
        <div class="col-lg-5">
            <div class="card border-0 rounded-3 shadow-sm h-100" style="max-height: 400px;">
                <div class="card-header bg-gradient-success text-white rounded-top-3 border-0">
                    <h5 class="mb-0 fw-semibold text-center">
                        <i class="fa fa-pie-chart me-2"></i>
                        Paket Terpopuler
                    </h5>
                </div>
                <div class="card-body d-flex justify-content-center align-items-center">
                    <div class="chart-container">
                        <canvas id="paketUsageDonutChart" width="220" height="220"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Baris untuk Bar Chart Pendapatan - Kompak --}}
    <div class="row g-3">
        <div class="col-12">
            <div class="card border-0 rounded-3 shadow-sm" style="max-height: 350px;">
                <div class="card-header bg-gradient-primary text-white rounded-top-3 border-0">
                    <h5 class="mb-0 fw-semibold text-center">
                        <i class="fa fa-bar-chart me-2"></i>
                        Pendapatan 6 Bulan Terakhir
                    </h5>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="monthlyIncomeChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    {{-- Pastikan kamu sudah meload Chart.js di layout utama atau di sini --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Bar Chart Pendapatan Bulanan
            const monthlyIncomeCtx = document.getElementById('monthlyIncomeChart');
            if (monthlyIncomeCtx) {
                const monthlyIncomeData = @json($monthlyIncomeData);
                new Chart(monthlyIncomeCtx, {
                    type: 'bar',
                    data: {
                        labels: monthlyIncomeData.map(row => row.month),
                        datasets: [{
                            label: 'Pendapatan (Rp)',
                            data: monthlyIncomeData.map(row => row.income),
                            backgroundColor: 'rgba(54, 162, 235, 0.5)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value, index, values) {
                                        return 'Rp ' + value.toLocaleString('id-ID');
                                    }
                                }
                            }
                        },
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        let label = context.dataset.label || '';
                                        if (label) {
                                            label += ': ';
                                        }
                                        if (context.parsed.y !== null) {
                                            label += 'Rp ' + context.parsed.y.toLocaleString('id-ID');
                                        }
                                        return label;
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // Donut Chart Penggunaan Paket (BARU)
            const paketUsageCtx = document.getElementById('paketUsageDonutChart');
            if (paketUsageCtx) {
                const paketUsageData = @json($paketUsageData);
                const backgroundColors = [ // Siapkan beberapa warna
                    'rgba(255, 99, 132, 0.7)', 'rgba(54, 162, 235, 0.7)',
                    'rgba(255, 206, 86, 0.7)', 'rgba(75, 192, 192, 0.7)',
                    'rgba(153, 102, 255, 0.7)', 'rgba(255, 159, 64, 0.7)',
                    'rgba(199, 199, 199, 0.7)', 'rgba(83, 102, 83, 0.7)'
                ];
                const borderColors = backgroundColors.map(color => color.replace('0.7', '1'));

                new Chart(paketUsageCtx, {
                    type: 'doughnut',
                    data: {
                        labels: paketUsageData.map(row => row.kecepatan_paket),
                        datasets: [{
                            label: 'Jumlah Pengguna',
                            data: paketUsageData.map(row => row.total_pengguna),
                            backgroundColor: backgroundColors.slice(0, paketUsageData.length),
                            borderColor: borderColors.slice(0, paketUsageData.length),
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom', // Pindahkan legenda ke bawah agar tidak menutupi chart kecil
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        let label = context.label || '';
                                        if (label) {
                                            label += ': ';
                                        }
                                        if (context.parsed !== null) {
                                            label += context.parsed.toLocaleString('id-ID') +
                                                ' pengguna';
                                        }
                                        return label;
                                    }
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>
@endpush
