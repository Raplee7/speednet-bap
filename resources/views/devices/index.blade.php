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
                            gravity: "top", // or "bottom"
                            position: "right", // or "left", "center"
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
                        <h4 class="card-title">Data Perangkat</h4>
                    </div>
                    <a href="{{ route('devices.create') }}" class="btn btn-primary">Tambah</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive mt-4">
                        <table id="basic-table" class="table table-striped mb-0" role="grid">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Perangkat</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($devices as $device)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $device->nama_perangkat }}</td>
                                        <td>
                                            <a href="{{ route('devices.edit', $device->id_devices) }}"
                                                class="btn btn-sm btn-warning">Edit</a>
                                            <form action="{{ route('devices.destroy', $device->id_devices) }}"
                                                method="POST" class="d-inline delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-sm btn-danger btn-delete"
                                                    data-nama="{{ $device->nama_perangkat }}">Hapus</button>
                                            </form>

                                        </td>
                                    </tr>
                                @endforeach
                                @if ($devices->isEmpty())
                                    <tr>
                                        <td colspan="3" class="text-center">Data kosong</td>
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
                            text: `Perangkat "${nama}" akan dihapus secara permanen.`,
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
