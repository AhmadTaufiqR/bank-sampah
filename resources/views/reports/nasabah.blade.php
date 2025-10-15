<!DOCTYPE html>
<html>
<head>
    <title>Laporan Nasabah Bank Sampah</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }
        th, td {
            border: 1px solid #000;
            padding: 6px;
            text-align: center;
        }
    </style>
</head>
<body>
    <h2 style="text-align: center;">Laporan Data Nasabah Bank Sampah</h2>
    <table>
        <thead>
            <tr>
                <th>Username</th>
                <th>No. HP</th>
                <th>Tgl Setor</th>
                <th>Jenis Sampah</th>
                <th>Berat (kg)</th>
                <th>Total (Rp)</th>
                <th>Tgl Tarik</th>
                <th>Jumlah (Rp)</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($records as $row)
                <tr>
                    <td>{{ $row['username'] }}</td>
                    <td>{{ $row['phone'] }}</td>
                    <td>{{ $row['tgl_setor'] }}</td>
                    <td>{{ $row['jenis_sampah'] }}</td>
                    <td>{{ $row['berat'] }}</td>
                    <td>{{ $row['total'] }}</td>
                    <td>{{ $row['tgl_tarik'] }}</td>
                    <td>{{ $row['jumlah_tarik'] }}</td>
                    <td>{{ $row['status'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
