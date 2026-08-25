<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Intern;
use App\Models\LateAbsence;
use App\Models\Attendance;
use App\Models\DetailSchedule;
use App\Models\Shift;
use App\Models\Office;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LateAbsenceController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = LateAbsence::with(['intern.user.profile', 'shift', 'attendance'])
                ->orderBy('absen_time', 'desc');

            // Filter berdasarkan tanggal
            if ($request->filled('date')) {
                $query->whereDate('absen_time', $request->date);
            } else {
                // Default tampilkan hari ini
                $query->whereDate('absen_time', today());
            }

            // Filter berdasarkan nama intern
            if ($request->filled('name')) {
                $query->whereHas('intern.user.profile', function ($q) use ($request) {
                    $q->where('full_name', 'like', '%' . $request->name . '%');
                });
            }

            // Filter berdasarkan status
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Filter berdasarkan durasi keterlambatan minimum
            if ($request->filled('min_late_minutes')) {
                $query->where('late_minutes', '>=', $request->min_late_minutes);
            }

            $lateAbsences = $query->paginate(20);

            // Tambahkan perhitungan adjusted end time untuk setiap record
            foreach ($lateAbsences as $lateAbsence) {
                $lateAbsence->adjusted_info = $this->calculateAdjustedTimeInfo($lateAbsence);
            }

            // Data untuk filter dropdown
            $shifts = Shift::all();
            $offices = Office::all();

            return view('admin.late-absence', compact('lateAbsences', 'shifts', 'offices'));

        } catch (\Exception $e) {
            Log::error('Error in LateAbsenceController@index: ' . $e->getMessage());

            // Return with empty data if error occurs
            $lateAbsences = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
            $shifts = collect([]);
            $offices = collect([]);

            return view('admin.late-absence', compact('lateAbsences', 'shifts', 'offices'))
                ->with('error', 'Terjadi kesalahan saat memuat data keterlambatan');
        }
    }

    private function calculateAdjustedTimeInfo(LateAbsence $lateAbsence)
    {
        $shift = $lateAbsence->shift;
        $attendance = $lateAbsence->attendance;

        if (!$shift) {
            return null;
        }

        $info = [
            'original_start_time' => $shift->start_time ?? '00:00',
            'original_end_time' => $shift->end_time ?? '00:00',
            'adjusted_end_time' => null,
            'can_checkout' => false,
            'current_time' => now()->format('H:i'),
            'late_minutes' => $lateAbsence->late_minutes
        ];

        // Untuk status lewat, hitung adjusted end time
        if ($lateAbsence->status === 'lewat' && $shift->end_time) {
            $originalEndTime = Carbon::parse($shift->end_time);
            $adjustedEndTime = $originalEndTime->copy()->addMinutes($lateAbsence->late_minutes);
            $info['adjusted_end_time'] = $adjustedEndTime->format('H:i');

            // Cek apakah sudah bisa checkout
            $currentTime = Carbon::now();
            $checkTime = Carbon::parse($currentTime->format('Y-m-d') . ' ' . $adjustedEndTime->format('H:i:s'));
            $info['can_checkout'] = $currentTime->greaterThanOrEqualTo($checkTime);
        }

        // Untuk status tepat_waktu, gunakan start time shift
        if ($lateAbsence->status === 'tepat_waktu') {
            $info['start_time_applied'] = $shift->start_time;
        }

        // Cek actual end time di attendance
        if ($attendance) {
            $info['actual_start_time'] = $attendance->start_time ? Carbon::parse($attendance->start_time)->format('H:i') : null;
            $info['actual_end_time'] = $attendance->end_time ? Carbon::parse($attendance->end_time)->format('H:i') : null;
            $info['has_checked_out'] = !is_null($attendance->end_time);
        }

        return $info;
    }

    public function create(Request $request)
    {
        $date = $request->get('date', today()->format('Y-m-d'));

        try {
            // Ambil semua attendance untuk tanggal tersebut yang belum punya late_absence record
            $attendances = Attendance::whereDate('date', $date)
                ->whereNotNull('start_time')
                ->whereDoesntHave('lateAbsences')
                ->with(['intern.user.profile', 'detailSchedules.shift'])
                ->get();

            $lateAttendances = [];

            foreach ($attendances as $attendance) {
                // Dapatkan shift yang seharusnya untuk intern pada tanggal tersebut
                $shift = $this->getInternShiftForDate($attendance->intern_id, $date);

                if (!$shift) continue;

                // Calculate if actually late
                $absenTime = Carbon::parse($attendance->start_time);
                $attendanceDate = Carbon::parse($attendance->date);
                $shiftStartTime = Carbon::parse($attendanceDate->format('Y-m-d') . ' ' . $shift->start_time);

                $toleranceMinutes = 5;

                if ($absenTime->greaterThan($shiftStartTime)) {
                    $differenceMinutes = $absenTime->diffInMinutes($shiftStartTime);
                    if ($differenceMinutes > $toleranceMinutes) {
                        $lateMinutes = $differenceMinutes - $toleranceMinutes;

                        $lateAttendances[] = [
                            'attendance' => $attendance,
                            'intern' => $attendance->intern,
                            'shift' => $shift,
                            'absen_time' => $absenTime,
                            'shift_start_time' => $shiftStartTime,
                            'late_minutes' => $lateMinutes,
                            'difference_minutes' => $differenceMinutes
                        ];
                    }
                }
            }

            return response()->json([
                'date' => $date,
                'late_attendances' => $lateAttendances,
                'total_found' => count($lateAttendances)
            ]);

        } catch (\Exception $e) {
            Log::error('Error in create method: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan saat mencari data'], 500);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'attendance_ids' => 'array',
            'create_all' => 'boolean'
        ]);

        try {
            DB::beginTransaction();

            $date = $request->date;
            $created = 0;

            if ($request->create_all) {
                // Buat semua yang terlambat untuk tanggal tersebut
                $attendances = Attendance::whereDate('date', $date)
                    ->whereNotNull('start_time')
                    ->whereDoesntHave('lateAbsences')
                    ->with(['intern.user.profile'])
                    ->get();

                foreach ($attendances as $attendance) {
                    $created += $this->createLateRecord($attendance, $date) ? 1 : 0;
                }
            } else {
                // Buat hanya yang dipilih
                $attendanceIds = $request->attendance_ids ?? [];
                $attendances = Attendance::whereIn('id', $attendanceIds)
                    ->with(['intern.user.profile'])
                    ->get();

                foreach ($attendances as $attendance) {
                    $created += $this->createLateRecord($attendance, $date) ? 1 : 0;
                }
            }

            DB::commit();

            return redirect()->back()->with('status', "Berhasil membuat {$created} record keterlambatan untuk tanggal {$date}");

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error creating late absence records: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal membuat record keterlambatan: ' . $e->getMessage());
        }
    }

    private function getInternShiftForDate($internId, $date)
    {
        try {
            // Cari detail schedule untuk intern pada tanggal tertentu
            $detailSchedule = DetailSchedule::whereHas('schedule', function($query) use ($internId) {
                    $query->where('intern_id', $internId);
                })
                ->whereDate('date', $date)
                ->with('shift')
                ->first();

            return $detailSchedule ? $detailSchedule->shift : null;

        } catch (\Exception $e) {
            Log::error('Error getting intern shift: ' . $e->getMessage());
            return null;
        }
    }

    private function createLateRecord($attendance, $date)
    {
        try {
            // Dapatkan shift yang seharusnya untuk intern pada tanggal tersebut
            $shift = $this->getInternShiftForDate($attendance->intern_id, $date);

            if (!$shift) return false;

            // Check if already exists
            if (LateAbsence::where('attendance_id', $attendance->id)->exists()) {
                return false;
            }

            // Calculate late status
            $absenTime = Carbon::parse($attendance->start_time);
            $attendanceDate = Carbon::parse($attendance->date);
            $shiftStartTime = Carbon::parse($attendanceDate->format('Y-m-d') . ' ' . $shift->start_time);

            $toleranceMinutes = 5;

            if ($absenTime->greaterThan($shiftStartTime)) {
                $differenceMinutes = $absenTime->diffInMinutes($shiftStartTime);
                if ($differenceMinutes > $toleranceMinutes) {
                    $lateMinutes = $differenceMinutes - $toleranceMinutes;

                    LateAbsence::create([
                        'intern_id' => $attendance->intern_id,
                        'shift_id' => $shift->id,
                        'attendance_id' => $attendance->id,
                        'date' => $attendanceDate->format('Y-m-d'),
                        'absen_time' => $absenTime,
                        'original_absen_time' => $absenTime, // Simpan waktu asli
                        'scheduled_time' => $shift->start_time,
                        'late_minutes' => $lateMinutes,
                        'status' => 'telat',
                        'type' => 'checkin'
                    ]);

                    return true;
                }
            }

            return false;

        } catch (\Exception $e) {
            Log::error('Error creating late record: ' . $e->getMessage());
            return false;
        }
    }

    public function scanLateAbsences(Request $request)
    {
        $date = $request->get('date', today()->format('Y-m-d'));

        try {
            DB::beginTransaction();

            $attendances = Attendance::whereDate('date', $date)
                ->whereNotNull('start_time')
                ->whereDoesntHave('lateAbsences')
                ->with(['intern.user.profile'])
                ->get();

            $results = [
                'date' => $date,
                'total_checked' => $attendances->count(),
                'late_found' => 0,
                'records_created' => 0,
                'details' => []
            ];

            foreach ($attendances as $attendance) {
                // Dapatkan shift yang seharusnya untuk intern pada tanggal tersebut
                $shift = $this->getInternShiftForDate($attendance->intern_id, $date);

                if (!$shift) continue;

                // Calculate late status
                $absenTime = Carbon::parse($attendance->start_time);
                $attendanceDate = Carbon::parse($attendance->date);
                $shiftStartTime = Carbon::parse($attendanceDate->format('Y-m-d') . ' ' . $shift->start_time);

                $toleranceMinutes = 5;
                $isLate = false;
                $lateMinutes = 0;

                if ($absenTime->greaterThan($shiftStartTime)) {
                    $differenceMinutes = $absenTime->diffInMinutes($shiftStartTime);
                    if ($differenceMinutes > $toleranceMinutes) {
                        $isLate = true;
                        $lateMinutes = $differenceMinutes - $toleranceMinutes;
                        $results['late_found']++;

                        // Create the record
                        LateAbsence::create([
                            'intern_id' => $attendance->intern_id,
                            'shift_id' => $shift->id,
                            'attendance_id' => $attendance->id,
                            'date' => $attendanceDate->format('Y-m-d'),
                            'absen_time' => $absenTime,
                            'original_absen_time' => $absenTime, // Simpan waktu asli
                            'scheduled_time' => $shift->start_time,
                            'late_minutes' => $lateMinutes,
                            'status' => 'telat',
                            'type' => 'checkin'
                        ]);

                        $results['records_created']++;
                    }
                }

                $results['details'][] = [
                    'intern_name' => $attendance->intern->user->profile->full_name ?? 'Unknown',
                    'shift_name' => $shift->name,
                    'shift_start' => $shift->start_time,
                    'absen_time' => $absenTime->format('H:i:s'),
                    'is_late' => $isLate,
                    'late_minutes' => $lateMinutes
                ];
            }

            DB::commit();

            return response()->json($results);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error scanning late absences: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:telat,tepat_waktu,lewat',
            'notes' => 'nullable|string|max:500'
        ]);

        try {
            DB::beginTransaction();

            $lateAbsence = LateAbsence::with(['attendance', 'shift'])->findOrFail($id);
            $oldStatus = $lateAbsence->status;
            $newStatus = $request->status;

            Log::info('Memperbarui status late absence', [
                'id' => $id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus
            ]);

            // 1. Jika status berubah menjadi "tepat_waktu", set start_time ke jam shift
            if ($newStatus === 'tepat_waktu') {
                $this->adjustStartTimeToShift($lateAbsence);
            }

            // 2. Jika status berubah menjadi "lewat", hitung adjusted_end_time
            elseif ($newStatus === 'lewat') {
                $this->calculateAdjustedEndTime($lateAbsence);
            }

            // 3. Reset perubahan jika status berubah dari sebelumnya
            if ($oldStatus === 'tepat_waktu' && $newStatus !== 'tepat_waktu') {
                $this->restoreOriginalStartTime($lateAbsence);
            }

            if ($oldStatus === 'lewat' && $newStatus !== 'lewat') {
                $this->clearAdjustedEndTime($lateAbsence);
            }

            // Update status late absence
            $lateAbsence->update([
                'status' => $newStatus,
                'notes' => $request->notes,
                'reviewed_at' => now(),
                'reviewed_by' => auth()->id()
            ]);

            DB::commit();

            return redirect()->back()->with('status', 'Status keterlambatan berhasil diperbarui');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error updating late absence status: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memperbarui status: ' . $e->getMessage());
        }
    }

    /**
     * Set start_time ke jam shift untuk status "tepat_waktu"
     */
    private function adjustStartTimeToShift(LateAbsence $lateAbsence)
    {
        try {
            $attendance = $lateAbsence->attendance;
            $shift = $lateAbsence->shift;

            if (!$attendance || !$shift || !$shift->start_time) {
                throw new \Exception('Data attendance atau shift tidak lengkap');
            }

            // Simpan waktu asli sebelum diubah
            if (!$lateAbsence->original_absen_time) {
                $lateAbsence->update([
                    'original_absen_time' => $attendance->start_time
                ]);
            }

            // Gabungkan tanggal attendance dengan jam start shift
            $attendanceDate = $attendance->date ? $attendance->date->format('Y-m-d') : today()->format('Y-m-d');
            $newStartTime = Carbon::parse($attendanceDate . ' ' . $shift->start_time);

            // Update HANYA start_time, end_time TIDAK diubah
            $attendance->update([
                'start_time' => $newStartTime
            ]);

            Log::info('Start time disesuaikan dengan jam shift', [
                'late_absence_id' => $lateAbsence->id,
                'attendance_id' => $attendance->id,
                'original_start_time' => $lateAbsence->original_absen_time,
                'new_start_time' => $newStartTime,
                'shift_start_time' => $shift->start_time,
                'end_time_remains' => $attendance->end_time
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Error adjusting start time to shift: ' . $e->getMessage());
            throw $e;
        }
    }
    /**
     * Hitung adjusted_end_time berdasarkan keterlambatan
     * TIDAK otomatis mengisi end_time di attendance
     */
        private function calculateAdjustedEndTime(LateAbsence $lateAbsence)
    {
        try {
            $attendance = $lateAbsence->attendance;
            $shift = $lateAbsence->shift;

            if (!$attendance || !$shift || !$shift->end_time) {
                throw new \Exception('Data attendance atau shift tidak lengkap');
            }

            // Parse jam pulang normal dari shift (HANYA waktu, tanpa tanggal)
            $normalEndTime = Carbon::parse($shift->end_time);

            // Tambahkan menit keterlambatan
            $adjustedEndTime = $normalEndTime->addMinutes($lateAbsence->late_minutes);

            // Simpan di adjusted_end_time HANYA waktu saja (format H:i:s)
            $attendance->update([
                'adjusted_end_time' => $adjustedEndTime->format('H:i:s')
            ]);

            Log::info('Adjusted end time dihitung untuk keterlambatan', [
                'attendance_id' => $attendance->id,
                'late_minutes' => $lateAbsence->late_minutes,
                'normal_end_time' => $shift->end_time,
                'adjusted_end_time' => $adjustedEndTime->format('H:i:s'),
                'end_time_attendance' => $attendance->end_time
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Error calculating adjusted end time: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Kembalikan start_time ke waktu asli
     */
    private function restoreOriginalStartTime(LateAbsence $lateAbsence)
    {
        try {
            $attendance = $lateAbsence->attendance;

            if (!$attendance || !$lateAbsence->original_absen_time) {
                return false;
            }

            $attendance->update([
                'start_time' => $lateAbsence->original_absen_time
            ]);

            Log::info('Start time dikembalikan ke waktu asli', [
                'late_absence_id' => $lateAbsence->id,
                'restored_start_time' => $lateAbsence->original_absen_time
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Error restoring original start time: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Hapus adjusted_end_time
     */
    private function clearAdjustedEndTime(LateAbsence $lateAbsence)
    {
        try {
            $attendance = $lateAbsence->attendance;

            if (!$attendance) {
                return false;
            }

            $attendance->update([
                'adjusted_end_time' => null
            ]);

            Log::info('Adjusted end time dihapus', [
                'late_absence_id' => $lateAbsence->id,
                'attendance_id' => $attendance->id
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Error clearing adjusted end time: ' . $e->getMessage());
            return false;
        }
    }

    public function bulkUpdateStatus(Request $request)
    {
        $request->validate([
            'selected_ids' => 'required|array|min:1',
            'selected_ids.*' => 'required|exists:late_absences,id',
            'bulk_status' => 'required|in:telat,tepat_waktu,lewat',
            'bulk_notes' => 'nullable|string|max:500'
        ]);

        try {
            DB::beginTransaction();

            $selectedIds = $request->input('selected_ids');
            $bulkStatus = $request->input('bulk_status');

            $lateAbsences = LateAbsence::whereIn('id', $selectedIds)->get();

            // Handle perubahan status
            foreach ($lateAbsences as $lateAbsence) {
                $oldStatus = $lateAbsence->status;

                // LOGIKA YANG DIPERBAIKI (sama seperti updateStatus):
                // 1. Jika mengubah dari "tepat_waktu" ke status lain, kembalikan waktu absen asli
                if ($oldStatus === 'tepat_waktu' && $bulkStatus !== 'tepat_waktu') {
                    $this->restoreOriginalStartTime($lateAbsence);
                }

                // 2. Jika mengubah dari "lewat" ke status lain, kembalikan jam pulang normal
                if ($oldStatus === 'lewat' && $bulkStatus !== 'lewat') {
                    $this->clearAdjustedEndTime($lateAbsence);
                }

                $lateAbsence->update([
                    'status' => $bulkStatus,
                    'notes' => $request->input('bulk_notes'),
                    'reviewed_at' => now(),
                    'reviewed_by' => auth()->id()
                ]);

                // 3. Jika status berubah menjadi "tepat_waktu", update HANYA waktu absen sesuai jam start shift
                if ($bulkStatus === 'tepat_waktu' && $oldStatus !== 'tepat_waktu') {
                    $this->adjustStartTimeToShift($lateAbsence);
                }

                // 4. Jika status "lewat", hitung adjusted_end_time (HANYA jika sebelumnya bukan lewat)
                if ($bulkStatus === 'lewat' && $oldStatus !== 'lewat') {
                    $this->calculateAdjustedEndTime($lateAbsence);
                }
            }

            $updated = count($lateAbsences);

            // Hitung berapa yang berhasil disesuaikan
            $timeAdjusted = $lateAbsences->where('status', 'tepat_waktu')->count();
            $overtimeApplied = $lateAbsences->where('status', 'lewat')->count();

            DB::commit();

            $message = "Berhasil memperbarui {$updated} data keterlambatan";
            if ($timeAdjusted > 0) {
                $message .= " dan menyesuaikan waktu absen untuk {$timeAdjusted} karyawan";
            }
            if ($overtimeApplied > 0) {
                $message .= " dan menerapkan penyesuaian jam pulang ke {$overtimeApplied} karyawan";
            }

            return redirect()->back()->with('status', $message);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error bulk updating late absence: ' . $e->getMessage(), [
                'selected_ids' => $request->input('selected_ids', []),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Gagal memperbarui data secara massal: ' . $e->getMessage());
        }
    }

    /**
     * Method untuk handle checkout user
     */
    public function processCheckout(Request $request, $attendanceId)
    {
        try {
            $attendance = Attendance::with(['shift', 'lateAbsences'])->findOrFail($attendanceId);

            // Validasi: sudah checkout belum?
            if ($attendance->end_time) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda sudah melakukan checkout hari ini'
                ], 400);
            }

            // Validasi: apakah sudah waktunya checkout?
            if (!$attendance->canCheckOut()) {
                $expectedTime = $attendance->getExpectedEndTime();
                $formattedTime = $expectedTime ? Carbon::parse($expectedTime)->format('H:i') : '--:--';

                return response()->json([
                    'success' => false,
                    'message' => "Belum waktunya pulang. Dapat pulang setelah jam: {$formattedTime}",
                    'expected_time' => $formattedTime
                ], 400);
            }

            DB::beginTransaction();

            // Set end_time dengan waktu sekarang
            $attendance->update([
                'end_time' => Carbon::now()->format('H:i:s'),
                'checkout_notes' => $request->notes ?? 'Checkout normal'
            ]);

            // Hitung total menit kerja
            $totalWorkMinutes = $attendance->getTotalWorkMinutes();
            $attendance->update([
                'total_min' => $totalWorkMinutes
            ]);

            DB::commit();

            Log::info('Checkout berhasil diproses', [
                'attendance_id' => $attendance->id,
                'end_time' => $attendance->end_time,
                'total_work_minutes' => $totalWorkMinutes,
                'had_adjusted_time' => !empty($attendance->adjusted_end_time)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Checkout berhasil',
                'checkout_time' => $attendance->end_time,
                'total_work_hours' => round($totalWorkMinutes / 60, 2)
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error processing checkout: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat checkout: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $lateAbsence = LateAbsence::findOrFail($id);

            // Kembalikan waktu absen dan jam pulang ke normal sebelum hapus
            if ($lateAbsence->status === 'tepat_waktu' && $lateAbsence->original_absen_time) {
                $lateAbsence->attendance->update([
                    'start_time' => $lateAbsence->original_absen_time
                ]);
            }

            if ($lateAbsence->status === 'lewat') {
                $lateAbsence->attendance->update([
                    'adjusted_end_time' => null
                ]);
            }

            $lateAbsence->delete();

            return response()->json(['success' => true, 'message' => 'Record berhasil dihapus']);

        } catch (\Exception $e) {
            Log::error('Error deleting late absence: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal menghapus record'], 500);
        }
    }

    public function export(Request $request)
    {
        try {
            $query = LateAbsence::with(['intern.user.profile', 'shift', 'attendance'])
                ->orderBy('absen_time', 'desc');

            if ($request->filled('date')) {
                $query->whereDate('absen_time', $request->date);
            }

            if ($request->filled('name')) {
                $query->whereHas('intern.user.profile', function ($q) use ($request) {
                    $q->where('full_name', 'like', '%' . $request->name . '%');
                });
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            $lateAbsences = $query->get();

            return response()->streamDownload(function () use ($lateAbsences) {
                $handle = fopen('php://output', 'w');

                // Header CSV
                fputcsv($handle, [
                    'Tanggal',
                    'Nama Intern',
                    'Shift',
                    'Waktu Absen',
                    'Waktu Absen Asli',
                    'Durasi Telat (menit)',
                    'Status',
                    'Jam Mulai Disesuaikan',
                    'Jam Pulang Asli',
                    'Jam Pulang Disesuaikan',
                    'Jam Pulang Aktual',
                    'Catatan',
                    'Ditinjau Oleh',
                    'Waktu Ditinjau'
                ]);

                // Data
                foreach ($lateAbsences as $late) {
                    $adjustedInfo = $this->calculateAdjustedTimeInfo($late);

                    fputcsv($handle, [
                        $late->absen_time->format('d-m-Y'),
                        $late->intern->user->profile->full_name ?? '-',
                        $late->shift->name ?? '-',
                        $late->absen_time->format('H:i:s'),
                        $late->original_absen_time ? $late->original_absen_time->format('H:i:s') : '-',
                        $late->late_minutes,
                        ucfirst($late->status),
                        $adjustedInfo['start_time_applied'] ?? '-',
                        $adjustedInfo['original_end_time'] ?? '-',
                        $adjustedInfo['adjusted_end_time'] ?? '-',
                        $adjustedInfo['actual_end_time'] ?? '-',
                        $late->notes ?? '-',
                        $late->reviewer->name ?? '-',
                        $late->reviewed_at ? $late->reviewed_at->format('d-m-Y H:i') : '-'
                    ]);
                }

                fclose($handle);
            }, 'keterlambatan-' . now()->format('Y-m-d') . '.csv');
        } catch (\Exception $e) {
            Log::error('Error exporting late absences: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal export data'], 500);
        }
    }

    public function getDetails($id)
    {
        try {
            $lateAbsence = LateAbsence::with(['intern.user.profile', 'shift', 'attendance'])
                ->findOrFail($id);

            $adjustedInfo = $this->calculateAdjustedTimeInfo($lateAbsence);

            return response()->json([
                'id' => $lateAbsence->id,
                'intern_name' => $lateAbsence->intern->user->profile->full_name ?? 'Unknown',
                'late_minutes' => $lateAbsence->late_minutes,
                'absen_time' => $lateAbsence->absen_time->format('H:i:s'),
                'status' => $lateAbsence->status,
                'shift' => [
                    'name' => $lateAbsence->shift->name ?? 'Unknown',
                    'start_time' => $lateAbsence->shift->start_time ?? '00:00',
                    'end_time' => $lateAbsence->shift->end_time ?? '00:00'
                ],
                'attendance' => [
                    'current_start_time' => $lateAbsence->attendance->start_time ? Carbon::parse($lateAbsence->attendance->start_time)->format('H:i:s') : null,
                    'current_end_time' => $lateAbsence->attendance->end_time ?? null,
                    'adjusted_end_time' => $lateAbsence->attendance->adjusted_end_time ?? null,
                ],
                'adjusted_info' => $adjustedInfo,
                'has_original_time' => !empty($lateAbsence->original_absen_time),
                'original_absen_time' => $lateAbsence->original_absen_time ? Carbon::parse($lateAbsence->original_absen_time)->format('H:i:s') : null,
                'can_checkout' => $lateAbsence->attendance ? $lateAbsence->attendance->canCheckOut() : false
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching late absence details: ' . $e->getMessage());
            return response()->json(['error' => 'Data tidak ditemukan'], 404);
        }
    }

    public function exportPdf(Request $request)
    {
        try {
            $query = LateAbsence::with(['intern.user.profile', 'shift', 'attendance'])
                ->orderBy('absen_time', 'asc');

            // Replikasi filter dari method index() agar data konsisten
            if ($request->filled('date')) {
                $query->whereDate('absen_time', $request->date);
            } else {
                $query->whereDate('absen_time', today());
            }

            if ($request->filled('name')) {
                $query->whereHas('intern.user.profile', function ($q) use ($request) {
                    $q->where('full_name', 'like', '%' . $request->name . '%');
                });
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('min_late_minutes')) {
                $query->where('late_minutes', '>=', $request->min_late_minutes);
            }

            $lateAbsences = $query->get();

            // Tambahkan adjusted info untuk PDF
            foreach ($lateAbsences as $lateAbsence) {
                $lateAbsence->adjusted_info = $this->calculateAdjustedTimeInfo($lateAbsence);
            }

            $pdf = PDF::loadView('admin.late-absence.pdf', [
                'lateAbsences' => $lateAbsences
            ]);

            $fileName = 'laporan-keterlambatan-' . now()->format('d-m-Y') . '.pdf';
            return $pdf->download($fileName);

        } catch (\Exception $e) {
            Log::error('Error exporting PDF data: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal mengekspor data PDF');
        }
    }
}