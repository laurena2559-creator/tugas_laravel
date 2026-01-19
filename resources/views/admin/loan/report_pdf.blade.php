<!DOCTYPE html>
<html>
<head>
    <title>Laporan Bulanan Inventaris</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table, th, td { border: 1px solid black; padding: 8px; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <div class="header">
        <h2>LAPORAN PEMINJAMAN BARANG SEKOLAH</h2>
        <p>Periode: {{ $bulan }} / {{ $tahun }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tgl Pinjam</th>
                <th>Peminjam</th>
                <th>Barang & Qty</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($loans as $loan)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $loan->loan_date }}</td>
                <td>{{ $loan->user->name }}</td>
                <td>
                    @foreach($loan->items as $item)
                    {{ $item->name }} ({{ $item->pivot->quantity }} unit)<br>
                    @endforeach
                </td>
                <td>{{ ucfirst($loan->status) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>