@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('device_sns.update', $device_sn->id_dsn) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="nomor" class="form-label">Nomor SN</label>
                            <input type="text" name="nomor" class="form-control" id="nomor"
                                value="{{ $device_sn->nomor }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="model_id" class="form-label">Model</label>
                            <select name="model_id" id="model_id" class="form-control" required>
                                @foreach ($device_models as $model)
                                    <option value="{{ $model->id_dm }}"
                                        {{ $device_sn->model_id == $model->id_dm ? 'selected' : '' }}>
                                        {{ $model->nama_model }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" id="status" class="form-control" required>
                                <option value="tersedia" {{ $device_sn->status == 'tersedia' ? 'selected' : '' }}>Tersedia
                                </option>
                                <option value="dipakai" {{ $device_sn->status == 'dipakai' ? 'selected' : '' }}>Dipakai
                                </option>
                                <option value="rusak" {{ $device_sn->status == 'rusak' ? 'selected' : '' }}>Rusak</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Update</button>
                        <a href="{{ route('device_sns.index') }}" class="btn btn-secondary">Kembali</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
