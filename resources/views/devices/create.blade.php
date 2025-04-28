@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('devices.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="nama_perangkat" class="form-label">Nama Perangkat</label>
                            <input type="text" name="nama_perangkat" class="form-control" id="nama_perangkat" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                        <a href="{{ route('devices.index') }}" class="btn btn-secondary">Kembali</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
