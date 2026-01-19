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
                <form action="{{ route('category.store') }}" method="POST">
                    @csrf
                    {{-- Nama --}}
                    <div class="mb-3">
                        <label for="name" class="form-label">Nama Kategori</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" id="name" placeholder="Nama Kategori" value="{{ old('name') }}">
                        @error('name')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                    
                    {{-- Tombol --}}
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="{{ route('category.index') }}" class="btn btn-warning">Kembali</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
{{-- JS Tambahan --}}
@endsection