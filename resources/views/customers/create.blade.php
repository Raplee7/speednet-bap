@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('customers.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="row">
                            <!-- Kolom kiri -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="id_customer" class="form-label">ID Pelanggan</label>
                                    <input type="text" name="id_customer" id="id_customer" class="form-control"
                                        value="{{ old('id_customer', $generatedCustomerId) }}" readonly>
                                </div>
                                <div class="mb-3">
                                    <label for="nama_customer" class="form-label">Nama Pelanggan</label>
                                    <input type="text" name="nama_customer" class="form-control" id="nama_customer"
                                        required>
                                </div>
                                <div class="mb-3">
                                    <label for="nik_customer" class="form-label">NIK</label>
                                    <input type="text" name="nik_customer" class="form-control" id="nik_customer"
                                        required>
                                </div>
                                <div class="mb-3">
                                    <label for="alamat_customer" class="form-label">Alamat</label>
                                    <input type="text" name="alamat_customer" class="form-control" id="alamat_customer"
                                        required>
                                </div>
                                <div class="mb-3">
                                    <label for="wa_customer" class="form-label">WA</label>
                                    <input type="text" name="wa_customer" class="form-control" id="wa_customer" required>
                                </div>
                                <div class="mb-3">
                                    <label for="foto_ktp_customer" class="form-label">Foto KTP</label>
                                    <input type="file" name="foto_ktp_customer" class="form-control"
                                        id="foto_ktp_customer">
                                    <img id="preview_ktp" src="#" alt="Preview KTP" class="img-thumbnail mt-2 d-none"
                                        style="width: 150px;">
                                </div>
                                <div class="mb-3">
                                    <label for="foto_timestamp_rumah" class="form-label">Foto Timestamp Rumah</label>
                                    <input type="file" name="foto_timestamp_rumah" class="form-control"
                                        id="foto_timestamp_rumah">
                                    <img id="preview_rumah" src="#" alt="Preview Rumah"
                                        class="img-thumbnail mt-2 d-none" style="width: 150px;">
                                </div>
                            </div>
                            <!-- Kolom kanan -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="active_user" class="form-label">Active User</label>
                                    <input type="text" name="active_user" class="form-control" id="active_user" required>
                                </div>
                                <div class="mb-3">
                                    <label for="ip_ppoe" class="form-label">IP PPOE</label>
                                    <input type="text" name="ip_ppoe" class="form-control" id="ip_ppoe" required>
                                </div>

                                <div class="mb-3">
                                    <label for="ip_onu" class="form-label">IP ONU</label>
                                    <input type="text" name="ip_onu" class="form-control" id="ip_onu" required>
                                </div>
                                <div class="mb-3">
                                    <label for="paket_id" class="form-label">Paket</label>
                                    <select name="paket_id" class="form-select" required>
                                        @foreach ($pakets as $paket)
                                            <option value="{{ $paket->id_paket }}">{{ $paket->kecepatan_paket }} -
                                                Rp.{{ number_format($paket->harga_paket, 0, ',', '.') }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="device_sn_id" class="form-label">Perangkat</label>
                                    <select name="device_sn_id" class="form-select" required id="device-sn-select">
                                        @foreach ($deviceSns as $deviceSn)
                                            <option value="{{ $deviceSn->id_dsn }}">{{ $deviceSn->nomor }}
                                                ({{ $deviceSn->deviceModel->nama_model }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="tanggal_aktivasi" class="form-label">Tanggal Aktivasi</label>
                                    <input type="date" name="tanggal_aktivasi" class="form-control"
                                        id="tanggal_aktivasi" required>
                                </div>
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select name="status" class="form-select" required>
                                        <option value="belum">Belum</option>
                                        <option value="proses">Proses</option>
                                        <option value="terpasang">Terpasang</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="password" name="password" class="form-control" id="password" required>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 float-end">
                            <button type="submit" class="btn btn-primary">Simpan</button>
                            <a href="{{ route('customers.index') }}" class="btn btn-secondary">Kembali</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Script Choices.js & Preview Gambar -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Inisialisasi dropdown
            new Choices('#device-sn-select', {
                removeItemButton: true,
                searchEnabled: true,
                placeholderValue: 'Pilih Perangkat',
                itemSelectText: '',
            });

            // Preview gambar
            function previewImage(inputId, previewId) {
                const input = document.getElementById(inputId);
                const preview = document.getElementById(previewId);

                input.addEventListener('change', function() {
                    const file = this.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            preview.src = e.target.result;
                            preview.classList.remove('d-none');
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }

            previewImage('foto_ktp_customer', 'preview_ktp');
            previewImage('foto_timestamp_rumah', 'preview_rumah');
        });
    </script>
@endsection
