@extends('landing.layouts.app') {{-- Atau layout khusus area pelanggan jika Anda sudah membuatnya --}}

@section('title', $pageTitle ?? 'Pembayaran & Perpanjangan Layanan')

@push('styles')
    <style>
        .customer-content-area {
            padding-top: 120px;
            /* Sesuaikan dengan tinggi header fixed Anda */
            padding-bottom: 60px;
            min-height: 75vh;
            /* Minimal tinggi konten agar footer tidak naik */
        }

        .payment-info dt {
            font-weight: 600;
            color: #495057;
        }

        .payment-info dd {
            color: #212529;
        }

        .highlight-info {
            font-size: 1.1rem;
            font-weight: 500;
        }

        .total-amount {
            font-size: 1.4rem;
            font-weight: bold;
            color: var(--bs-danger, #dc3545);
        }

        .new-expiry-date {
            font-size: 1.1rem;
            font-weight: bold;
            color: var(--bs-success, #198754);
        }

        .form-control.readonly-display {
            background-color: #e9ecef;
            opacity: 1;
            cursor: default;
            border: 1px solid #ced4da;
            padding: .375rem .75rem;
            min-height: calc(1.5em + .75rem + 2px);
        }

        .form-label.fw-semibold {
            color: #343a40;
        }

        .invoice-details-card {
            background-color: #f8f9fa;
            /* Warna latar yang sedikit berbeda untuk detail invoice */
            border: 1px solid #dee2e6;
        }
    </style>
@endpush
@section('content')
    <section class="customer-content-area">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-9 col-md-10">
                    <!-- Header Section with Back Button -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h2 class="fw-semibold m-0">{{ $pageTitle }}</h2>
                        <a href="{{ $existingPayment ? route('customer.payments.index', ['status' => 'unpaid']) : route('customer.dashboard') }}"
                            class="btn btn-outline-secondary rounded-pill px-3 d-flex align-items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor"
                                class="bi bi-arrow-left-short me-1" viewBox="0 0 16 16">
                                <path fill-rule="evenodd"
                                    d="M12 8a.5.5 0 0 1-.5.5H5.707l2.147 2.146a.5.5 0 0 1-.708.708l-3-3a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L5.707 7.5H11.5a.5.5 0 0 1 .5.5z" />
                            </svg>
                            @if ($existingPayment)
                                Kembali ke Daftar Tagihan
                            @else
                                Kembali ke Dashboard
                            @endif
                        </a>
                    </div>
                    <hr class="mb-4">

                    <!-- Alert Messages -->
                    @if (session('info'))
                        <div class="alert alert-info alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
                            {!! session('info') !!}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <!-- Main Card -->
                    <div class="card shadow border-0 rounded-4 mb-5">
                        <div class="card-body p-4 p-lg-5">
                            <form action="{{ route('customer.renewal.process') }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf

                                {{-- Bagian ini ditampilkan jika ini adalah pembayaran untuk INVOICE YANG SUDAH ADA --}}
                                @if ($existingPayment)
                                    <input type="hidden" name="existing_payment_id"
                                        value="{{ $existingPayment->id_payment }}">
                                    <div class="mb-4 pb-3 border-bottom">
                                        <h5 class="text-primary mb-3 fw-bold d-flex align-items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22"
                                                fill="currentColor" class="bi bi-file-earmark-ruled-fill me-2"
                                                viewBox="0 0 16 16">
                                                <path
                                                    d="M9.293 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.707A1 1 0 0 0 13.707 4L10 .293A1 1 0 0 0 9.293 0zM9.5 3.5v-2l3 3h-2a1 1 0 0 1-1-1zM3 5.5a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5zm0 2a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5zm0 2a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5zm0 2a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5zm0 2a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5z" />
                                            </svg>
                                            Detail Invoice #{{ $existingPayment->nomor_invoice }}
                                        </h5>
                                        <div class="card invoice-details-card bg-light rounded-4 p-4 mb-3 border">
                                            <dl class="row payment-info gy-3 mb-0">
                                                <dt class="col-sm-4 text-muted">Pelanggan:</dt>
                                                <dd class="col-sm-8 fw-medium">{{ $customer->nama_customer }}</dd>

                                                <dt class="col-sm-4 text-muted">Periode Tagihan:</dt>
                                                <dd class="col-sm-8 fw-medium">
                                                    {{ \Carbon\Carbon::parse($existingPayment->periode_tagihan_mulai)->locale('id')->translatedFormat('d M Y') }}
                                                    &mdash;
                                                    {{ \Carbon\Carbon::parse($existingPayment->periode_tagihan_selesai)->addDay()->locale('id')->translatedFormat('d M Y') }}
                                                    <small class="d-block text-muted mt-1" style="font-size: 0.8rem;">
                                                        (Layanan hingga akhir hari
                                                        {{ \Carbon\Carbon::parse($existingPayment->periode_tagihan_selesai)->locale('id')->translatedFormat('d M Y') }})
                                                    </small>
                                                </dd>

                                                <dt class="col-sm-4 text-muted">Durasi:</dt>
                                                <dd class="col-sm-8 fw-medium">
                                                    {{ $existingPayment->durasi_pembayaran_bulan }} bulan
                                                </dd>

                                                <dt class="col-sm-4 text-muted">Jumlah Tagihan:</dt>
                                                <dd class="col-sm-8 fw-bold text-danger fs-5">
                                                    Rp {{ number_format($existingPayment->jumlah_tagihan, 0, ',', '.') }}
                                                </dd>
                                            </dl>
                                        </div>
                                    </div>
                                    {{-- Untuk kasus existing payment, durasi sudah fixed dari invoice --}}
                                    <input type="hidden" name="durasi_pembayaran_bulan"
                                        value="{{ $existingPayment->durasi_pembayaran_bulan }}">
                                    <div class="mb-3" style="display:none;"> {{-- Sembunyikan, karena sudah ditentukan oleh invoice --}}
                                        <label class="form-label fw-semibold">Total Biaya</label>
                                        <div
                                            class="form-control readonly-display bg-light rounded-3 total-amount d-flex align-items-center">
                                            Rp {{ number_format($existingPayment->jumlah_tagihan, 0, ',', '.') }}
                                        </div>
                                    </div>
                                @else
                                    {{-- Jika ini PERPANJANGAN BARU --}}
                                    <div class="mb-4 pb-3 border-bottom">
                                        <h5 class="text-primary mb-3 fw-bold d-flex align-items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22"
                                                fill="currentColor" class="bi bi-person-check-fill me-2"
                                                viewBox="0 0 16 16">
                                                <path fill-rule="evenodd"
                                                    d="M15.854 5.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 0 1 .708-.708L12.5 7.793l2.646-2.647a.5.5 0 0 1 .708 0z" />
                                                <path
                                                    d="M1 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1H1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6z" />
                                            </svg>
                                            Informasi Layanan Anda Saat Ini
                                        </h5>
                                        <div class="card bg-light rounded-4 p-4 mb-3 border">
                                            <dl class="row payment-info gy-3 mb-0">
                                                <dt class="col-sm-4 text-muted">Pelanggan:</dt>
                                                <dd class="col-sm-8 fw-medium">{{ $customer->nama_customer }}
                                                    <span class="text-muted">({{ $customer->id_customer }})</span>
                                                </dd>

                                                <dt class="col-sm-4 text-muted">Paket Aktif:</dt>
                                                <dd class="col-sm-8 fw-medium">
                                                    {{ $customer->paket->nama_paket ?? $customer->paket->kecepatan_paket }}
                                                </dd>

                                                <dt class="col-sm-4 text-muted">Harga per Bulan:</dt>
                                                <dd class="col-sm-8 fw-medium">
                                                    Rp {{ number_format($customer->paket->harga_paket, 0, ',', '.') }}
                                                </dd>

                                                @php
                                                    $latestPaymentInfo = $customer->latestPaidPayment();
                                                    $currentServicePeriodInfoText =
                                                        '<span class="text-danger">Tidak ada layanan aktif saat ini.</span>';
                                                    $currentServiceEndDateDbText = null;

                                                    if ($latestPaymentInfo) {
                                                        $currentPeriodStart = \Carbon\Carbon::parse(
                                                            $latestPaymentInfo->periode_tagihan_mulai,
                                                        )->locale('id');
                                                        $currentPeriodEndDbCarbon = \Carbon\Carbon::parse(
                                                            $latestPaymentInfo->periode_tagihan_selesai,
                                                        )->locale('id');
                                                        $currentPeriodEndVisual = $currentPeriodEndDbCarbon
                                                            ->copy()
                                                            ->addDay();

                                                        if (now()->startOfDay()->lte($currentPeriodEndDbCarbon)) {
                                                            $currentServicePeriodInfoText =
                                                                "Aktif untuk periode: <strong class='text-success'>" .
                                                                $currentPeriodStart->translatedFormat('d M Y') .
                                                                ' &mdash; ' .
                                                                $currentPeriodEndVisual->translatedFormat('d M Y') .
                                                                '</strong>.';
                                                            $currentServiceEndDateDbText = $currentPeriodEndDbCarbon->translatedFormat(
                                                                'd M Y',
                                                            );
                                                        } else {
                                                            $currentServicePeriodInfoText =
                                                                'Layanan terakhir Anda telah berakhir pada ' .
                                                                $currentPeriodEndVisual->translatedFormat('d M Y') .
                                                                '.';
                                                            $currentServiceEndDateDbText = $currentPeriodEndDbCarbon->translatedFormat(
                                                                'd M Y',
                                                            );
                                                        }
                                                    }
                                                @endphp

                                                <dt class="col-sm-4 text-muted">Status Layanan:</dt>
                                                <dd class="col-sm-8 highlight-info fw-medium">{!! $currentServicePeriodInfoText !!}</dd>

                                                @if ($nextPeriodStartDate)
                                                    <dt class="col-sm-4 text-muted">Perpanjangan dimulai dari:</dt>
                                                    <dd class="col-sm-8 fw-bold text-primary">
                                                        {{ \Carbon\Carbon::parse($nextPeriodStartDate)->locale('id')->translatedFormat('d F Y') }}
                                                    </dd>
                                                @endif
                                            </dl>
                                        </div>
                                    </div>

                                    <div class="mt-4 mb-4">
                                        <h5 class="text-primary mb-3 fw-bold d-flex align-items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22"
                                                fill="currentColor" class="bi bi-calendar-plus-fill me-2"
                                                viewBox="0 0 16 16">
                                                <path
                                                    d="M4 .5a.5.5 0 0 0-1 0V1H2a2 2 0 0 0-2 2v1h16V3a2 2 0 0 0-2-2h-1V.5a.5.5 0 0 0-1 0V1H4V.5zM16 14V5H0v9a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2zM8.5 8.5V10H10a.5.5 0 0 1 0 1H8.5v1.5a.5.5 0 0 1-1 0V11H6a.5.5 0 0 1 0-1h1.5V8.5a.5.5 0 0 1 1 0z" />
                                            </svg>
                                            Formulir Perpanjangan Layanan
                                        </h5>
                                        <div class="card border rounded-4 p-4 mb-3">
                                            <div class="row g-3">
                                                <div class="col-md-4">
                                                    <label for="durasi_pembayaran_bulan_input"
                                                        class="form-label fw-semibold">Perpanjang Berapa Bulan? <span
                                                            class="text-danger">*</span></label>
                                                    <input type="number" name="durasi_pembayaran_bulan"
                                                        id="durasi_pembayaran_bulan_input"
                                                        class="form-control rounded-3 @error('durasi_pembayaran_bulan') is-invalid @enderror"
                                                        value="{{ old('durasi_pembayaran_bulan', 1) }}" min="1"
                                                        max="24" required>
                                                    @error('durasi_pembayaran_bulan')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label fw-semibold">Diperpanjang Hingga</label>
                                                    <div id="new_expiry_date_display"
                                                        class="form-control bg-light rounded-3 new-expiry-date d-flex align-items-center">
                                                        (Pilih durasi)
                                                    </div>
                                                    <small class="form-text text-muted" id="new_expiry_date_clarification"
                                                        style="font-size: 0.75rem; display: block; min-height: 1.2em;"></small>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label fw-semibold">Perkiraan Total Biaya</label>
                                                    <div id="total_biaya_display"
                                                        class="form-control bg-light rounded-3 total-amount d-flex align-items-center fw-bold text-primary">
                                                        Rp
                                                        {{ number_format($customer->paket->harga_paket * old('durasi_pembayaran_bulan', 1), 0, ',', '.') }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <!-- Pembayaran -->
                                <div class="card border bg-light rounded-4 p-4 mb-4">
                                    <h5 class="text-primary mb-3 fw-bold d-flex align-items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22"
                                            fill="currentColor" class="bi bi-credit-card-fill me-2" viewBox="0 0 16 16">
                                            <path
                                                d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v1H0V4zm0 3v5a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7H0zm3 2h1a1 1 0 0 1 1 1v1a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1v-1a1 1 0 0 1 1-1z" />
                                        </svg>
                                        Informasi Pembayaran
                                    </h5>

                                    <div class="mb-4">
                                        <label for="ewallet_id" class="form-label fw-semibold">Pilih E-Wallet Tujuan
                                            Transfer
                                            <span class="text-danger">*</span></label>
                                        <select name="ewallet_id" id="ewallet_id"
                                            class="form-select rounded-3 @error('ewallet_id') is-invalid @enderror"
                                            required>
                                            <option value="">-- Pilih E-Wallet --</option>
                                            @foreach ($ewallets as $ewallet)
                                                <option value="{{ $ewallet->id_ewallet }}"
                                                    data-nomor="{{ $ewallet->no_ewallet }}"
                                                    data-atasnama="{{ $ewallet->atas_nama }}"
                                                    {{ old('ewallet_id') == $ewallet->id_ewallet ? 'selected' : '' }}>
                                                    {{ $ewallet->nama_ewallet }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('ewallet_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror

                                        <div id="ewallet_details" class="mt-3 p-4 bg-white border rounded-3 shadow-sm"
                                            style="display: none;">
                                            <div class="mb-2">Silakan transfer sejumlah <strong class="text-danger fs-5"
                                                    id="transfer_amount_info"></strong> ke:</div>
                                            <div class="card border-primary border-opacity-25 rounded-3 p-3 mb-0">
                                                <h5 id="detail_nama_ewallet" class="mb-3 fw-bold"></h5>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="mb-2">
                                                            <span class="text-muted">Nomor:</span>
                                                            <div class="fw-bold fs-5" id="detail_no_ewallet"></div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div>
                                                            <span class="text-muted">Atas Nama:</span>
                                                            <div class="fw-bold" id="detail_atas_nama"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-2">
                                        <label for="bukti_pembayaran" class="form-label fw-semibold">Upload Bukti Transfer
                                            <span class="text-danger">*</span></label>
                                        <input type="file" name="bukti_pembayaran" id="bukti_pembayaran"
                                            class="form-control rounded-3 @error('bukti_pembayaran') is-invalid @enderror"
                                            required accept="image/*">
                                        <small class="form-text text-muted">Format: JPG, PNG, GIF. Maksimal 2MB.</small>
                                        @error('bukti_pembayaran')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="d-grid mt-4">
                                    <button type="submit" class="btn btn-primary btn-lg rounded-pill fw-semibold py-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                            fill="currentColor" class="bi bi-send-check-fill me-2" viewBox="0 0 16 16">
                                            <path
                                                d="M15.964.686a.5.5 0 0 0-.65-.65L.767 5.855H.766l-.452.18a.5.5 0 0 0-.082.887l.41.26.001.002 4.995 3.178 3.178 4.995.002.002.26.41a.5.5 0 0 0 .886-.083l6-15Zm-1.833 1.89L6.637 10.07l-.215-.338a.5.5 0 0 0-.154-.154l-.338-.215 7.494-7.494 1.178-.471-.47 1.178Z" />
                                            <path
                                                d="M16 12.5a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0Zm-1.993-1.679a.5.5 0 0 0-.686.172l-1.17 1.95-.547-.547a.5.5 0 0 0-.708.708l.774.773a.75.75 0 0 0 1.174-.144l1.335-2.226a.5.5 0 0 0-.172-.686Z" />
                                        </svg>
                                        @if ($existingPayment)
                                            Konfirmasi Pembayaran
                                        @else
                                            Ajukan Perpanjangan & Kirim Bukti
                                        @endif
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const isExistingPayment = {{ $existingPayment ? 'true' : 'false' }};
            const durasiInput = document.getElementById(
                'durasi_pembayaran_bulan_input'); // Pastikan ID ini ada jika !isExistingPayment
            const totalBiayaDisplay = document.getElementById('total_biaya_display');
            const newExpiryDateDisplay = document.getElementById(
                'new_expiry_date_display'); // Pastikan ID ini ada jika !isExistingPayment
            const newExpiryDateClarification = document.getElementById(
                'new_expiry_date_clarification'); // Pastikan ID ini ada
            const transferAmountInfo = document.getElementById('transfer_amount_info');

            const hargaPaketPerBulan = parseFloat({{ $customer->paket->harga_paket ?? 0 }});

            // Ambil tanggal mulai periode berikutnya dari PHP (dilewatkan ke view)
            // Untuk existingPayment, periode mulai sudah ada di invoice
            const baseStartDateString = isExistingPayment ?
                "{{ $existingPayment ? \Carbon\Carbon::parse($existingPayment->periode_tagihan_mulai)->toDateString() : '' }}" :
                "{{ $nextPeriodStartDate ? \Carbon\Carbon::parse($nextPeriodStartDate)->toDateString() : '' }}";

            // Ambil jumlah tagihan tetap jika ini pembayaran untuk invoice yang sudah ada
            const fixedAmountForExisting = parseFloat({{ $existingPayment->jumlah_tagihan ?? 0 }});
            const fixedDurasiForExisting = parseInt({{ $existingPayment->durasi_pembayaran_bulan ?? 1 }});

            function calculateAndDisplay() {
                let durasi = 1;
                let calculatedTotal = 0;

                if (isExistingPayment) {
                    durasi = fixedDurasiForExisting;
                    calculatedTotal = fixedAmountForExisting;
                    if (totalBiayaDisplay) totalBiayaDisplay.textContent = 'Rp ' + calculatedTotal.toLocaleString(
                        'id-ID');
                    if (transferAmountInfo) transferAmountInfo.textContent = 'Rp ' + calculatedTotal.toLocaleString(
                        'id-ID');
                } else if (durasiInput) { // Hanya proses jika ini perpanjangan baru dan input durasi ada
                    durasi = parseInt(durasiInput.value) || 0;
                    if (hargaPaketPerBulan > 0 && durasi > 0) {
                        calculatedTotal = hargaPaketPerBulan * durasi;
                        if (totalBiayaDisplay) totalBiayaDisplay.textContent = 'Rp ' + calculatedTotal
                            .toLocaleString('id-ID');
                        if (transferAmountInfo) transferAmountInfo.textContent = 'Rp ' + calculatedTotal
                            .toLocaleString('id-ID');
                    } else {
                        if (totalBiayaDisplay) totalBiayaDisplay.textContent = 'Rp 0';
                        if (transferAmountInfo) transferAmountInfo.textContent = 'Rp 0';
                    }
                }


                // Hitung dan Tampilkan Tanggal Berakhir Baru (Hanya untuk perpanjangan baru)
                if (!isExistingPayment && newExpiryDateDisplay && baseStartDateString && durasi > 0) {
                    try {
                        let startDate = new Date(baseStartDateString + 'T00:00:00');

                        let actualEndDate = new Date(startDate);
                        actualEndDate.setMonth(actualEndDate.getMonth() + durasi);
                        actualEndDate.setDate(actualEndDate.getDate() - 1);

                        let visualEndDate = new Date(startDate);
                        visualEndDate.setMonth(visualEndDate.getMonth() + durasi);

                        const options = {
                            year: 'numeric',
                            month: 'long',
                            day: 'numeric'
                        };
                        newExpiryDateDisplay.textContent = visualEndDate.toLocaleDateString('id-ID', options);

                        newExpiryDateDisplay.classList.remove('text-muted');
                        newExpiryDateClarification.style.display = 'block';

                    } catch (e) {
                        console.error("Error parsing date:", e, "Input date string:", baseStartDateString);
                        newExpiryDateDisplay.textContent = '(Error)';
                        newExpiryDateClarification.style.display = 'none';
                        newExpiryDateDisplay.classList.add('text-muted');
                    }
                } else if (!isExistingPayment &&
                    newExpiryDateDisplay) { // Jika perpanjangan baru tapi durasi 0 atau tanggal tidak valid
                    newExpiryDateDisplay.textContent = '(Pilih durasi)';
                    newExpiryDateClarification.style.display = 'none';
                    newExpiryDateDisplay.classList.add('text-muted');
                }
            }

            // Panggil saat load untuk set nilai awal
            calculateAndDisplay();
            if (durasiInput && !isExistingPayment) { // Hanya tambahkan event listener jika ini perpanjangan baru
                durasiInput.addEventListener('input', calculateAndDisplay);
            }


            const ewalletSelect = document.getElementById('ewallet_id');
            const ewalletDetailsDiv = document.getElementById('ewallet_details');
            const detailNamaElem = document.getElementById('detail_nama_ewallet');
            const detailNomorElem = document.getElementById('detail_no_ewallet');
            const detailAtasNamaElem = document.getElementById('detail_atas_nama');

            function updateEwalletDetails() {
                if (ewalletSelect.value) {
                    const selectedOption = ewalletSelect.options[ewalletSelect.selectedIndex];
                    detailNamaElem.textContent = selectedOption.text.trim();
                    detailNomorElem.textContent = selectedOption.getAttribute('data-nomor');
                    detailAtasNamaElem.textContent = selectedOption.getAttribute('data-atasnama');
                    ewalletDetailsDiv.style.display = 'block';
                    calculateAndDisplay(); // Update jumlah transfer saat ewallet dipilih/diganti
                } else {
                    ewalletDetailsDiv.style.display = 'none';
                }
            }

            if (ewalletSelect) {
                ewalletSelect.addEventListener('change', updateEwalletDetails);
                if (ewalletSelect.value) {
                    updateEwalletDetails();
                }
            }
        });
    </script>
@endpush
