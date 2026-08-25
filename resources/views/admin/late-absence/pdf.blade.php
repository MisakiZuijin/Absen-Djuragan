<!DOCTYPE html>
<html>
<head>
    <title>Laporan Keterlambatan</title>
    <style>
        body { 
            font-family: 'Helvetica', 'Arial', sans-serif; 
            font-size: 12px;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 20px;
        }
        th, td { 
            border: 1px solid #dddddd; 
            padding: 8px; 
            text-align: left; 
        }
        th { 
            background-color: #f2f2f2; 
            font-weight: bold;
        }
        h2 {
            text-align: center;
        }
        .status-telat { background-color: #fff3cd; }
        .status-diterima { background-color: #d4edda; }
        .status-ditambah { background-color: #f8d7da; }
    </style>
</head>
<body>
    <h2>Laporan Keterlambatan</h2>
    <p><strong>Tanggal Laporan:</strong> {{ now()->format('d-m-Y') }}</p>
    <p><strong>Filter Tanggal Data:</strong> {{ request('date', today()->format('d-m-Y')) }}</p>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Intern</th>
                <th>Shift</th>
                <th>Waktu Absen</th>
                <th>Durasi Telat</th>
                <th>Status</th>
                <th>Catatan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($lateAbsences as $index => $late)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $late->intern->user->profile->full_name ?? '-' }}</td>
                    <td>{{ $late->shift->name ?? '-' }} ({{ \Carbon\Carbon::parse($late->shift->start_time ?? '')->format('H:i') }})</td>
                    <td>{{ \Carbon\Carbon::parse($late->absen_time)->format('H:i:s') }}</td>
                    <td>{{ $late->late_minutes }} menit</td>
                    <td class="
                        @if($late->status == 'telat') status-telat
                        @elseif($late->status == 'tepat_waktu') status-diterima
                        @else status-ditambah
                        @endif
                    ">
                        @if($late->status == 'telat') Belum Ditinjau
                        @elseif($late->status == 'tepat_waktu') Alasan Diterima
                        @else Jam Ditambah
                        @endif
                    </td>
                    <td>{{ $late->notes ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center;">Tidak ada data keterlambatan yang ditemukan sesuai filter.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>