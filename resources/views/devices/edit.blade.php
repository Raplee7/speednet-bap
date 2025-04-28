@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('devices.update', $device->id_devices) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="nama_perangkat" class="form-label">Nama Perangkat</label>
                            <input type="text" name="nama_perangkat" class="form-control" id="nama_perangkat"
                                value="{{ $device->nama_perangkat }}" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Update</button>
                        <a href="{{ route('devices.index') }}" class="btn btn-secondary">Kembali</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
