@extends('admin.layouts.app')

@section('content')
<div class="container">
    <h1>Detail Item</h1>

    <table class="table table-bordered mt-3">
        <tr>
            <th>ID</th>
            <td>{{ $item->id }}</td>
        </tr>

        <tr>
            <th>Nama</th>
            <td>{{ $item->name }}</td>
        </tr>

        <tr>
            <th>Kode Unik</th>
            <td>{{ $item->unique_code }}</td>
        </tr>

        <tr>
            <th>Kondisi</th>
            <td>{{ $item->condition }}</td>
        </tr>

        <tr>
            <th>User</th>
            <td>{{ $item->user->name ?? '-' }}</td>
        </tr>

        <tr>
            <th>Foto</th>
            <td>
                @if ($item->photo)
                    <img src="{{ asset('photos/' . $item->photo) }}" width="150">
                @else
                    <span>Tidak ada foto</span>
                @endif
            </td>
        </tr>
    </table>

    <a href="{{ route('item.index') }}" class="btn btn-secondary">Kembali</a>
</div>
@endsection
