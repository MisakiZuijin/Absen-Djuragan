<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Presensi & Performa Pemagang</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10px;
            color: #333;
            margin: 0;
            padding: 10px;
        }

        .header {
            margin-bottom: 20px;
            border-bottom: 2px solid #1e293b;
            padding-bottom: 10px;
        }

        .title {
            font-size: 16px;
            font-weight: bold;
            color: #0f172a;
            margin: 0 0 5px 0;
            text-transform: uppercase;
        }

        .subtitle {
            font-size: 11px;
            color: #64748b;
            margin: 0;
        }

        .meta-info {
            margin-top: 5px;
            font-size: 10px;
            color: #475569;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 9px;
        }

        th {
            background-color: #1e293b;
            color: #ffffff;
            font-weight: bold;
            text-transform: uppercase;
            padding: 6px 4px;
            border: 1px solid #0f172a;
            text-align: center;
        }

        td {
            border: 1px solid #cbd5e1;
            padding: 5px 4px;
            vertical-align: middle;
        }

        tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .text-left { text-align: left; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }

        .badge {
            display: inline-block;
            padding: 2px 5px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 8px;
        }
        .badge-success { background-color: #dcfce7; color: #166534; }
        .badge-warning { background-color: #fef3c7; color: #92400e; }
        .badge-danger { background-color: #fee2e2; color: #991b1b; }
        .badge-info { background-color: #e0f2fe; color: #075985; }

        .footer {
            margin-top: 25px;
            font-size: 9px;
            color: #94a3b8;
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1 class="title">
            @if($isSuperAdmin ?? false)
                Laporan Eksekutif Performa & Presensi Pemagang
            @else
                Laporan Rekap Data Presensi Pemagang
            @endif
        </h1>
        <p class="subtitle">Absensi Djuragan — Sistem Manajemen & Monitoring Presensi</p>
        <div class="meta-info">
            <strong>Periode:</strong> {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }} |
            <strong>Dicetak Pada:</strong> {{ \Carbon\Carbon::now()->format('d M Y H:i') }} WIB |
            <strong>Total Pemagang:</strong> {{ count($internValue) }} Orang
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 25px;">No</th>
                <th class="text-left" style="width: 140px;">Nama Mahasiswa</th>
                <th style="width: 70px;">NIP / NIM</th>
                <th style="width: 80px;">Divisi</th>
                <th style="width: 80px;">Sekolah / Kampus</th>
                <th style="width: 45px;">Hari Kerja</th>
                <th style="width: 45px;">Total Hadir</th>
                @if($isSuperAdmin ?? false)
                    <th style="width: 45px;">Tepat Waktu</th>
                    <th style="width: 45px;">Terlambat</th>
                    <th style="width: 40px;">Izin</th>
                    <th style="width: 40px;">Alpha</th>
                    <th style="width: 65px;">Presensi Offline</th>
                    <th style="width: 45px;">Skor Disiplin</th>
                @else
                    <th style="width: 50px;">Total Izin</th>
                    <th style="width: 50px;">Total Alpha</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse ($internValue as $index => $intern)
                <tr>
                    <td class="text-center font-bold">{{ $index + 1 }}</td>
                    <td class="text-left font-bold">{{ $intern['name'] }}</td>
                    <td class="text-center">{{ $intern['nip'] }}</td>
                    <td class="text-center">{{ $intern['division'] ?? '-' }}</td>
                    <td class="text-center">{{ $intern['school'] ?? '-' }}</td>
                    <td class="text-center">{{ $intern['total_days'] ?? '-' }}</td>
                    <td class="text-center font-bold" style="color: #166534;">{{ $intern['submitted'] }}</td>
                    
                    @if($isSuperAdmin ?? false)
                        <td class="text-center" style="color: #0284c7;">{{ $intern['on_time'] ?? 0 }}</td>
                        <td class="text-center" style="color: #d97706;">{{ $intern['late'] ?? 0 }}</td>
                        <td class="text-center" style="color: #ca8a04;">{{ $intern['permits'] }}</td>
                        <td class="text-center" style="color: #dc2626; font-weight: bold;">{{ $intern['absence'] }}</td>
                        <td class="text-center">
                            {{ $intern['offline_hadir'] ?? 0 }} Hadir
                            @if(($intern['offline_late'] ?? 0) > 0)
                                ({{ $intern['offline_late'] }} Tlt)
                            @endif
                        </td>
                        <td class="text-center font-bold">
                            @php
                                $score = $intern['discipline_score'] ?? 100;
                            @endphp
                            @if($score >= 90)
                                <span class="badge badge-success">{{ $score }}% (A)</span>
                            @elseif($score >= 75)
                                <span class="badge badge-info">{{ $score }}% (B)</span>
                            @elseif($score >= 60)
                                <span class="badge badge-warning">{{ $score }}% (C)</span>
                            @else
                                <span class="badge badge-danger">{{ $score }}% (D)</span>
                            @endif
                        </td>
                    @else
                        <td class="text-center" style="color: #ca8a04;">{{ $intern['permits'] }}</td>
                        <td class="text-center font-bold" style="color: #dc2626;">{{ $intern['absence'] }}</td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="{{ ($isSuperAdmin ?? false) ? 13 : 9 }}" class="text-center" style="padding: 20px; color: #94a3b8;">
                        Tidak ada data presensi pada rentang tanggal yang dipilih.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dokumen ini dibuat otomatis oleh Sistem Absensi Djuragan pada {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y H:i:s') }} WIB
    </div>
</body>
</html>
