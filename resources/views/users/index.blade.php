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
                    <h4 class="card-title">Data User</h4>
                    <a href="{{ route('users.create') }}" class="btn btn-primary">Tambah User</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive mt-4">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($users as $user)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $user->nama_user }}</td>
                                        <td>{{ $user->username_user }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>
                                            @php
                                                $badgeClass = match ($user->role) {
                                                    'admin' => 'bg-success-subtle',
                                                    'kasir' => 'bg-primary-subtle',
                                                };
                                            @endphp
                                            <span class=" {{ $badgeClass }} rounded-pill px-3 text-capitalize">
                                                {{ $user->role }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('users.edit', $user->id_user) }}"
                                                class="btn btn-sm btn-warning">Edit</a>
                                            <form action="{{ route('users.destroy', $user->id_user) }}" method="POST"
                                                class="d-inline delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-sm btn-danger btn-delete"
                                                    data-nama="{{ $user->nama_user }}">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                                @if ($users->isEmpty())
                                    <tr>
                                        <td colspan="6" class="text-center">Data kosong</td>
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
                            text: `User "${nama}" akan dihapus secara permanen.`,
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
