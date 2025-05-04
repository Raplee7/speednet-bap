@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('device_models.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="nama_model" class="form-label">Nama Model</label>
                            <input type="text" name="nama_model" class="form-control" id="nama_model" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                        <a href="{{ route('device_models.index') }}" class="btn btn-secondary">Kembali</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
