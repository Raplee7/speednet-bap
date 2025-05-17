@extends('layouts.app') {{-- Sesuaikan dengan layout admin utama Anda --}}
@section('content')
    <div class="row">
        <div class="col-xl-6 col-md-6 ">
            <div class="card border-0 rounded-4 shadow-sm hover-shadow ">
                <div class="card-body d-flex align-items-center p-4">
                    <div class="flex-shrink-0 me-3 rounded-circle bg-primary bg-opacity-10 p-3 icon-wrapper">
                        <i class="fa fa-users text-primary fa-2x"></i>
                    </div>
                    <div>
                        <h3 class="mb-0 fw-bold card-title-num">
                            {{ $totalActiveCustomers }}<small class="fw-normal text-muted fs-5"> /
                                {{ $totalCustomers }}</small>
                        </h3>
                        <p class="mb-0 text-muted small card-text-label">Pelanggan Aktif / Total</p>
                    </div>
                </div>
                <div class="card-footer bg-transparent text-center py-2 border-0 rounded-bottom-4">
                    <a href="{{ route('customers.index') }}" class="text-primary small text-decoration-none link-muted">
                        <span class="me-1">Lihat</span>
                        <i class="fa fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
        <div class="col-xl-6 col-md-6 ">
            <div class="card border-0 rounded-4 shadow-sm hover-shadow ">
                <div class="card-body d-flex align-items-center p-4">
                    <div class="flex-shrink-0 me-3 rounded-circle bg-info bg-opacity-10 p-3 icon-wrapper">
                        <i class="fa fa-user-plus text-info fa-2x"></i>
                    </div>
                    <div>
                        <h3 class="mb-0 fw-bold card-title-num">{{ $newCustomersNeedingConfirmation }}</h3>
                        <p class="mb-0 text-muted small card-text-label">Pelanggan Baru (Butuh Konfirmasi)</p>
                    </div>
                </div>
                @if ($newCustomersNeedingConfirmation > 0)
                    <div class="card-footer bg-transparent text-center py-2 border-0 rounded-bottom-4">
                        <a href="{{ route('customers.index', ['status' => 'baru']) }}"
                            class="text-info small text-decoration-none link-muted">
                            <span class="me-1">Lihat & Proses</span>
                            <i class="fa fa-arrow-right"></i>
                        </a>
                    </div>
                @endif
            </div>
        </div>



        {{-- <div class="col-xl-4 col-md-6 mb-3">
            <div class="card border-0 rounded-4 shadow-sm hover-shadow h-100">
                <div class="card-body d-flex align-items-center p-4">
                    <div class="flex-shrink-0 me-3 rounded-circle bg-warning bg-opacity-10 p-3 icon-wrapper">
                        <i class="fa fa-clock-o text-warning fa-2x"></i>
                    </div>
                    <div>
                        <h3 class="mb-0 fw-bold card-title-num">{{ $pendingConfirmationPayments }}</h3>
                        <p class="mb-0 text-muted small card-text-label">Tagihan Menunggu Konfirmasi</p>
                    </div>
                </div>
                @if ($pendingConfirmationPayments > 0)
                    <div class="card-footer bg-transparent text-center py-2 border-0 rounded-bottom-4">
                        <a href="{{ route('payments.index', ['status_pembayaran' => 'pending_confirmation']) }}"
                            class="text-warning small text-decoration-none link-muted">
                            <span class="me-1">Lihat & Verifikasi</span>
                            <i class="fa fa-arrow-right"></i>
                        </a>
                    </div>
                @endif
            </div>
        </div> --}}
        {{--         
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 rounded-4 shadow-sm hover-shadow h-100">
                <div class="card-body d-flex align-items-center p-4">
                    <div class="flex-shrink-0 me-3 rounded-circle bg-success bg-opacity-10 p-3 icon-wrapper">
                        <i class="fa fa-money text-success fa-2x"></i>
                    </div>
                    <div>
                        <h3 class="mb-0 fw-bold card-title-num">Rp {{ number_format($incomeThisMonth, 0, ',', '.') }}
                        </h3>
                        <p class="mb-0 text-muted small card-text-label">Pendapatan Bulan Ini</p>
                    </div>
                </div>
            </div>
        </div> --}}
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card border-0 rounded-4 shadow-sm card-dashboard-table">
                <div class="card-header p-3 rounded-top-4 bg-white">
                    <h5 class="mb-0 fw-semibold d-flex align-items-center fs-6">
                        <i class="mdi mdi-alarm-off text-danger me-2"></i>
                        Pelanggan Akan Habis Masa Aktif (dalam {{ $daysThreshold }} hari)
                    </h5>
                </div>
                <div class="card-body p-0">
                    @if (count($expiringSoonCustomers) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3">No.</th>
                                        <th>ID</th>
                                        <th>Nama</th>
                                        <th class="text-center">Berakhir</th>
                                        <th class="text-center">Sisa</th>
                                        <th class="text-center pe-3">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($expiringSoonCustomers as $customer)
                                        <tr>
                                            <td class="ps-3">{{ $loop->iteration }}</td>
                                            <td>{{ $customer->id_customer }}</td>
                                            <td>{{ Str::limit($customer->nama_customer, 20) }}</td>
                                            <td class="text-center">
                                                {{ $customer->layanan_berakhir_pada->locale('id')->translatedFormat('d M Y') }}
                                            </td>
                                            <td class="text-center">
                                                @if ($customer->sisa_hari <= 0)
                                                    <span class="badge text-bg-danger rounded-pill px-3">Hari
                                                        Ini</span>
                                                @elseif ($customer->sisa_hari <= 3)
                                                    <span
                                                        class="badge text-bg-warning rounded-pill px-3">{{ $customer->sisa_hari }}
                                                        hari</span>
                                                @else
                                                    <span
                                                        class="badge text-bg-info rounded-pill px-3">{{ $customer->sisa_hari }}
                                                        hari</span>
                                                @endif
                                            </td>
                                            <td class="text-center pe-3">
                                                <a href="{{ route('customers.show', $customer->id_customer) }}"
                                                    class="btn btn-sm btn-outline-primary rounded-pill py-1 px-2"
                                                    title="Detail Pelanggan">
                                                    <i class="fa fa-search"></i>
                                                </a>
                                                <a href="{{ route('payments.create', ['customer_id' => $customer->id_customer, 'auto_fill_next_period' => 'true']) }}"
                                                    class="btn btn-sm btn-outline-success rounded-pill py-1 px-2 ms-1"
                                                    title="Buat Tagihan">
                                                    <i class="fa fa-file-text-o"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="p-4 text-center text-muted">
                            <p class="mb-0">Tidak ada pelanggan yang masa aktifnya akan berakhir dalam
                                {{ $daysThreshold }} hari ke depan.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card border-0 rounded-4 shadow-sm card-dashboard-table">
                <div class="card-header p-3 rounded-top-4 bg-white">
                    <h5 class="mb-0 fs-6 fw-semibold d-flex align-items-center">
                        <i class="mdi mdi-timer text-info me-2"></i>
                        Pembayaran Terbaru Menunggu Konfirmasi
                    </h5>
                </div>
                <div class="card-body p-0">
                    @if ($latestPendingPayments->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach ($latestPendingPayments as $payment)
                                <div
                                    class="list-group-item border-0 px-4 py-3 d-flex justify-content-between align-items-center">
                                    <div>
                                        <a href="{{ route('payments.show', $payment->id_payment) }}"
                                            class="fw-semibold text-decoration-none d-block">
                                            {{ $payment->nomor_invoice }}
                                        </a>
                                        <span
                                            class="d-block small text-muted">{{ Str::limit($payment->customer->nama_customer ?? 'N/A', 20) }}</span>
                                        <span class="badge bg-success-subtle text-success mt-1">Rp
                                            {{ number_format($payment->jumlah_tagihan, 0, ',', '.') }}</span>
                                        <span
                                            class="badge bg-secondary-subtle text-secondary mt-1 ms-1">{{ $payment->updated_at->locale('id')->diffForHumans() }}</span>
                                    </div>
                                    <a href="{{ route('payments.show', $payment->id_payment) }}"
                                        class="btn btn-sm btn-info rounded-pill py-1 px-2">
                                        Verifikasi
                                        <i class="fa fa-check ms-1"></i>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                        @if ($pendingConfirmationPayments > $latestPendingPayments->count())
                            <div class="card-footer bg-light-subtle text-center p-2 rounded-bottom-4">
                                <a href="{{ route('payments.index', ['status_pembayaran' => 'pending_confirmation']) }}"
                                    class="text-primary small text-decoration-none link-muted">
                                    <span class="me-1">Lihat Semua ({{ $pendingConfirmationPayments }})</span>
                                    <i class="fa fa-arrow-right"></i>
                                </a>
                            </div>
                        @endif
                    @else
                        <div class="p-4 text-center text-muted">
                            <p class="mb-0">Tidak ada Pembayaran menunggu konfirmasi saat ini.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="card border-0 rounded-4 shadow-sm p-3 mb-4">
                <h5 class="mb-4 fw-semibold text-center text-primary">
                    <i class="fa fa-bar-chart me-2"></i>
                    Pendapatan 6 Bulan Terakhir
                </h5>
                <div class="chart-container" style="position: relative; height: 300px;">
                    <canvas id="monthlyIncomeChart"></canvas>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const monthlyIncomeData = @json($monthlyIncomeData);

            if (monthlyIncomeData && monthlyIncomeData.length > 0) {
                const labels = monthlyIncomeData.map(item => item.month);
                const data = monthlyIncomeData.map(item => item.income);

                const ctx = document.getElementById('monthlyIncomeChart');
                if (ctx) {
                    new Chart(ctx.getContext('2d'), {
                        type: 'bar',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Pendapatan (Rp)',
                                data: data,
                                backgroundColor: 'rgba(13, 110, 253, 0.7)', // Warna primary Bootstrap dengan opacity
                                borderColor: 'rgba(13, 110, 253, 1)',
                                borderWidth: 1,
                                borderRadius: {
                                    topLeft: 6,
                                    topRight: 6
                                }, // Rounded top corners
                                hoverBackgroundColor: 'rgba(13, 110, 253, 0.9)'
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: function(value) {
                                            if (value >= 1000000) return 'Rp ' + (value / 1000000)
                                                .toLocaleString('id-ID') + ' Jt';
                                            if (value >= 1000) return 'Rp ' + (value / 1000)
                                                .toLocaleString('id-ID') + ' Rb';
                                            return 'Rp ' + value.toLocaleString('id-ID');
                                        }
                                    }
                                },
                                x: {
                                    grid: {
                                        display: false
                                    }
                                }
                            },
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    backgroundColor: '#212529',
                                    titleFont: {
                                        weight: 'bold'
                                    },
                                    bodyFont: {
                                        size: 14
                                    },
                                    displayColors: false,
                                    callbacks: {
                                        label: function(context) {
                                            let label = context.dataset.label || '';
                                            if (label) {
                                                label += ': ';
                                            }
                                            if (context.parsed.y !== null) {
                                                label += 'Rp ' + context.parsed.y.toLocaleString(
                                                    'id-ID');
                                            }
                                            return label;
                                        }
                                    }
                                }
                            },
                            interaction: {
                                intersect: false,
                                mode: 'index',
                            },
                        }
                    });
                }
            } else {
                const chartContainer = document.querySelector('.chart-container');
                if (chartContainer) {
                    chartContainer.innerHTML =
                        '<div class="p-5 text-center text-muted"><i class="fa fa-bar-chart fa-3x mb-3 text-secondary opacity-50"></i><p class="mb-0">Data pendapatan belum cukup untuk ditampilkan.</p></div>';
                }
            }
        });
    </script>
@endpush

<style>
    /* CSS untuk memperbaiki tampilan dashboard */
    .hover-shadow {
        transition: all 0.3s ease;
    }

    .hover-shadow:hover {
        transform: translateY(-5px);
        box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .15) !important;
    }

    .icon-wrapper {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 60px;
        height: 60px;
    }

    .card-title-num {
        font-size: 1.75rem;
    }

    .card-text-label {
        font-size: 0.85rem;
    }

    .list-group-item:hover {
        background-color: rgba(0, 0, 0, .01);
    }

    /* Membuat kartu tidak gepeng */
    .card {
        border-radius: 15px;
        transition: all 0.3s;
    }

    /* Membuat tampilan responsive untuk mobile */
    @media (max-width: 768px) {
        .icon-wrapper {
            width: 50px;
            height: 50px;
        }

        .card-title-num {
            font-size: 1.5rem;
        }
    }
</style>
