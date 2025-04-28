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
                        <h4 class="card-title">Data Paket</h4>
                    </div>
                    <a href="{{ route('pakets.create') }}" class="btn btn-primary">Tambah Paket</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive mt-4">
                        <table id="basic-table" class="table table-striped mb-0" role="grid">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Kecepatan Paket</th>
                                    <th>Harga Paket</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($pakets as $paket)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $paket->kecepatan_paket }}</td>
                                        <td>Rp {{ number_format($paket->harga_paket, 0, ',', '.') }}</td>
                                        <td>
                                            <a href="{{ route('pakets.edit', $paket->id_pakets) }}"
                                                class="btn btn-sm btn-warning">Edit</a>
                                            <form action="{{ route('pakets.destroy', $paket->id_pakets) }}" method="POST"
                                                style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger"
                                                    onclick="return confirm('Yakin ingin menghapus paket ini?')">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                                @if ($pakets->isEmpty())
                                    <tr>
                                        <td colspan="4" class="text-center">Data kosong</td>
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
