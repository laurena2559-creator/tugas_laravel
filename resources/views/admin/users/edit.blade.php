@extends('admin.layouts.app')
@section('css')
{{-- CSS Tambahan --}}
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <h5 class="card-title fw-semibold mb-4">Ubah Data {{ $title }}</h5>
        <div class="card">
            <div class="card-body">
                <form action="{{ route('users.update', $user->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label for="name" class="form-label">Nama</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" 
                        id="name" placeholder="Nama Lengkap" value="{{ old('name', $user->name) }}">
                        @error('name')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                    
                    {{-- Email --}}
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" 
                        id="email" placeholder="contoh@email.com" value="{{ old('email', $user->email) }}">
                        @error('email')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                    
                    {{-- Password --}}
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" 
                        name="password" id="password" placeholder="Kosongkan jika tidak ingin diubah">
                        @error('password')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                    
                    {{-- Role --}}
                    <div class="mb-3">
                        <label for="role" class="form-label">Role</label>
                        <select class="form-select @error('role') is-invalid @enderror" name="role" id="role">
                            <option value="Siswa" {{ old('role', $user->role) == 'Siswa' ? 'selected' : '' }}>Siswa</option>
                        </select>
                        @error('role')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    {{-- Kelas --}}
                    <div class="mb-3">
                        <label for="kelas" class="form-label">Kelas</label>
                        <select name="kelas" id="kelas" class="form-select @error('kelas') is-invalid @enderror">
                            <option value="">- Pilih Kelas -</option>
                            <option {{ old('kelas', $user->kelas) == 'X LPS 1' ? 'selected' : '' }}>X LPS 1</option>
                            <option {{ old('kelas', $user->kelas) == 'X LPS 2' ? 'selected' : '' }}>X LPS 2</option>
                            <option {{ old('kelas', $user->kelas) == 'X DKV 1' ? 'selected' : '' }}>X DKV 1</option>
                            <option {{ old('kelas', $user->kelas) == 'X DKV 2' ? 'selected' : '' }}>X DKV 2</option>
                            <option {{ old('kelas', $user->kelas) == 'X APHP 1' ? 'selected' : '' }}>X APHP 1</option>
                            <option {{ old('kelas', $user->kelas) == 'X APHP 2' ? 'selected' : '' }}>X APHP 2</option>
                            <option {{ old('kelas', $user->kelas) == 'X TB 1' ? 'selected' : '' }}>X TB 1</option>
                            <option {{ old('kelas', $user->kelas) == 'X TB 2' ? 'selected' : '' }}>X TB 2</option>
                            <option {{ old('kelas', $user->kelas) == 'X RPL 1' ? 'selected' : '' }}>X RPL 1</option>
                            <option {{ old('kelas', $user->kelas) == 'X RPL 2' ? 'selected' : '' }}>X RPL 2</option>
                            <option {{ old('kelas', $user->kelas) == 'X RPL 3' ? 'selected' : '' }}>X RPL 3</option>
                            <option {{ old('kelas', $user->kelas) == 'XI LPS 1' ? 'selected' : '' }}>XI LPS 1</option>
                            <option {{ old('kelas', $user->kelas) == 'XI LPS 2' ? 'selected' : '' }}>XI LPS 2</option>
                            <option {{ old('kelas', $user->kelas) == 'XI DKV 1' ? 'selected' : '' }}>XI DKV 1</option>
                            <option {{ old('kelas', $user->kelas) == 'XI DKV 2' ? 'selected' : '' }}>XI DKV 2</option>
                            <option {{ old('kelas', $user->kelas) == 'XI APHP 1' ? 'selected' : '' }}>XI APHP 1</option>
                            <option {{ old('kelas', $user->kelas) == 'XI APHP 2' ? 'selected' : '' }}>XI APHP 2</option>
                            <option {{ old('kelas', $user->kelas) == 'XI TB 1' ? 'selected' : '' }}>XI TB 1</option>
                            <option {{ old('kelas', $user->kelas) == 'XI TB 2' ? 'selected' : '' }}>XI TB 2</option>
                            <option {{ old('kelas', $user->kelas) == 'XI RPL 1' ? 'selected' : '' }}>XI RPL 1</option>
                            <option {{ old('kelas', $user->kelas) == 'XI RPL 2' ? 'selected' : '' }}>XI RPL 2</option>
                            <option {{ old('kelas', $user->kelas) == 'XI RPL 3' ? 'selected' : '' }}>XI RPL 3</option>
                            <option {{ old('kelas', $user->kelas) == 'XII LPS 1' ? 'selected' : '' }}>XII LPS 1</option>
                            <option {{ old('kelas', $user->kelas) == 'XII LPS 2' ? 'selected' : '' }}>XII LPS 2</option>
                            <option {{ old('kelas', $user->kelas) == 'XII DKV 1' ? 'selected' : '' }}>XII DKV 1</option>
                            <option {{ old('kelas', $user->kelas) == 'XII DKV 2' ? 'selected' : '' }}>XII DKV 2</option>
                            <option {{ old('kelas', $user->kelas) == 'XII APHP 1' ? 'selected' : '' }}>XII APHP 1</option>
                            <option {{ old('kelas', $user->kelas) == 'XII APHP 2' ? 'selected' : '' }}>XII APHP 2</option>
                            <option {{ old('kelas', $user->kelas) == 'XII TB 1' ? 'selected' : '' }}>XII TB 1</option>
                            <option {{ old('kelas', $user->kelas) == 'XII TB 2' ? 'selected' : '' }}>XII TB 2</option>
                            <option {{ old('kelas', $user->kelas) == 'XII RPL 1' ? 'selected' : '' }}>XII RPL 1</option>
                            <option {{ old('kelas', $user->kelas) == 'XII RPL 2' ? 'selected' : '' }}>XII RPL 2</option>
                            <option {{ old('kelas', $user->kelas) == 'XII RPL 3' ? 'selected' : '' }}>XII RPL 3</option>
                        </select>
                        @error('kelas')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                    
                    {{-- Tombol --}}
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="{{ route('users.index') }}" class="btn btn-warning">Kembali</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
{{-- JS Tambahan --}}
@endsection