@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-sm-12">
            @if (session('success'))
                <script>
                    document.addEventListener("DOMContentLoaded", function() {
                        Toastify({
                            text: "✅ {{ session('success') }}",
                            duration: 4000,
                            close: true,
                            gravity: "top",
                            position: "right",
                            style: {
                                background: "linear-gradient(to right, #00b09b, #96c93d)",
                                color: "#fff",
                                borderRadius: "8px",
                                fontSize: "15px",
                                boxShadow: "0 4px 8px rgba(0, 0, 0, 0.2)",
                            },
                            stopOnFocus: true,
                        }).showToast();
                    });
                </script>
            @endif
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <div class="header-title">
                        <h4 class="card-title">Data Serial Number Perangkat</h4>
                    </div>
                    <a href="{{ route('device_sns.create') }}" class="btn btn-primary">Tambah SN</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive mt-4">

                        <table id="basic-table" class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nomor</th>
                                    <th>Model</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($device_sns as $sn)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $sn->nomor }}</td>
                                        <td>{{ $sn->deviceModel->nama_model }}</td>
                                        <td>
                                            @php
                                                $badgeClass = match ($sn->status) {
                                                    'tersedia' => 'bg-success-subtle', // hijau
                                                    'dipakai' => 'bg-warning-subtle', // kuning
                                                    'rusak' => 'bg-danger-subtle', // merah
                                                };
                                            @endphp
                                            <span class=" {{ $badgeClass }} rounded-pill px-3 text-capitalize">
                                                {{ $sn->status }}
                                            </span>
                                        </td>

                                        <td>
                                            <a href="{{ route('device_sns.edit', $sn->id_dsn) }}"
                                                class="btn btn-sm btn-warning">Edit</a>
                                            <form action="{{ route('device_sns.destroy', $sn->id_dsn) }}" method="POST"
                                                class="d-inline delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-sm btn-danger btn-delete"
                                                    data-nama="{{ $sn->nomor }}">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                                @if ($device_sns->isEmpty())
                                    <tr>
                                        <td colspan="5" class="text-center">Data kosong</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const deleteButtons = document.querySelectorAll('.btn-delete');
                deleteButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const form = this.closest('form');
                        const nama = this.getAttribute('data-nama');

                        Swal.fire({
                            title: 'Yakin ingin menghapus?',
                            text: `Serial Number "${nama}" akan dihapus secara permanen.`,
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#e3342f',
                            cancelButtonColor: '#6c757d',
                            confirmButtonText: 'Ya, hapus!',
                            cancelButtonText: 'Batal'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                form.submit();
                            }
                        });
                    });
                });
            });
        </script>
    @endpush
@endsection
