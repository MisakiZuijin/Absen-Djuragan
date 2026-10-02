<?php

namespace App\Services;

use App\Helper\ActionResult;
use App\Helper\TimeHelper;
use App\Models\Attendance;
use App\Models\ChangeTimeSessionTarget;
use App\Models\DetailSchedule;
use App\Utils\DateNow;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DebtCalculationService
{
    /**
     * Hitung hutang dasar (dalam menit) untuk satu DetailSchedule.
     *
     * @param DetailSchedule $schedule
     * @return int
     */
    public function calculateScheduleBaseDebt(DetailSchedule $schedule): int
    {
        if (!$schedule->shift) {
            return 0;
        }

        // 1. Jika sudah lunas atau izin bebas ganti jam yang disetujui -> 0
        if (
            TimeHelper::isApprovedExcusedLeave($schedule) ||
            (int) $schedule->isChangeSchedule === 1 ||
            (int) $schedule->is_change_schedule_approved === 1
        ) {
            return 0;
        }

        // 2. Jika hari libur / Minggu dan tidak ada presensi masuk -> Hari Libur (0 hutang jam)
        $scheduleDate = Carbon::parse($schedule->date);
        $dateStr = $scheduleDate->toDateString();
        $isSunday = $scheduleDate->isSunday();
        $isHoliday = \App\Models\Holiday::whereDate('date', $dateStr)->exists();

        $attendance = $schedule->attendance;
        if (!$attendance && $schedule->schedule) {
            $attendance = Attendance::where('intern_id', $schedule->schedule->intern_id)
                ->whereDate('date', $schedule->date)
                ->with('permitLogs')
                ->first();
        }

        $hasCheckIn = $attendance && !empty($attendance->start_time);
        if (($isSunday || $isHoliday) && !$hasCheckIn && (int)($schedule->attd_status_id ?? 1) !== 3) {
            return 0;
        }

        // Hitung menit shift
        $shift = $schedule->shift;
        $shiftMinutes = (int) ($shift->total_time_in_minute ?? 0);
        if ($shiftMinutes <= 0 && $shift->start_time && $shift->end_time && $shift->start_time !== '00:00:00') {
            $start = Carbon::parse($shift->start_time);
            $end = Carbon::parse($shift->end_time);
            $break = (int) ($shift->break_time_in_minute ?? 0);
            $shiftMinutes = max(0, $end->diffInMinutes($start) - $break);
        }

        $statusId = (int) ($schedule->attd_status_id ?? 1);

        // 3. Alpha (attd_status_id = 5) -> Hutang full 1 shift
        if ($statusId === 5) {
            return $shiftMinutes;
        }

        // 3. Izin Sakit / Keperluan (attd_status_id = 3)
        if ($statusId === 3) {
            $permitReason = $schedule->permitReason;
            $categoryId = $permitReason?->permit_category_id;
            $description = strtolower($permitReason?->description ?? '');
            $hasProof = !empty($permitReason?->proof_url);
            $isSakit = ($categoryId == 1 || $categoryId == 2 || str_contains($description, 'sakit'));

            if ($isSakit) {
                if ($schedule->isChangeSchedule == 2 || !$hasProof || $categoryId == 2) {
                    return $shiftMinutes;
                }
                return 0; // Izin sakit dengan bukti surat sah
            } else {
                if ($schedule->isChangeSchedule == 2 || !$hasProof) {
                    return $shiftMinutes;
                }
                return 0;
            }
        }

        // 4. Presensi Reguler (attd_status_id = 1 atau 2)
        $attendance = $schedule->attendance;
        if (!$attendance && $schedule->schedule) {
            $attendance = Attendance::where('intern_id', $schedule->schedule->intern_id)
                ->whereDate('date', $schedule->date)
                ->with('permitLogs')
                ->first();
        }

        $workingMinutes = 0;
        if ($attendance && $attendance->start_time && $attendance->end_time) {
            $startTime = Carbon::parse($attendance->start_time);
            $endTime = Carbon::parse($attendance->end_time);
            $totalDuration = $endTime->diffInMinutes($startTime);
            $breakDuration = (int) ($attendance->total_break_min ?? 0);
            $workingMinutes = max(0, $totalDuration - $breakDuration);
        }

        $differenceInMinutes = $shiftMinutes - $workingMinutes;

        // Jika hari ini dan belum pulang: hitung keterlambatan masuk
        $scheduleDate = Carbon::parse($schedule->date);
        if ($scheduleDate->isToday() && $attendance && $attendance->start_time && !$attendance->end_time) {
            $scheduledStartTime = Carbon::parse($schedule->date . ' ' . $shift->start_time);
            $actualStartTime = Carbon::parse($attendance->start_time);
            if ($actualStartTime->isAfter($scheduledStartTime)) {
                $differenceInMinutes = $actualStartTime->diffInMinutes($scheduledStartTime);
            } else {
                $differenceInMinutes = 0;
            }
        }

        // Tambah hutang izin keluar wajib ganti jam jika ada
        $extraLeaveDebt = TimeHelper::mandatoryReplaceDebtMinutes($attendance);
        $differenceInMinutes += $extraLeaveDebt;

        // Toleransi 1 menit
        return $differenceInMinutes > 1 ? max(0, $differenceInMinutes) : 0;
    }

    /**
     * Hitung sisa hutang menit pada suatu DetailSchedule setelah dikurangi sesi ganti jam yang sudah pernah ada.
     *
     * @param DetailSchedule $schedule
     * @param int|null $excludeSessionId
     * @return int
     */
    public function getRemainingDebtMinutes(DetailSchedule $schedule, ?int $excludeSessionId = null): int
    {
        $baseDebt = $this->calculateScheduleBaseDebt($schedule);
        if ($baseDebt <= 0) {
            return 0;
        }

        // 1. Sesi yang sudah disetujui: hitung menit yang benar-benar dibayarkan (paid_minutes)
        $approvedPaidMinutes = ChangeTimeSessionTarget::where('detail_schedule_id', $schedule->id)
            ->whereHas('session', function ($q) use ($excludeSessionId) {
                $q->where('status', 'approved');
                if ($excludeSessionId) {
                    $q->where('id', '!=', $excludeSessionId);
                }
            })
            ->sum('paid_minutes');

        // 2. Sesi yang masih berjalan / menunggu persetujuan: hitung menit yang sedang dialokasikan (debt_minutes)
        $pendingCoveredMinutes = ChangeTimeSessionTarget::where('detail_schedule_id', $schedule->id)
            ->whereHas('session', function ($q) use ($excludeSessionId) {
                $q->whereIn('status', ['active', 'pending_approval']);
                if ($excludeSessionId) {
                    $q->where('id', '!=', $excludeSessionId);
                }
            })
            ->sum('debt_minutes');

        $coveredMinutes = (int) $approvedPaidMinutes + (int) $pendingCoveredMinutes;

        return max(0, $baseDebt - $coveredMinutes);
    }

    /**
     * Mengambil seluruh daftar hutang jam milik pemagang.
     *
     * @param int $internId
     * @param bool $includeToday
     * @return Collection
     */
    public function getInternDebts(int $internId, bool $includeToday = false): Collection
    {
        $dateLimit = $includeToday ? DateNow::getCurrentDateYMD() : Carbon::yesterday()->format('Y-m-d');

        $schedules = DetailSchedule::whereHas('schedule', function ($q) use ($internId) {
            $q->where('intern_id', $internId);
        })
            ->whereDate('date', '<=', $dateLimit)
            ->with(['shift', 'attendance.permitLogs', 'permitReason.category', 'schedule'])
            ->orderBy('date', 'asc') // Urutan kronologis (FIFO)
            ->get();

        $debts = collect();

        foreach ($schedules as $schedule) {
            $remainingMinutes = $this->getRemainingDebtMinutes($schedule);
            if ($remainingMinutes <= 1) {
                continue;
            }

            $scheduleDate = Carbon::parse($schedule->date);
            $hours = round($remainingMinutes / 60, 1);
            $hoursInt = floor($remainingMinutes / 60);
            $minsInt = $remainingMinutes % 60;
            $formattedTime = sprintf('%02d:%02d', $hoursInt, $minsInt);

            $statusId = (int) ($schedule->attd_status_id ?? 1);
            $categoryName = 'Kekurangan Jam Reguler';
            $badgeClass = 'bg-slate-100 text-slate-800 border-slate-300';

            if ($statusId === 5) {
                $categoryName = 'Alpha (Tidak Hadir)';
                $badgeClass = 'bg-rose-100 text-rose-800 border-rose-300';
            } elseif ($statusId === 3) {
                $categoryName = 'Izin (Wajib Ganti Jam)';
                $badgeClass = 'bg-amber-100 text-amber-800 border-amber-300';
            } elseif ($scheduleDate->isToday()) {
                $categoryName = 'Keterlambatan Hari Ini';
                $badgeClass = 'bg-orange-100 text-orange-800 border-orange-300';
            }

            $isSmallDebt = $remainingMinutes < 120; // < 2 jam

            $debts->push([
                'id' => 'schedule_' . $schedule->id,
                'schedule_id' => $schedule->id,
                'schedule_date' => $schedule->date,
                'schedule_date_formatted' => $scheduleDate->locale('id')->isoFormat('D MMM Y'),
                'title' => $scheduleDate->locale('id')->isoFormat('D MMM Y') . ' - ' . $categoryName,
                'category' => $categoryName,
                'badge' => $badgeClass,
                'debt_minutes' => $remainingMinutes,
                'debt_hours' => $hours,
                'debt_time_formatted' => $formattedTime,
                'detail' => sprintf('%s Jam (%d Menit)', $hours, $remainingMinutes),
                'note' => 'Ganti jam tanggal ' . $scheduleDate->format('d/m/Y') . " ({$formattedTime})",
                'is_small_debt' => $isSmallDebt,
                'shift_name' => $schedule->shift?->name ?? 'Reguler',
                'raw_schedule' => $schedule,
            ]);
        }

        return $debts;
    }

    /**
     * Validasi target jadwal hutang yang dipilih pemagang untuk sesi ganti jam baru.
     *
     * Aturan:
     * 1. Target tidak boleh kosong.
     * 2. Jika memilih 1 jadwal: Bebas berapapun durasinya (normal/kecil).
     * 3. Jika memilih > 1 jadwal:
     *    - SEMUA jadwal yang dipilih WAJIB merupakan hutang kecil (< 2 jam / 120 menit).
     *    - Total durasi gabungan TIDAK BOLEH melebihi 7 jam (420 menit).
     *
     * @param int $internId
     * @param array $scheduleIds
     * @return ActionResult
     */
    public function validateSelectedDebts(int $internId, array $scheduleIds): ActionResult
    {
        if (empty($scheduleIds)) {
            return new ActionResult(false, 'Harap pilih minimal 1 jadwal hutang untuk diganti.');
        }

        $allDebts = $this->getInternDebts($internId, true)->keyBy('schedule_id');
        $selectedItems = collect();
        $totalDebtMinutes = 0;

        foreach ($scheduleIds as $scheduleId) {
            $debt = $allDebts->get((int) $scheduleId);
            if (!$debt) {
                return new ActionResult(false, "Jadwal dengan ID {$scheduleId} tidak memiliki hutang jam yang valid atau sudah dilunasi.");
            }
            $selectedItems->push($debt);
            $totalDebtMinutes += (int) $debt['debt_minutes'];
        }

        $count = $selectedItems->count();

        // Kasus 1 Jadwal: Boleh langsung diproses berapapun durasinya
        if ($count === 1) {
            return new ActionResult(true, 'Target hutang valid.', [
                'total_target_debt_minutes' => $totalDebtMinutes,
                'target_debts' => $selectedItems,
            ]);
        }

        // Kasus > 1 Jadwal: Batas maksimal total gabungan 7 jam (420 menit)
        $maxAllowedMinutes = 420;
        if ($totalDebtMinutes > $maxAllowedMinutes) {
            $totalHours = round($totalDebtMinutes / 60, 1);
            return new ActionResult(
                false,
                "Total durasi gabungan hutang yang dipilih adalah {$totalHours} jam ({$totalDebtMinutes} menit), melebihi batas maksimal 7 jam (420 menit) untuk 1 sesi."
            );
        }

        return new ActionResult(true, 'Target gabungan hutang valid.', [
            'total_target_debt_minutes' => $totalDebtMinutes,
            'target_debts' => $selectedItems,
        ]);
    }
}
