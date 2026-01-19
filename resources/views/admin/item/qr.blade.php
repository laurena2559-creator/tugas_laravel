@extends('admin.layouts.app')

@section('content')
<div class="card">
    <div class="card-body text-center">
        <h4>QR Code untuk {{ $item->name }}</h4>
        <hr>

        {!! QrCode::size(200)->generate(route('item.show', $item->id)) !!}

        <p class="mt-3">
            Scan untuk melihat detail barang.
        </p>

        <a href="{{ route('item.index') }}" class="btn btn-warning mt-3">Kembali</a>
    </div>
</div>
@endsection
