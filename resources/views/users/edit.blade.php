@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('users.update', $user->id_user) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="nama_user" class="form-label">Nama User</label>
                            <input type="text" name="nama_user" class="form-control" id="nama_user"
                                value="{{ $user->nama_user }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" id="email"
                                value="{{ $user->email }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="role_user" class="form-label">Role</label>
                            <select name="role_user" id="role_user" class="form-select" required>
                                <option value="admin" {{ $user->role_user === 'admin' ? 'selected' : '' }}>Admin</option>
                                <option value="kasir" {{ $user->role_user === 'kasir' ? 'selected' : '' }}>Kasir</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Update</button>
                        <a href="{{ route('users.index') }}" class="btn btn-secondary">Kembali</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
