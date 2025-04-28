@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-sm-12">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
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
                                                method="POST" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger"
                                                    onclick="return confirm('Yakin ingin menghapus device ini?')">Hapus</button>
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
@endsection
