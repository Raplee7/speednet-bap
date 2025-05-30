@extends('layouts.auth_layout')

@section('title', 'Lupa Password')

@section('content_auth')
    <h3 class="text-center font-weight-light my-4">Lupa Password Anda?</h3>


    {{-- Menampilkan pesan status (misalnya link berhasil dikirim) --}}
    @if (session('status'))
        <div class="alert alert-success" role="alert">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="form-group mb-3"> {{-- Menggunakan form-group seperti di loginmu --}}
            <label for="email" class="form-label">Alamat Email</label>
            <input class="form-control @error('email') is-invalid @enderror" id="email" type="email" name="email"
                value="{{ old('email') }}" required autofocus />
            @error('email')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>

        <div class="d-flex align-items-center justify-content-between mt-4 mb-0">
            <a class="small" href="{{ route('login') }}">Kembali ke Login</a> {{-- Sesuaikan route loginmu --}}
            <button type="submit" class="btn btn-primary">Kirim Link Reset</button>
        </div>
    </form>
@endsection
