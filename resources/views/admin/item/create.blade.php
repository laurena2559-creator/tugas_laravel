@extends('admin.layouts.app')
@section('css')
{{-- CSS Tambahan --}}
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <h5 class="card-title fw-semibold mb-4">Tambah Data {{ $title }}</h5>
        <div class="card">
            <div class="card-body">
                <form action="{{ route('item.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    {{-- Nama --}}
                    <div class="mb-3">
                        <label for="name" class="form-label">Nama Barang</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" id="name" placeholder="Nama Item" value="{{ old('name') }}">
                        @error('name')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                    
                    {{-- Total Stok --}}
                    <div class="mb-3">
                        <label for="stock" class="form-label">Jumlah Total Stok</label>
                        <input type="number" class="form-control @error('stock') is-invalid @enderror" 
                               name="stock" id="stock" value="{{ old('stock') }}" min="1" required>
                        @error('stock')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    {{-- Jumlah Rusak (Opsional saat tambah barang baru) --}}
                    <div class="mb-3">
                        <label for="damaged_count" class="form-label">Jumlah Barang Rusak (Opsional)</label>
                        <input type="number" class="form-control @error('damaged_count') is-invalid @enderror" 
                               name="damaged_count" id="damaged_count" 
                               value="{{ old('damaged_count', 0) }}" min="0">
                        <small class="text-muted">Kosongkan atau isi 0 jika tidak ada barang rusak</small>
                        @error('damaged_count')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    {{-- Foto --}}
                    <div class="mb-3">
                        <label for="photo" class="form-label">Foto Barang</label>
                        <input type="file" class="form-control @error('photo') is-invalid @enderror" name="photo" id="photo">
                        @error('photo')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    {{-- Kode Unik --}}
                    <div class="mb-3">
                        <label for="unique_code" class="form-label">Nomor Seri</label>
                        <input type="text" class="form-control @error('unique_code') is-invalid @enderror" 
                        name="unique_code" id="unique_code" placeholder="Contoh: MM-001" 
                        value="{{ old('unique_code') }}">
                        @error('unique_code')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    {{-- Penanggung Jawab --}}
                    <div class="mb-3">
                        <label for="user_id" class="form-label">Penanggung Jawab</label>
                        <select class="form-select @error('user_id') is-invalid @enderror" name="user_id" id="user_id">
                            <option value="" disabled selected>Pilih</option>
                            @foreach ($users as $user)
                            <option value="{{ $user->id }}" 
                            {{ old('user_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }}   
                            </option>
                            @endforeach
                        </select>
                        @error('user_id')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    {{-- Tombol --}}
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="{{ route('item.index') }}" class="btn btn-warning">Kembali</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
{{-- JS Tambahan --}}
@endsection