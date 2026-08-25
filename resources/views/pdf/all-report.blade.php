<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }

        th {
            background-color: #2d3e50;
            color: #fff;
        }

        .sub-row td {
            background-color: #f9f9f9;
        }
    </style>
</head>
<body>
    <h1>Laporan Presensi</h1>
    <p>Data per Tanggal {{ \Carbon\Carbon::parse($startDate)->format('d-m-Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d-m-Y') }}</p>
    <table border="1" cellpadding="5" cellspacing="0">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>NIP</th>
                <th>Total Kehadiran</th>
                <th>Total Izin</th>
                <th>Total Ketidakhadiran</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($internValue as $index => $intern)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $intern['name'] }}</td>
                    <td>{{ $intern['nip'] }}</td>
                    <td>{{ $intern['submitted'] }}</td>
                    <td>{{ $intern['permits'] }}</td>
                    <td>{{ $intern['absence'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
