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
                        <h4 class="card-title">Data E-Wallet</h4>
                    </div>
                    <a href="{{ route('ewallets.create') }}" class="btn btn-primary">Tambah E-Wallet</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive mt-4">
                        <table id="datatable" data-toggle="data-table" class="table table-hover mb-0" role="grid">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama E-Wallet</th>
                                    <th>Nomor</th>
                                    <th>Atas Nama</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($ewallets as $ewallet)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $ewallet->nama_ewallet }}</td>
                                        <td>{{ $ewallet->no_ewallet }}</td>
                                        <td>{{ $ewallet->atas_nama }}</td>
                                        <td>
                                            <form action="{{ route('ewallets.toggle-status', $ewallet->id_ewallet) }}"
                                                method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit"
                                                    class="btn btn-sm {{ $ewallet->is_active ? 'btn-success' : 'btn-secondary' }}">
                                                    {{ $ewallet->is_active ? 'Aktif' : 'Nonaktif' }}
                                                </button>
                                            </form>
                                        </td>
                                        <td>
                                            <a href="{{ route('ewallets.edit', $ewallet->id_ewallet) }}"
                                                class="btn btn-sm btn-warning">Edit</a>
                                            <form action="{{ route('ewallets.destroy', $ewallet->id_ewallet) }}"
                                                method="POST" class="d-inline delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-sm btn-danger btn-delete"
                                                    data-nama="{{ $ewallet->nama_ewallet }}">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach

                                @if ($ewallets->isEmpty())
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
                            text: `E-Wallet "${nama}" akan dihapus secara permanen.`,
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
