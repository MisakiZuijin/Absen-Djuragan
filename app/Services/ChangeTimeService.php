<?php

namespace App\Services;

use App\Helper\ActionResult;
use App\Models\Attendance;
use App\Models\ChangeTimeSession;
use App\Models\ChangeTimeSessionTarget;
use App\Models\ChangeTimeSetting;
use App\Models\DetailSchedule;
use App\Models\Holiday;
use App\Models\Office;
use App\Models\Shift;
use App\Models\User;
use App\Utils\DateNow;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ChangeTimeService
{
    protected DebtCalculationService $debtCalculationService;
    protected LocationService $locationService;
    protected WhatsappService $whatsappService;

    public function __construct(
        DebtCalculationService $debtCalculationService,
        LocationService $locationService,
        WhatsappService $whatsappService
    ) {
        $this->debtCalculationService = $debtCalculationService;
        $this->locationService = $locationService;
        $this->whatsappService = $whatsappService;
    }

    /**
     * Memulai sesi ganti jam baru untuk pemagang.
     *
     * @param int $userId
     * @param array $targetScheduleIds
     * @param int $shiftId
     * @param int $officeId
     * @param float|null $latitude
     * @param float|null $longitude
     * @param string|null $message
     * @return ActionResult
     */
    public function startSession(
        int $userId,
        array $targetScheduleIds,
        int $shiftId,
        int $officeId,
        ?float $latitude = null,
        ?float $longitude = null,
        ?string $message = null
    ): ActionResult {
        $user = User::with('intern.division')->find($userId);
        if (!$user || !$user->intern) {
            return new ActionResult(false, 'Data pemagang tidak ditemukan.');
        }

        $intern = $user->intern;
        $nowDate = Carbon::now('Asia/Jakarta')->toDateString();
        $nowTime = Carbon::now('Asia/Jakarta')->format('H:i:s');

        // 1. Cek Pendaftaran Ganti Jam (ChangeTimeRegistration) untuk hari ini jika ada
        $activeReg = \App\Models\ChangeTimeRegistration::where('intern_id', $intern->id)
            ->whereIn('status', ['pending', 'approved'])
            ->where(function ($q) use ($nowDate) {
                $q->whereNull('requested_date')
                  ->orWhereDate('requested_date', $nowDate);
            })
            ->latest('id')
            ->first();

        // 1b. Catatan / keterangan ganti jam bersifat opsional
        $finalNote = trim($message ?? '') ?: (trim($activeReg?->reason ?? '') ?: null);

        // 2. Validasi: Maksimal 1 sesi ganti jam per hari
        $sessionToday = ChangeTimeSession::where('intern_id', $intern->id)
            ->whereDate('session_date', $nowDate)
            ->exists();

        if ($sessionToday) {
            return new ActionResult(false, 'Anda sudah melakukan sesi ganti jam hari ini. Maksimal 1 sesi ganti jam per hari.');
        }

        // 3. Cek Pengaturan Hari Libur (ChangeTimeSetting)
        $setting = ChangeTimeSetting::getSettings();
        if ($setting->restrict_to_holidays) {
            $isSunday = Carbon::now('Asia/Jakarta')->isSunday();
            $isHoliday = Holiday::whereDate('date', $nowDate)->exists();
            if (!$isSunday && !$isHoliday) {
                // Di hari kerja biasa: HANYA jika ada pendaftaran yang SUDAH DISETUJUI (approved) oleh Admin KHUSUS hari ini
                $hasApprovedToday = $activeReg && $activeReg->status === 'approved' && (
                    empty($activeReg->requested_date) || $activeReg->requested_date->format('Y-m-d') === $nowDate
                );

                if (!$hasApprovedToday) {
                    return new ActionResult(false, 'Sesi ganti jam hanya diizinkan pada hari Minggu atau hari libur nasional, kecuali telah disetujui Admin untuk hari ini.');
                }
            }
        }

        // Fallback Shift & Office jika tidak diset / nilai default
        if ($shiftId <= 0) {
            $shiftId = (int) ($activeReg?->shift_id ?: (!empty($setting->allowed_shift_ids) ? $setting->allowed_shift_ids[0] : 2));
        }
        if ($officeId <= 0) {
            $officeId = (int) ($activeReg?->office_id ?: ($setting->default_office_id ?: (!empty($setting->allowed_office_ids) ? $setting->allowed_office_ids[0] : 1)));
        }

        // 4. Validasi: Pemagang tidak boleh ganti jam jika sedang aktif dalam jam kerja shift reguler hari ini
        $activeRegularAttendance = Attendance::where('intern_id', $intern->id)
            ->whereDate('date', $nowDate)
            ->whereNotNull('start_time')
            ->whereNull('end_time')
            ->exists();

        if ($activeRegularAttendance) {
            return new ActionResult(
                false,
                'Tidak dapat memulai ganti jam karena Anda sedang aktif dalam shift kerja reguler hari ini. Selesaikan shift reguler terlebih dahulu.'
            );
        }

        // 5. Validasi: Tidak ada sesi ganti jam lain yang sedang aktif
        $activeSession = ChangeTimeSession::where('intern_id', $intern->id)
            ->where('status', 'active')
            ->exists();

        if ($activeSession) {
            return new ActionResult(false, 'Anda masih memiliki sesi ganti jam yang sedang aktif.');
        }

        // 6. Target hutang jam: Ambil otomatis secara FIFO dari tanggal paling lampau (maks 7j 15m = 435 menit)
        $allDebts = $this->debtCalculationService->getInternDebts($intern->id, true);
        if ($allDebts->isEmpty()) {
            return new ActionResult(false, 'Anda tidak memiliki catatan kekurangan/hutang jam kerja saat ini.');
        }

        $targetDebts = collect();
        $accumulatedDebtMinutes = 0;
        $maxSessionTarget = 435; // 7 Jam 15 Menit (1 shift penuh)

        if (!empty($targetScheduleIds)) {
            $debtsById = $allDebts->keyBy('schedule_id');
            foreach ($targetScheduleIds as $sId) {
                if ($debt = $debtsById->get((int) $sId)) {
                    $targetDebts->push($debt);
                    $accumulatedDebtMinutes += (int) $debt['debt_minutes'];
                }
            }
        } else {
            // Urutan FIFO otomatis dari yang paling lampau
            foreach ($allDebts as $debt) {
                $targetDebts->push($debt);
                $accumulatedDebtMinutes += (int) $debt['debt_minutes'];
                if ($accumulatedDebtMinutes >= $maxSessionTarget) {
                    break;
                }
            }
        }

        if ($targetDebts->isEmpty()) {
            return new ActionResult(false, 'Target hutang jam tidak valid atau sudah terlunasi.');
        }

        $totalTargetDebtMinutes = min($accumulatedDebtMinutes, $maxSessionTarget);

        // 7. Validasi Shift & Kantor
        $shift = Shift::find($shiftId) ?: Shift::where('id', '>', 1)->first();
        $office = Office::find($officeId) ?: Office::first();
        if (!$shift || !$office) {
            return new ActionResult(false, 'Data shift atau kantor yang dipilih tidak valid.');
        }

        // 6. Validasi GPS Kantor jika diaktifkan
        $isGpsRequired = ($user->is_gps_activate == 1) && ($shift->is_gps_active == 1);
        if ($isGpsRequired) {
            if ($latitude === null || $longitude === null) {
                return new ActionResult(false, 'Gagal mendapatkan lokasi GPS. Pastikan GPS aktif dan izin lokasi diberikan.');
            }
            $mapsTrack = $this->locationService->checkIsInOfficeArea((float) $latitude, (float) $longitude, true);
            if ($mapsTrack->isInArea == false) {
                return new ActionResult(false, 'Lokasi Anda saat ini berada di luar area kantor yang ditentukan.');
            }
        }

        // 7. Simpan Sesi & Target ke Database
        DB::beginTransaction();
        try {
            $session = ChangeTimeSession::create([
                'intern_id' => $intern->id,
                'session_date' => $nowDate,
                'shift_id' => $shift->id,
                'office_id' => $office->id,
                'start_time' => $nowTime,
                'total_work_minutes' => 0,
                'total_break_minutes' => 0,
                'total_target_debt_minutes' => $totalTargetDebtMinutes,
                'status' => 'active',
                'start_time_message' => $finalNote,
                'latitude_start' => $latitude,
                'longitude_start' => $longitude,
            ]);

            foreach ($targetDebts as $target) {
                $rawSchedule = $target['raw_schedule'];
                ChangeTimeSessionTarget::create([
                    'change_time_session_id' => $session->id,
                    'detail_schedule_id' => $rawSchedule->id,
                    'attendance_id' => $rawSchedule->attendance_id,
                    'debt_minutes' => (int) $target['debt_minutes'],
                    'paid_minutes' => 0,
                    'is_fulfilled' => false,
                ]);
            }

            DB::commit();

            // Kirim notifikasi WhatsApp
            $internName = $user->profile->full_name ?? $user->username;
            $this->whatsappService->sendAttendanceNotificationToAllTargets(
                $intern,
                $internName,
                'GANTI JAM MASUK',
                $nowTime
            );

            return new ActionResult(true, 'Berhasil memulai sesi ganti jam.', $session->load(['shift', 'office', 'targets.detailSchedule.shift']));
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error starting ChangeTimeSession: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return new ActionResult(false, 'Gagal memulai sesi ganti jam: ' . $e->getMessage());
        }
    }

    /**
     * Memulai istirahat pada sesi ganti jam yang aktif.
     *
     * @param int $sessionId
     * @param string|null $message
     * @return ActionResult
     */
    public function startBreak(int $sessionId, ?string $message = null): ActionResult
    {
        $session = ChangeTimeSession::with('shift')->find($sessionId);
        if (!$session || !$session->isActive()) {
            return new ActionResult(false, 'Sesi ganti jam aktif tidak ditemukan.');
        }

        if (!empty($session->break_time)) {
            return new ActionResult(false, 'Sesi ini sudah mengambil waktu istirahat.');
        }

        $now = Carbon::now('Asia/Jakarta');
        $nowTime = $now->format('H:i:s');
        $shift = $session->shift;

        // Validasi aturan istirahat sesuai shift yang dipilih
        if ($shift) {
            // 1. Shift tanpa istirahat (break_time_in_minute <= 0)
            if (isset($shift->break_time_in_minute) && (int) $shift->break_time_in_minute <= 0) {
                return new ActionResult(false, 'Shift ' . ($shift->name ?? '') . ' tidak memiliki waktu istirahat.');
            }

            // 2. Belum memasuki waktu istirahat shift
            if (!empty($shift->start_break_time) && $nowTime < $shift->start_break_time) {
                return new ActionResult(
                    false,
                    'Belum waktunya istirahat (Waktu istirahat shift ' . ($shift->name ?? '') . ' mulai pukul ' . substr($shift->start_break_time, 0, 5) . ').'
                );
            }

            // 3. Sudah melewati waktu akhir istirahat shift
            if (!empty($shift->end_break_time) && $nowTime > $shift->end_break_time) {
                return new ActionResult(
                    false,
                    'Waktu istirahat shift ' . ($shift->name ?? '') . ' sudah terlewat (' . substr($shift->start_break_time ?? '', 0, 5) . ' - ' . substr($shift->end_break_time, 0, 5) . ').'
                );
            }
        }

        $session->update([
            'break_time' => $nowTime,
            'break_time_message' => $message,
        ]);

        return new ActionResult(true, 'Berhasil memulai istirahat ganti jam.', $session->fresh(['shift', 'office', 'targets.detailSchedule.shift']));
    }

    /**
     * Mengakhiri istirahat (kembali) pada sesi ganti jam yang aktif.
     *
     * @param int $sessionId
     * @param string|null $message
     * @return ActionResult
     */
    public function endBreak(int $sessionId, ?string $message = null): ActionResult
    {
        $session = ChangeTimeSession::find($sessionId);
        if (!$session || !$session->isActive()) {
            return new ActionResult(false, 'Sesi ganti jam aktif tidak ditemukan.');
        }

        if (empty($session->break_time)) {
            return new ActionResult(false, 'Anda belum melakukan presensi mulai istirahat.');
        }

        if (!empty($session->back_time)) {
            return new ActionResult(false, 'Presensi kembali istirahat sudah pernah dilakukan.');
        }

        $now = Carbon::now('Asia/Jakarta');
        $nowTime = $now->format('H:i:s');
        $breakStart = Carbon::parse($session->session_date->format('Y-m-d') . ' ' . $session->break_time, 'Asia/Jakarta');
        $breakMinutes = max(0, $now->diffInMinutes($breakStart));

        $session->update([
            'back_time' => $nowTime,
            'back_time_message' => $message,
            'total_break_minutes' => $breakMinutes,
        ]);

        return new ActionResult(true, 'Berhasil kembali dari istirahat ganti jam.', $session->fresh(['shift', 'office', 'targets.detailSchedule.shift']));
    }

    /**
     * Mengakhiri / checkout sesi ganti jam.
     *
     * @param int $sessionId
     * @param float|null $latitude
     * @param float|null $longitude
     * @param string|null $message
     * @return ActionResult
     */
    public function endSession(
        int $sessionId,
        ?float $latitude = null,
        ?float $longitude = null,
        ?string $message = null
    ): ActionResult {
        $session = ChangeTimeSession::with(['intern.user', 'shift', 'targets.detailSchedule.shift'])->find($sessionId);
        if (!$session || !$session->isActive()) {
            return new ActionResult(false, 'Sesi ganti jam aktif tidak ditemukan.');
        }

        $user = $session->intern->user;
        $shift = $session->shift;

        // Validasi GPS
        $isGpsRequired = ($user && $user->is_gps_activate == 1) && (!$shift || $shift->is_gps_active == 1);
        if ($isGpsRequired) {
            if ($latitude === null || $longitude === null) {
                return new ActionResult(false, 'Gagal mendapatkan lokasi GPS. Pastikan GPS aktif dan izin lokasi diberikan.');
            }
            $mapsTrack = $this->locationService->checkIsInOfficeArea((float) $latitude, (float) $longitude, true);
            if ($mapsTrack->isInArea == false) {
                return new ActionResult(false, 'Lokasi Anda saat ini berada di luar area kantor.');
            }
        }

        $now = Carbon::now('Asia/Jakarta');
        $nowTime = $now->format('H:i:s');
        $sessionDateStr = $session->session_date->format('Y-m-d');
        $startDateTime = Carbon::parse($sessionDateStr . ' ' . $session->start_time, 'Asia/Jakarta');

        // Hitung total durasi menit kerja kotor dikurangi total menit istirahat
        $grossMinutes = max(0, $now->diffInMinutes($startDateTime));
        $breakMinutes = (int) $session->total_break_minutes;

        // Jika sedang dalam status istirahat dan belum absen kembali, hitung istirahat otomatis sampai sekarang
        if (!empty($session->break_time) && empty($session->back_time)) {
            $breakStart = Carbon::parse($sessionDateStr . ' ' . $session->break_time, 'Asia/Jakarta');
            $breakMinutes = max(0, $now->diffInMinutes($breakStart));
            $session->back_time = $nowTime;
            $session->total_break_minutes = $breakMinutes;
        }

        // Maksimal akumulasi ganti jam adalah 7 jam 15 menit (435 menit)
        $netWorkMinutes = min(435, max(0, $grossMinutes - $breakMinutes));
        $targetDebtMinutes = (int) $session->total_target_debt_minutes;


        // Simpan sesi dengan status pending_approval
        $session->update([
            'end_time' => $nowTime,
            'total_work_minutes' => $netWorkMinutes,
            'total_break_minutes' => $breakMinutes,
            'status' => 'pending_approval',
            'end_time_message' => $message,
            'latitude_end' => $latitude,
            'longitude_end' => $longitude,
        ]);

        // Kirim notifikasi WhatsApp
        if ($session->intern) {
            $internName = $user->profile->full_name ?? $user->username ?? 'Unknown';
            $this->whatsappService->sendAttendanceNotificationToAllTargets(
                $session->intern,
                $internName,
                'GANTI JAM PULANG (MENUNGGU PERSETUJUAN ADMIN)',
                $nowTime
            );
        }

        return new ActionResult(
            true,
            'Sesi ganti jam telah selesai dan berhasil diajukan ke admin untuk persetujuan.',
            $session->fresh(['shift', 'office', 'targets.detailSchedule.shift'])
        );
    }

    /**
     * Admin menyetujui sesi ganti jam dan melunaskan jadwal & absensi utama secara proporsional.
     *
     * @param int $sessionId
     * @param int $adminUserId
     * @return ActionResult
     */
    public function approveSession(int $sessionId, int $adminUserId): ActionResult
    {
        $session = ChangeTimeSession::with(['intern', 'shift', 'targets.detailSchedule.shift'])->find($sessionId);
        if (!$session || (!$session->isPendingApproval() && !($session->isActive() && !empty($session->end_time)))) {
            return new ActionResult(false, 'Sesi ganti jam tidak ditemukan atau belum selesai presensi pulang.');
        }

        DB::beginTransaction();
        try {
            $now = Carbon::now('Asia/Jakarta');

            $session->update([
                'status' => 'approved',
                'approved_by' => $adminUserId,
                'approved_at' => $now,
            ]);

            // Tandai pendaftaran ganti jam terkait sebagai selesai
            \App\Models\ChangeTimeRegistration::where('intern_id', $session->intern_id)
                ->whereIn('status', ['pending', 'approved'])
                ->update(['status' => 'completed']);

            // Maksimal pelunasan hutang per sesi adalah 7 jam 15 menit (435 menit = 1 shift penuh)
            $remainingWorkMinutes = min((int) ($session->total_work_minutes ?? 0), 435);

            // Pastikan session memiliki target hutang secara FIFO jika belum ada
            if ($session->targets->isEmpty()) {
                $debts = $this->debtCalculationService->getInternDebts($session->intern_id);
                $accumulated = 0;
                foreach ($debts as $debt) {
                    $target = ChangeTimeSessionTarget::create([
                        'change_time_session_id' => $session->id,
                        'detail_schedule_id' => $debt['schedule_id'],
                        'attendance_id' => $debt['raw_schedule']->attendance_id ?? null,
                        'debt_minutes' => $debt['debt_minutes'],
                        'paid_minutes' => 0,
                        'is_fulfilled' => false,
                    ]);
                    $session->targets->push($target);
                    $accumulated += $debt['debt_minutes'];
                    if ($accumulated >= 435) {
                        break;
                    }
                }
            }

            // Urutkan target secara FIFO berdasarkan tanggal jadwal paling lampau
            $sortedTargets = $session->targets->sortBy(function ($t) {
                return $t->detailSchedule?->date ?? '9999-12-31';
            });

            foreach ($sortedTargets as $target) {
                $debtMins = (int) $target->debt_minutes;
                $allocatedPaid = min($remainingWorkMinutes, $debtMins);
                $isFulfilled = ($allocatedPaid >= $debtMins && $debtMins > 0) || ($debtMins <= 0);

                $target->update([
                    'paid_minutes' => $allocatedPaid,
                    'is_fulfilled' => $isFulfilled,
                ]);

                $remainingWorkMinutes = max(0, $remainingWorkMinutes - $allocatedPaid);

                $detailSchedule = $target->detailSchedule;
                if (!$detailSchedule) {
                    continue;
                }

                // Jika target jadwal ini telah lunas 100%
                if ($isFulfilled) {
                    $shift = $detailSchedule->shift;
                    $startTime = $shift->start_time ?? '08:00:00';
                    $endTime = $shift->end_time ?? '16:00:00';
                    $totalMin = (int) ($shift->total_time_in_minute ?? 420);
                    $totalBreakMin = (int) ($shift->break_time_in_minute ?? 60);

                    // 1. Update DetailSchedule
                    $detailSchedule->start_time = $startTime;
                    $detailSchedule->end_time = $endTime;
                    $detailSchedule->attd_status_id = 2; // Hadir
                    $detailSchedule->isChangeSchedule = 1; // Lunas / Bebas Ganti Jam
                    $detailSchedule->is_change_schedule_approved = 1;

                    // 2. Update atau Create Attendance pada jadwal tersebut
                    $attendance = $detailSchedule->attendance;
                    if (!$attendance) {
                        $attendance = Attendance::where('intern_id', $session->intern_id)
                            ->whereDate('date', $detailSchedule->date)
                            ->first();
                    }

                    $attPayload = [
                        'start_time' => Carbon::parse($detailSchedule->date . ' ' . $startTime, 'Asia/Jakarta'),
                        'end_time' => Carbon::parse($detailSchedule->date . ' ' . $endTime, 'Asia/Jakarta'),
                        'break_time' => $shift?->start_break_time ? Carbon::parse($detailSchedule->date . ' ' . $shift->start_break_time, 'Asia/Jakarta') : null,
                        'back_time' => $shift?->end_break_time ? Carbon::parse($detailSchedule->date . ' ' . $shift->end_break_time, 'Asia/Jakarta') : null,
                        'total_min' => $totalMin,
                        'total_break_min' => $totalBreakMin,
                        'keterangan' => 'Hadir (Lunas Ganti Jam)',
                        'is_debt_fulfilled' => true,
                        'debt_fulfilled_session_id' => $session->id,
                        'debt_fulfilled_at' => $now,
                        'start_time_message' => $attendance?->start_time_message ?: 'Ganti Jam Selesai & Disetujui',
                        'end_time_message' => $attendance?->end_time_message ?: 'Ganti Jam Selesai & Disetujui',
                    ];

                    if ($attendance) {
                        $attendance->update($attPayload);
                    } else {
                        $attendance = Attendance::create(array_merge([
                            'intern_id' => $session->intern_id,
                            'date' => $detailSchedule->date,
                        ], $attPayload));
                    }

                    $detailSchedule->attendance_id = $attendance->id;
                    $detailSchedule->save();

                    // Update foreign key attendance_id pada target pivot jika belum ada
                    if (!$target->attendance_id) {
                        $target->update(['attendance_id' => $attendance->id]);
                    }
                } else {
                    // Jika baru terbayar sebagian (antara 50% dan 99%), jadwal tetap aktif dan sisa hutang otomatis berkurang
                    $detailSchedule->save();
                }
            }

            // Bersihkan log chat ganti jam agar tidak memenuhi database
            \App\Models\ChangeTimeNote::where('session_id', $session->id)->delete();
            \App\Models\ChangeTimeNote::whereHas('registration', function ($q) use ($session) {
                $q->where('intern_id', $session->intern_id)->whereIn('status', ['completed', 'rejected', 'cancelled']);
            })->delete();

            DB::commit();

            return new ActionResult(
                true,
                'Sesi ganti jam berhasil disetujui dan seluruh jadwal hutang terkait telah dilunaskan.',
                $session->fresh(['shift', 'office', 'targets.detailSchedule.shift'])
            );
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error approving ChangeTimeSession: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return new ActionResult(false, 'Gagal menyetujui sesi ganti jam: ' . $e->getMessage());
        }
    }

    /**
     * Admin menolak sesi ganti jam pemagang.
     *
     * @param int $sessionId
     * @param int $adminUserId
     * @param string|null $reason
     * @return ActionResult
     */
    public function rejectSession(int $sessionId, int $adminUserId, ?string $reason = null): ActionResult
    {
        $session = ChangeTimeSession::find($sessionId);
        if (!$session || (!$session->isPendingApproval() && !$session->isActive())) {
            return new ActionResult(false, 'Sesi ganti jam tidak ditemukan atau tidak dalam status aktif/menunggu persetujuan.');
        }

        $updateData = [
            'status' => 'rejected',
            'approved_by' => $adminUserId,
            'approved_at' => now(),
            'rejection_note' => $reason ?: 'Ditolak oleh admin',
        ];

        // Jika sesi ditolak saat sedang berlangsung (belum ada jam pulang),
        // set jam pulang sama seperti jam masuknya agar akumulasi durasi kerja menjadi 0
        if (empty($session->end_time)) {
            $updateData['end_time'] = $session->start_time;
            $updateData['total_work_minutes'] = 0;
            $updateData['total_break_minutes'] = 0;
            if (!empty($session->break_time) && empty($session->back_time)) {
                $updateData['back_time'] = $session->break_time;
            }
        } else {
            // Jika sudah ada jam pulang namun ditolak admin, pastikan total_work_minutes menjadi 0
            $updateData['total_work_minutes'] = 0;
        }

        $session->update($updateData);

        // Reset target hutang pivot jika sempat ada alokasi
        $session->targets()->update([
            'paid_minutes' => 0,
            'is_fulfilled' => false,
        ]);

        // Bersihkan log chat ganti jam
        \App\Models\ChangeTimeNote::where('session_id', $session->id)->delete();

        return new ActionResult(true, 'Sesi ganti jam telah ditolak.', $session->fresh());
    }

    /**
     * Ambil sesi ganti jam yang sedang aktif untuk pemagang.
     *
     * @param int $internId
     * @return ChangeTimeSession|null
     */
    public function getActiveSession(int $internId): ?ChangeTimeSession
    {
        return ChangeTimeSession::where('intern_id', $internId)
            ->where('status', 'active')
            ->with(['shift', 'office', 'targets.detailSchedule.shift'])
            ->latest('id')
            ->first();
    }

    /**
     * Ambil sesi ganti jam yang sedang menunggu persetujuan admin untuk pemagang.
     *
     * @param int $internId
     * @return ChangeTimeSession|null
     */
    public function getPendingSession(int $internId): ?ChangeTimeSession
    {
        return ChangeTimeSession::where('intern_id', $internId)
            ->where('status', 'pending_approval')
            ->with(['shift', 'office', 'targets.detailSchedule.shift'])
            ->latest('id')
            ->first();
    }
}
