@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('device_sns.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="nomor" class="form-label">Nomor SN</label>
                            <input type="text" name="nomor" class="form-control" id="nomor" required>
                        </div>
                        <div class="mb-3">
                            <label for="model_id" class="form-label">Model</label>
                            <select name="model_id" id="model_id" class="form-control" required>
                                <option value="">-- Pilih Model --</option>
                                @foreach ($device_models as $model)
                                    <option value="{{ $model->id_dm }}">{{ $model->nama_model }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" id="status" class="form-control" required>
                                <option value="tersedia">Tersedia</option>
                                <option value="dipakai">Dipakai</option>
                                <option value="rusak">Rusak</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                        <a href="{{ route('device_sns.index') }}" class="btn btn-secondary">Kembali</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
