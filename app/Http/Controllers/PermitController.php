<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\PermitLog;
use App\Models\PermitSetting;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Carbon\CarbonInterval;

class PermitController extends Controller
{
    public function start(Request $request)
    {
        $user = Auth::user();
        if (!$user->intern) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => 'Data intern tidak ditemukan untuk pengguna ini.'], 404);
            }
            return redirect()->back()->with('error', 'Data intern tidak ditemukan untuk pengguna ini.');
        }
        $internId = $user->intern->id;
        $permitType = $request->input('type');

        // Cek izin aktif langsung di tabel `permit_logs` melalui relasi attendance
        $activePermitLog = PermitLog::whereHas('attendance', function ($q) use ($internId) {
            $q->where('intern_id', $internId);
        })
            ->whereNull('end_time')
            ->exists();

        if ($activePermitLog) {
            $errorMsg = 'Anda sudah memiliki izin lain yang sedang aktif.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => $errorMsg], 409);
            }
            return redirect()->back()->with('error', $errorMsg);
        }

        // Cek batas harian (jika > 0)
        $limitSetting = PermitSetting::where('type', $permitType)->first();
        if ($limitSetting && (int) $limitSetting->max_daily_count > 0) {
            $todaysCount = PermitLog::whereHas('attendance', function ($q) use ($internId) {
                $q->where('intern_id', $internId);
            })
                ->where('type', $permitType)
                ->whereDate('start_time', today())
                ->count();

            if ($todaysCount >= (int) $limitSetting->max_daily_count) {
                $errorMsg = 'Anda telah mencapai batas maksimal untuk Izin ' . ucfirst($permitType) . ' hari ini (' . $limitSetting->max_daily_count . ' kali).';
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['error' => $errorMsg], 422);
                }
                return redirect()->back()->with('error', $errorMsg);
            }
        }

        // Simpan ke tabel 'attendances'
        $attendance = Attendance::firstOrCreate(
            ['intern_id' => $internId, 'date' => now()->toDateString()],
            ['user_id' => $user->id]
        );
        
        $reason = null;
        if ($permitType === 'leave') {
            $reason = $request->input('keterangan');
            $attendance->permit_type = 'leave';
            $attendance->permit_start = now();
            $attendance->permit_back = null;
            $attendance->permit_description = $reason;
            $attendance->save();
        }

        PermitLog::create([
            'attendance_id'   => $attendance->id,
            'type'            => $permitType,
            'start_time'      => now(),
            'description'     => $reason,
            'approval_status' => $permitType === 'leave' ? 'pending' : 'approved',
            'is_mandatory_replace' => false,
            'agreed_duration_minutes' => 0,
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Izin berhasil dimulai.']);
        }
        return redirect()->back()->with('status', 'Izin berhasil dimulai.');
    }

    public function end(Request $request)
    {
        $user = Auth::user();
        if (!$user->intern) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => 'Data intern tidak ditemukan.'], 404);
            }
            return redirect()->back()->with('error', 'Data intern tidak ditemukan.');
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
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => 'Tidak ada izin aktif yang ditemukan.'], 404);
            }
            return redirect()->back()->with('error', 'Tidak ada izin aktif yang ditemukan.');
        }
        
        $endTime = now();
        $startTime = Carbon::parse($activePermitLog->start_time);
        $durationInMinutes = (int) ceil($startTime->diffInSeconds($endTime) / 60);

        // Ambil konfigurasi batas durasi dari PermitSetting
        $limitSetting = PermitSetting::where('type', $activePermitLog->type)->first();
        $maxDuration = $limitSetting ? (int) $limitSetting->max_duration_minutes : 0;

        // Fallback default jika di DB belum terisi
        if ($maxDuration <= 0 && $activePermitLog->type !== 'leave') {
            $maxDuration = $activePermitLog->type === 'prayer' ? 20 : ($activePermitLog->type === 'toilet' ? 25 : 0);
        }

        // Jika ada kesepakatan durasi spesifik di record (misal izin keluar yang disepakati)
        if ($activePermitLog->agreed_duration_minutes > 0) {
            $maxDuration = $activePermitLog->agreed_duration_minutes;
        }

        $isOverdue = false;
        $overdueMinutes = 0;

        $activePermitLog->end_time = $endTime;
        $activePermitLog->duration_in_minutes = $durationInMinutes;

        // Konsekuensi jika izin (Sholat, Toilet, atau Keluar) melebihi batas waktu:
        if ($maxDuration > 0 && $durationInMinutes > $maxDuration) {
            $isOverdue = true;
            $overdueMinutes = $durationInMinutes - $maxDuration;

            $activePermitLog->is_mandatory_replace = true;
            $activePermitLog->agreed_duration_minutes = $overdueMinutes;
            $activePermitLog->approval_status = 'approved';

            $overdueNote = "Melebihi batas waktu ({$durationInMinutes}m / batas {$maxDuration}m, kelebihan {$overdueMinutes}m masuk hutang jam)";
            $activePermitLog->description = $activePermitLog->description 
                ? ($activePermitLog->description . ' | ' . $overdueNote) 
                : $overdueNote;
        } else {
            if ($activePermitLog->type !== 'leave') {
                $activePermitLog->is_mandatory_replace = false;
                $activePermitLog->agreed_duration_minutes = 0;
            }
        }

        $activePermitLog->save();

        // Akhiri juga izin di tabel 'attendances'
        $attendance = $activePermitLog->attendance;
        if ($attendance) {
            if ($activePermitLog->type === 'leave' && $attendance->permit_start && !$attendance->permit_back) {
                $attendance->permit_back = $activePermitLog->end_time;
            }
            $totalPermitMinutesToday = PermitLog::where('attendance_id', $attendance->id)->sum('duration_in_minutes');
            $attendance->total_permit_min = $totalPermitMinutesToday;
            $attendance->save();
        }

        $duration = CarbonInterval::seconds($startTime->diffInSeconds($endTime))->cascade()->forHumans();

        $message = 'Izin berhasil diakhiri.';
        if ($isOverdue) {
            $message .= " Anda melebihi batas waktu ({$maxDuration} menit), kelebihan {$overdueMinutes} menit secara otomatis dimasukkan ke target Hutang Jam Anda.";
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'duration' => $duration,
                'message' => $message,
                'is_overdue' => $isOverdue,
                'overdue_minutes' => $overdueMinutes,
            ]);
        }

        return redirect()->back()->with('status', $message);
    }
}