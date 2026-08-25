<!DOCTYPE html>
<html>

<head>
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
    <h2>Laporan Presensi {{ $profileName }}</h2>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Jam Masuk</th>
                <th>Jam Pulang</th>
                <th>Istirahat Mulai</th>
                <th>Istirahat Selesai</th>
                <th>Total Jam</th>
                <th>(+) / (-)</th>
                <th>Status Kehadiran</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach ($groupedData as $date => $rows)
                @foreach ($rows as $index => $row)
                    <tr>
                        @if ($index === 0)
                            <td rowspan="{{ count($rows) + collect($rows)->sum(fn($r) => count($r['adjustable'])) }}">
                                {{ $no++ }}</td>
                            <td rowspan="{{ count($rows) + collect($rows)->sum(fn($r) => count($r['adjustable'])) }}">
                                {{ $date }}</td>
                        @endif
                        <td>{{ $row['attendance']['start_time'] ?? '-' }}</td>
                        <td>{{ $row['attendance']['end_time'] ?? '-' }}</td>
                        <td>{{ $row['attendance']['break_time'] ?? '-' }}</td>
                        <td>{{ $row['attendance']['back_time'] ?? '-' }}</td>
                        <td>{{ $row['total_time'] ?? '-' }}</td>
                        <td>{{ $row['difference'] ?? '-' }}</td>
                        <td>{{ $row['attdStatus'] ?? '-' }}</td>
                    </tr>
                    @foreach ($row['adjustable'] as $adjustable)
                        <tr>
                            <td>{{ $adjustable['start_time'] ?? '-' }}</td>
                            <td>{{ $adjustable['end_time'] ?? '-' }}</td>
                            <td>{{ $adjustable['break_time'] ?? '-' }}</td>
                            <td>{{ $adjustable['back_time'] ?? '-' }}</td>
                            <td>{{ $adjustable['formatted_duration'] ?? '-' }}</td>
                            <td>-</td>
                            <td>Ganti Jam</td>
                        </tr>
                    @endforeach
                @endforeach
            @endforeach
        </tbody>

    </table>
</body>

</html>
