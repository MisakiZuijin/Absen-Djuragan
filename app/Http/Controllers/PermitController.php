<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\PermitLog;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Carbon\CarbonInterval;

class PermitController extends Controller
{
    public function start(Request $request)
    {
        $user = Auth::user();
        if (!$user->intern) {
            return response()->json(['error' => 'Data intern tidak ditemukan untuk pengguna ini.'], 404);
        }
        $internId = $user->intern->id;
        $permitType = $request->input('type');

        // [PERBAIKAN] Cek izin aktif langsung di tabel `permit_logs` melalui relasi attendance
        // Ini lebih andal karena inilah yang dilihat oleh Asisten Admin.
        $activePermitLog = PermitLog::whereHas('attendance', function ($q) use ($internId) {
            $q->where('intern_id', $internId);
        })
            ->whereNull('end_time')
            ->exists();

        if ($activePermitLog) {
            return response()->json(['error' => 'Anda sudah memiliki izin lain yang sedang aktif.'], 409);
        }

        // Simpan ke tabel 'attendances' (jika masih diperlukan)
        $attendance = Attendance::firstOrCreate(
            ['intern_id' => $internId, 'date' => now()->toDateString()],
            ['user_id' => $user->id]
        );
        
        $attendance->permit_type = $permitType;
        $attendance->permit_start = now();
        $attendance->permit_back = null;

        // [PERBAIKAN] Ambil 'keterangan' dari request, bukan dari properti attendance.
        // Ini lebih aman karena properti itu hanya diisi untuk tipe 'leave'.
        $reason = null;
        if ($permitType === 'leave') {
             $reason = $request->input('keterangan');
             $attendance->permit_description = $reason;
        }
        $attendance->save();

        // [SOLUSI UTAMA] Buat entri di tabel `permit_logs`
        PermitLog::create([
            'attendance_id'   => $attendance->id,
            'type'            => $permitType,
            'start_time'      => $attendance->permit_start,
            'description'     => $reason,
            'approval_status' => $permitType === 'leave' ? 'pending' : 'approved',
        ]);

        return response()->json(['success' => true, 'message' => 'Izin berhasil dimulai.']);
    }

    public function end(Request $request)
    {
        $user = Auth::user();
        if (!$user->intern) {
            return response()->json(['error' => 'Data intern tidak ditemukan.'], 404);
        }
        $internId = $user->intern->id;
        
        // Akhiri izin di tabel `permit_logs` terlebih dahulu
        $activePermitLog = PermitLog::with('attendance')
            ->whereHas('attendance', function ($q) use ($internId) {
                $q->where('intern_id', $internId);
            })
            ->whereNull('end_time')
            ->latest('start_time')
            ->first();

        if (!$activePermitLog) {
            return response()->json(['error' => 'Tidak ada izin aktif yang ditemukan.'], 404);
        }
        
        $activePermitLog->end_time = now();
        $activePermitLog->save();

        // Akhiri juga izin di tabel 'attendances' (jika masih diperlukan)
        $attendance = $activePermitLog->attendance;
        if ($attendance && $attendance->permit_start && !$attendance->permit_back) {
            $attendance->permit_back = $activePermitLog->end_time;
            $attendance->save();
        }

        $startTime = Carbon::parse($activePermitLog->start_time);
        $endTime = Carbon::parse($activePermitLog->end_time);
        $duration = CarbonInterval::seconds($startTime->diffInSeconds($endTime))->cascade()->forHumans();

        return response()->json([
            'success' => true,
            'duration' => $duration,
            'message' => 'Izin berhasil diakhiri.'
        ]);
    }
}