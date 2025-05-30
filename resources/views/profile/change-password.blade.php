@extends('layouts.app')
@section('content')
    <div class="card mb-4">
        <div class="card-body">
            {{-- Menampilkan pesan sukses jika ada --}}
            @if (session('success'))
                @push('scripts')
                    <script>
                        toastr.success("{{ session('success') }}");
                    </script>
                @endpush
            @endif

            {{-- Menampilkan pesan error umum jika ada (selain validasi field) --}}
            @if ($errors->any() && !$errors->has('current_password') && !$errors->has('new_password'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Oops!</strong> Terjadi kesalahan:
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <form method="POST" action="{{ route('profile.password.update') }}">
                @csrf {{-- Token CSRF untuk keamanan --}}

                <div class="mb-3">
                    <label for="current_password" class="form-label">Password Saat Ini</label>
                    <input type="password" class="form-control @error('current_password') is-invalid @enderror"
                        id="current_password" name="current_password" required>
                    @error('current_password')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="new_password" class="form-label">Password Baru</label>
                    <input type="password" class="form-control @error('new_password') is-invalid @enderror"
                        id="new_password" name="new_password" required>
                    @error('new_password')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                    {{-- 
                        <div id="passwordHelpBlock" class="form-text">
                            Password baru minimal 8 karakter, mengandung huruf besar, huruf kecil, angka, dan simbol.
                        </div>
                        --}}
                </div>

                <div class="mb-3">
                    <label for="new_password_confirmation" class="form-label">Konfirmasi Password Baru</label>
                    <input type="password" class="form-control" id="new_password_confirmation"
                        name="new_password_confirmation" required>
                    {{-- Error untuk konfirmasi biasanya sudah ditangani oleh validasi 'confirmed' di field 'new_password' --}}
                </div>

                <button type="submit" class="btn btn-primary">
                    Update Password
                </button>
            </form>
        </div>
    </div>
@endsection
