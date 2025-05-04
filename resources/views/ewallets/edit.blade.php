@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('ewallets.update', $ewallet->id_ewallet) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="nama_ewallet" class="form-label">Nama E-Wallet</label>
                            <input type="text" name="nama_ewallet" class="form-control" id="nama_ewallet"
                                value="{{ $ewallet->nama_ewallet }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="no_ewallet" class="form-label">Nomor E-Wallet</label>
                            <input type="text" name="no_ewallet" class="form-control" id="no_ewallet"
                                value="{{ $ewallet->no_ewallet }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="atas_nama" class="form-label">Atas Nama</label>
                            <input type="text" name="atas_nama" class="form-control" id="atas_nama"
                                value="{{ $ewallet->atas_nama }}" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Update</button>
                        <a href="{{ route('ewallets.index') }}" class="btn btn-secondary">Kembali</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
