@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('device_models.update', $device_model->id_dm) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="nama_model" class="form-label">Nama Model</label>
                            <input type="text" name="nama_model" class="form-control" id="nama_model"
                                value="{{ $device_model->nama_model }}" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Update</button>
                        <a href="{{ route('device_models.index') }}" class="btn btn-secondary">Kembali</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
