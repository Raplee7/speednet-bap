@extends('layouts.app')
@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card shadow border-0 rounded-4 mb-4">
                    <div class="card-header shadow-sm text-white p-4 rounded-top-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="mb-0 fw-semibold">{{ $customer->nama_customer }}</h3>

                            </div>
                            <div>
                                @php
                                    $badgeClass = match ($customer->status) {
                                        'baru' => 'bg-primary-subtle',
                                        'belum' => 'bg-secondary-subtle',
                                        'proses' => 'bg-warning-subtle',
                                        'terpasang' => 'bg-success-subtle',
                                    };
                                @endphp
                                <span class=" {{ $badgeClass }} rounded px-4 py-2 text-capitalize">
                                    {{ $customer->status }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        <div class="row">
                            <div class="col-lg-8">
                                <h5 class="border-bottom pb-2 mb-4">Informasi Pelanggan</h5>

                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <div class="card h-100 border-0">
                                            <div class="card-body">
                                                <h6 class="card-title mb-3 text-primary fw-bold">Data Pribadi</h6>
                                                <div class="mb-2">
                                                    <label class="text-muted small mb-1">NIK</label>
                                                    <p class="mb-2 fw-semibold">{{ $customer->nik_customer }}</p>
                                                </div>
                                                <div class="mb-2">
                                                    <label class="text-muted small mb-1">Alamat</label>
                                                    <p class="mb-2 fw-semibold">{{ $customer->alamat_customer }}</p>
                                                </div>
                                                <div class="mb-2">
                                                    <label class="text-muted small mb-1">No. WhatsApp</label>
                                                    <p class="mb-0 fw-semibold">
                                                        <a href="https://wa.me/{{ $customer->wa_customer }}"
                                                            class="text-success">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="16"
                                                                height="16" fill="currentColor" class="bi bi-whatsapp"
                                                                viewBox="0 0 16 16">
                                                                <path
                                                                    d="M13.601 2.326A7.85 7.85 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.9 7.9 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.9 7.9 0 0 0 13.6 2.326zM7.994 14.521a6.6 6.6 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.56 6.56 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592m3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.73.73 0 0 0-.529.247c-.182.198-.691.677-.691 1.654s.71 1.916.81 2.049c.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232" />
                                                            </svg> {{ $customer->wa_customer }}
                                                        </a>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="card h-100 border-0">
                                            <div class="card-body">
                                                <h6 class="card-title mb-3 fw-bold text-primary">Informasi Berlangganan
                                                </h6>
                                                <div class="mb-2">
                                                    <label class="text-muted small mb-1">Tanggal Aktivasi</label>
                                                    <p class="mb-2 fw-semibold">
                                                        {{ \Carbon\Carbon::parse($customer->tanggal_aktivasi)->format('d M Y') }}
                                                    </p>
                                                </div>
                                                <div class="mb-2">
                                                    <label class="text-muted small mb-1">Paket</label>
                                                    <p class="mb-2 fw-semibold">
                                                        {{ $customer->paket->kecepatan_paket ?? '-' }}
                                                        <span class="badge bg-info ms-2">
                                                            Rp{{ number_format($customer->paket->harga_paket ?? 0, 0, ',', '.') }}
                                                        </span>
                                                    </p>
                                                </div>
                                                <div class="mb-2">
                                                    <label class="text-muted small mb-1">Active User</label>
                                                    <p class="mb-0 fw-semibold">{{ $customer->active_user }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="card h-100 border-0">
                                            <div class="card-body">
                                                <h6 class="card-title fw-bold mb-3 text-primary">Informasi Perangkat
                                                </h6>
                                                <div class="mb-2">
                                                    <label class="text-muted small mb-1">Model Perangkat</label>
                                                    <p class="mb-2 fw-semibold">
                                                        {{ $customer->deviceSn->deviceModel->nama_model ?? '-' }}
                                                    </p>
                                                </div>
                                                <div class="mb-2">
                                                    <label class="text-muted small mb-1">Serial Number</label>
                                                    <p class="mb-0 fw-semibold">{{ $customer->deviceSn->nomor ?? '-' }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="card h-100 border-0">
                                            <div class="card-body">
                                                <h6 class="card-title fw-bold mb-3 text-primary">Network Configuration
                                                </h6>
                                                <div class="mb-2">
                                                    <label class="text-muted small mb-1">IP PPPoE</label>
                                                    <p class="mb-2 fw-semibold">
                                                        <span class="badge bg-dark">{{ $customer->ip_ppoe }}</span>
                                                    </p>
                                                </div>
                                                <div class="mb-2">
                                                    <label class="text-muted small mb-1">IP ONU</label>
                                                    <p class="mb-0 fw-semibold">
                                                        <span class="badge bg-dark">{{ $customer->ip_onu }}</span>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-4 mt-4 mt-lg-0">
                                <h5 class="border-bottom pb-2 mb-4">Dokumen Pelanggan</h5>

                                <div class="card mb-4 border-0 shadow-sm">
                                    <div class="card-header bg-light d-flex align-items-center"
                                        style="height: 40px; padding-top: 0; padding-bottom: 0;">
                                        <h6 class="mb-0 w-100 text-center fw-bold" style="line-height: 1;">Foto KTP</h6>
                                    </div>

                                    <div class="card-body p-3 text-center">
                                        @if ($customer->foto_ktp_customer)
                                            <img src="{{ asset('storage/' . $customer->foto_ktp_customer) }}"
                                                alt="Foto KTP" class="img-fluid rounded shadow-sm"
                                                style="max-height: 200px;">
                                            <div class="mt-3">
                                                <a href="{{ asset('storage/' . $customer->foto_ktp_customer) }}"
                                                    class="btn btn-sm btn-outline-primary" target="_blank">
                                                    Lihat Full
                                                </a>
                                            </div>
                                        @else
                                            <div class="text-center py-5">
                                                <i class="bi bi-card-image text-muted" style="font-size: 3rem;"></i>
                                                <p class="text-muted mt-2">Foto KTP belum diunggah.</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="card border-0 shadow-sm">
                                    <div class="card-header bg-light d-flex align-items-center"
                                        style="height: 40px; padding-top: 0; padding-bottom: 0;">
                                        <h6 class="mb-0 w-100 text-center fw-bold" style="line-height: 1;">Foto Rumah
                                        </h6>
                                    </div>
                                    <div class="card-body p-3 text-center">
                                        @if ($customer->foto_timestamp_rumah)
                                            <img src="{{ asset('storage/' . $customer->foto_timestamp_rumah) }}"
                                                alt="Foto Rumah" class="img-fluid rounded shadow-sm"
                                                style="max-height: 200px;">
                                            <div class="mt-3">
                                                <a href="{{ asset('storage/' . $customer->foto_timestamp_rumah) }}"
                                                    class="btn btn-sm btn-outline-primary" target="_blank">
                                                    Lihat Full
                                                </a>
                                            </div>
                                        @else
                                            <div class="text-center py-5">
                                                <i class="bi bi-house text-muted" style="font-size: 3rem;"></i>
                                                <p class="text-muted mt-2">Foto rumah belum diunggah.</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer bg-light p-4 rounded-bottom-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small">
                                <i class="bi bi-info-circle"></i>
                                Terakhir diperbarui:
                                {{ \Carbon\Carbon::parse($customer->updated_at)->format('d M Y H:i') }}
                            </span>
                            <div>
                                <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                        fill="currentColor" class="bi bi-arrow-left" viewBox="0 0 16 16">
                                        <path fill-rule="evenodd"
                                            d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8" />
                                    </svg> Kembali
                                </a>
                                <a href="{{ route('customers.edit', $customer->id_customer) }}"
                                    class="btn btn-warning ms-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                        fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16">
                                        <path
                                            d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z" />
                                        <path fill-rule="evenodd"
                                            d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z" />
                                    </svg> Edit
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
