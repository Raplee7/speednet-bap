@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('pakets.update', $paket->id_paket) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="kecepatan_paket" class="form-label">Kecepatan Paket</label>
                            <input type="text" name="kecepatan_paket" class="form-control" id="kecepatan_paket"
                                value="{{ $paket->kecepatan_paket }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="harga_paket" class="form-label">Harga Paket</label>
                            <input type="number" name="harga_paket" class="form-control" id="harga_paket"
                                value="{{ $paket->harga_paket }}" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Update</button>
                        <a href="{{ route('pakets.index') }}" class="btn btn-secondary">Kembali</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
