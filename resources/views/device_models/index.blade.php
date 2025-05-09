@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-sm-12">
            @if (session('success'))
                @push('scripts')
                    <script>
                        toastr.success("{{ session('success') }}");
                    </script>
                @endpush
            @endif
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <div class="header-title">
                        <h4 class="card-title">Data Model Perangkat</h4>
                    </div>
                    <a href="{{ route('device_models.create') }}" class="btn btn-primary">Tambah Model</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive mt-4">
                        <table id="datatable" data-toggle="data-table" class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Model</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($device_models as $model)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $model->nama_model }}</td>
                                        <td>
                                            <a href="{{ route('device_models.edit', $model->id_dm) }}"
                                                class="btn btn-sm btn-warning">Edit</a>
                                            <form action="{{ route('device_models.destroy', $model->id_dm) }}"
                                                method="POST" class="d-inline delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-sm btn-danger btn-delete"
                                                    data-nama="{{ $model->nama_model }}">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                                @if ($device_models->isEmpty())
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
                            text: `Model "${nama}" akan dihapus secara permanen.`,
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
