@extends('layouts.app')

@section('content')
    <div class="row justify-content-center">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('payments.store') }}" method="POST">
                        @csrf

                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif

                        <div class="mb-3">
                            <label for="customer_id" class="form-label">Pilih Pelanggan <span
                                    class="text-danger">*</span></label>
                            <select name="customer_id" id="customer_id" class="form-select" required>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id_customer }}"
                                        {{ old('customer_id') == $customer->id_customer ? 'selected' : '' }}
                                        data-tanggal-aktivasi="{{ $customer->tanggal_aktivasi ? \Carbon\Carbon::parse($customer->tanggal_aktivasi)->format('d') : '' }}"
                                        data-nama-paket="{{ $customer->paket ? $customer->paket->nama_paket ?? $customer->paket->kecepatan_paket : '' }}"
                                        data-harga-paket="{{ $customer->paket ? $customer->paket->harga_paket : '' }}">
                                        {{ $customer->nama_customer }} ({{ $customer->id_customer }})
                                        @if ($customer->paket)
                                            - Paket:
                                            {{ $customer->paket->nama_paket ?? $customer->paket->kecepatan_paket }}
                                        @else
                                            - (Paket Belum Diatur)
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="durasi_pembayaran_bulan" class="form-label">Durasi Pembayaran (Bulan) <span
                                            class="text-danger">*</span></label>
                                    <input type="number" name="durasi_pembayaran_bulan" class="form-control"
                                        id="durasi_pembayaran_bulan" value="{{ old('durasi_pembayaran_bulan', 1) }}"
                                        min="1" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="bulan_tahun_tagihan" class="form-label">Untuk Bulan & Tahun Tagihan <span
                                            class="text-danger">*</span></label>
                                    <input type="month" name="bulan_tahun_tagihan" class="form-control"
                                        id="bulan_tahun_tagihan" value="{{ old('bulan_tahun_tagihan', date('Y-m')) }}"
                                        required>
                                    <small class="form-text text-muted" id="info_tanggal_penagihan">Hari penagihan akan
                                        disesuaikan.</small>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="jumlah_tagihan_display" class="form-label">Perkiraan Jumlah Tagihan</label>
                            <input type="text" id="jumlah_tagihan_display" class="form-control" readonly disabled>
                        </div>

                        <div class="mb-3">
                            <label for="catatan_admin" class="form-label">Catatan Admin (Opsional)</label>
                            <textarea name="catatan_admin" id="catatan_admin" class="form-control" rows="3">{{ old('catatan_admin') }}</textarea>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" name="bayar_tunai_sekarang" class="form-check-input"
                                id="bayar_tunai_sekarang" value="1"
                                {{ old('bayar_tunai_sekarang') ? 'checked' : '' }}>
                            <label class="form-check-label" for="bayar_tunai_sekarang">
                                Langsung Lunas (Pembayaran Tunai Diterima Sekarang)
                            </label>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-end">
                            <a href="{{ route('payments.index') }}" class="btn btn-secondary me-2">

                                Kembali
                            </a>
                            <button type="submit" class="btn btn-primary">

                                Simpan Tagihan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const customerSelect = document.getElementById('customer_id');
            const durasiInput = document.getElementById('durasi_pembayaran_bulan');
            const infoTanggalPenagihan = document.getElementById('info_tanggal_penagihan');
            const jumlahTagihanDisplay = document.getElementById('jumlah_tagihan_display');

            if (customerSelect) {
                const choicesInstance = new Choices(customerSelect, {
                    removeItemButton: true,
                    searchEnabled: true,
                    placeholderValue: 'Ketik untuk mencari atau pilih pelanggan...',
                    itemSelectText: '',
                    allowHTML: true,
                });

                // Fungsi untuk update info tanggal dan perkiraan tagihan
                function updateBillingInfo() {
                    const selectedOption = customerSelect.options[customerSelect.selectedIndex];
                    let tanggalAktivasiHari = '';
                    let hargaPaket = 0;
                    let namaPaket = '';

                    if (selectedOption && selectedOption.value !== "") {
                        tanggalAktivasiHari = selectedOption.getAttribute('data-tanggal-aktivasi');
                        hargaPaket = parseFloat(selectedOption.getAttribute('data-harga-paket')) || 0;
                        namaPaket = selectedOption.getAttribute('data-nama-paket');
                    }

                    if (tanggalAktivasiHari) {
                        infoTanggalPenagihan.textContent =
                            `Hari penagihan akan disesuaikan dengan Tanggal Aktivasi pelanggan (setiap tanggal ${tanggalAktivasiHari}).`;
                    } else {
                        infoTanggalPenagihan.textContent = 'Hari penagihan akan disesuaikan.';
                    }

                    const durasi = parseInt(durasiInput.value) || 0;
                    if (hargaPaket > 0 && durasi > 0) {
                        const totalTagihan = hargaPaket * durasi;
                        jumlahTagihanDisplay.value = `Rp ${totalTagihan.toLocaleString('id-ID')}`;
                        if (namaPaket) {
                            jumlahTagihanDisplay.value += ` (Paket: ${namaPaket})`;
                        }
                    } else {
                        jumlahTagihanDisplay.value = 'Pilih pelanggan dan isi durasi untuk melihat perkiraan.';
                    }
                }

                // Panggil saat halaman load jika ada old value
                updateBillingInfo();

                // Event listener untuk perubahan pilihan pelanggan dan durasi
                customerSelect.addEventListener('change', updateBillingInfo);
                durasiInput.addEventListener('input', updateBillingInfo);
            }
        });
    </script>
@endpush
