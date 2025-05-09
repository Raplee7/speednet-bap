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
                    <h4 class="card-title">Data Pelanggan</h4>
                    <a href="{{ route('customers.create') }}" class="btn btn-primary">Tambah Pelanggan</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive mt-4">
                        <table class="table table-hover mb-0" id="datatable" data-toggle="data-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>ID</th>
                                    <th>Nama</th>
                                    <th>WA</th>
                                    <th>Aktif User</th>
                                    <th>Paket</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($customers as $customer)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $customer->id_customer }}</td>
                                        <td>{{ $customer->nama_customer }}</td>
                                        <td><a href="https://wa.me/{{ $customer->wa_customer }}" class="text-success">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                    fill="currentColor" class="bi bi-whatsapp" viewBox="0 0 16 16">
                                                    <path
                                                        d="M13.601 2.326A7.85 7.85 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.9 7.9 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.9 7.9 0 0 0 13.6 2.326zM7.994 14.521a6.6 6.6 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.56 6.56 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592m3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.73.73 0 0 0-.529.247c-.182.198-.691.677-.691 1.654s.71 1.916.81 2.049c.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232" />
                                                </svg> {{ $customer->wa_customer }}
                                            </a></td>
                                        <td>{{ $customer->active_user }}</td>
                                        <td>{{ $customer->paket->kecepatan_paket ?? '-' }}</td>
                                        <td>
                                            @php
                                                $badgeClass = match ($customer->status) {
                                                    'baru' => 'bg-primary-subtle',
                                                    'belum' => 'bg-secondary-subtle',
                                                    'proses' => 'bg-warning-subtle',
                                                    'terpasang' => 'bg-success-subtle',
                                                };
                                            @endphp
                                            <span class=" {{ $badgeClass }} rounded-pill px-3 text-capitalize">
                                                {{ $customer->status }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('customers.show', $customer->id_customer) }}"
                                                class="btn btn-info btn-sm">Detail</a>
                                            <a href="{{ route('customers.edit', $customer->id_customer) }}"
                                                class="btn btn-sm btn-warning">Edit</a>
                                            <form action="{{ route('customers.destroy', $customer->id_customer) }}"
                                                method="POST" class="d-inline delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-sm btn-danger btn-delete"
                                                    data-nama="{{ $customer->nama_customer }}">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                                @if ($customers->isEmpty())
                                    <tr>
                                        <td colspan="8" class="text-center">Data kosong</td>
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
                            text: `Pelanggan "${nama}" akan dihapus secara permanen.`,
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
