@extends('layouts.auth_layout')

@section('title', 'Reset Password')

@section('content_auth')
    <h3 class="text-center font-weight-light my-4">Reset Password Anda</h3>

    <form method="POST" action="{{ route('password.store') }}">
        @csrf

        {{-- Input tersembunyi untuk token --}}
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        {{-- Alamat Email --}}
        <div class="form-group mb-3">
            <label for="email" class="form-label">Alamat Email</label>
            <input class="form-control @error('email') is-invalid @enderror" id="email" type="email" name="email"
                value="{{ $request->email ?? old('email') }}" placeholder="name@example.com" required autofocus />
            @error('email')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>

        {{-- Password Baru --}}
        <div class="form-group mb-3">
            <label for="password" class="form-label">Password Baru</label>
            <input class="form-control @error('password') is-invalid @enderror" id="password" type="password"
                name="password"required />
            @error('password')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>

        {{-- Konfirmasi Password Baru --}}
        <div class="form-group mb-3">
            <label for="password_confirmation" class="form-label">Konfirmasi Password Baru</label>
            <input class="form-control" id="password_confirmation" type="password" name="password_confirmation" required />
        </div>

        <div class="d-flex align-items-center justify-content-end mt-4 mb-0"> {{-- Mengubah ke justify-content-end --}}
            <button type="submit" class="btn btn-primary">Reset Password</button>
        </div>
    </form>
@endsection
