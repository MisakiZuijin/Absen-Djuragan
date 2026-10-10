<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\DetailSchedule;
use App\Models\Holiday;
use App\Models\Intern;
use App\Models\Office;
use App\Models\PiketSchedule;
use App\Models\Schedule;
use App\Models\Shift;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PiketSchedulingService
{
    /**
     * Generate jadwal piket otomatis dari jadwal kerja (detail_schedules) yang sudah aktif.
     *
     * Aturan:
     * 1. 1x piket per minggu kalender untuk setiap pemagang.
     * 2. Hanya dijadwalkan pada hari di mana pemagang berstatus WFO.
     * 3. Piket mengikuti jam shift pemagang pada hari WFO tersebut.
     * 4. Jika pemagang hybrid (misal 50:50 dengan 3 hari WFO), sistem memilih 1 hari dari hari WFO-nya.
     * 5. Pemagang yang minggu tersebut 100% WFH dilewati (0 piket).
    /**
     * Generate jadwal piket otomatis dari jadwal kerja (detail_schedules) yang sudah aktif.
     *
     * Aturan & Formula:
     * 1. Mengikuti batas maksimal piket per minggu (default 1x atau sesuai konfigurasi kantor/parameter).
     * 2. Isolasi Kantor Mutlak: Pemagang di kantor 2 ya hanya piket di kantor 2.
     * 3. Pemerataan Seimbang: Total beban piket pemagang di kantor tersebut dibagi jumlah hari kerja (Senin - Sabtu).
     * 4. Pemagang 100% WFH / Remote dilewati.
     * 5. Sinkronisasi jadwal & shift agar pemagang terhubung resmi ke jadwal kerjanya.
     *
     * @param string $startDate Format YYYY-MM-DD
     * @param string $endDate Format YYYY-MM-DD
     * @param int|null $officeId Filter kantor opsional
     * @param int|null $maxPiketPerWeek Batas maksimal piket per minggu opsional
     * @return array
     */
    public function generatePiketForPeriod(string $startDate, string $endDate, ?int $officeId = null, ?int $maxPiketPerWeek = null): array
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        if ($end->lt($start)) {
            return [
                'success' => false,
                'message' => 'Tanggal selesai tidak boleh lebih awal dari tanggal mulai.',
            ];
        }

        // 1. Ambil daftar hari libur nasional dalam rentang tanggal
        $holidays = Holiday::whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->pluck('date')
            ->map(fn($d) => Carbon::parse($d)->toDateString())
            ->toArray();

        // 2. Bagi rentang tanggal menjadi minggu-minggu kalender (Senin - Minggu)
        $period = CarbonPeriod::create($start, $end);
        $weeks = [];
        foreach ($period as $dt) {
            $wKey = $dt->format('o-W');
            if (!isset($weeks[$wKey])) {
                $weeks[$wKey] = [
                    'start' => $dt->copy()->startOfWeek(Carbon::MONDAY),
                    'end' => $dt->copy()->endOfWeek(Carbon::SUNDAY),
                ];
            }
        }

        // 3. Tentukan kantor target yang diproses
        if ($officeId && $officeId > 0) {
            $targetOffices = Office::where('id', $officeId)->get();
        } else {
            $targetOffices = Office::all();
        }

        if ($targetOffices->isEmpty()) {
            return [
                'success' => false,
                'message' => 'Kantor yang dipilih tidak ditemukan.',
            ];
        }

        $allShifts = Shift::orderBy('start_time')->get();
        $defaultShift = $allShifts->first();

        $piketRecordsToInsert = [];
        $assignedInternsCount = 0;
        $dailyDistribution = [];
        $dayNameDistribution = [
            'Senin' => 0,
            'Selasa' => 0,
            'Rabu' => 0,
            'Kamis' => 0,
            'Jumat' => 0,
            'Sabtu' => 0,
        ];

        // 4. Proses Tiap Minggu Kalender dan Tiap Kantor Secara Terisolasi Mutlak
        foreach ($weeks as $wKey => $wInfo) {
            $weekStart = $wInfo['start']->copy()->startOfDay();
            $weekEnd = $wInfo['end']->copy()->endOfDay();

            // Batasi hari dengan rentang filter yang dipilih pengguna
            $effectiveStart = $weekStart->lt($start) ? $start->copy() : $weekStart->copy();
            $effectiveEnd = $weekEnd->gt($end) ? $end->copy() : $weekEnd->copy();

            // Tentukan hari kerja yang valid (Senin s/d Sabtu, selain Minggu & libur nasional)
            $workingDays = [];
            $curD = $effectiveStart->copy();
            while ($curD->lte($effectiveEnd)) {
                $dStr = $curD->toDateString();
                if (!$curD->isSunday() && !in_array($dStr, $holidays, true)) {
                    $workingDays[] = [
                        'date' => $dStr,
                        'day_name' => $curD->locale('id')->translatedFormat('l'),
                        'carbon' => $curD->copy(),
                    ];
                }
                $curD->addDay();
            }

            $numWorkingDays = count($workingDays);
            if ($numWorkingDays === 0) {
                continue;
            }

            $allWorkingDates = array_column($workingDays, 'date');

            foreach ($targetOffices as $office) {
                // Tentukan batas maksimal piket per pemagang per minggu untuk kantor ini
                $officeLimit = $maxPiketPerWeek 
                    ?: ((int)($office->piket_max_per_week ?? 0) > 0 ? (int)$office->piket_max_per_week : (int)\App\Models\AutomationSetting::getInt('piket_max_per_week', 1));
                $officeLimit = max(1, min(7, $officeLimit));

                // Sinkronkan integritas office_id pada DetailSchedule agar pemagang kantor ini terikat benar
                DetailSchedule::whereIn('schedule_id', Schedule::where('office_id', $office->id)->pluck('id'))
                    ->where('office_id', '!=', $office->id)
                    ->update(['office_id' => $office->id]);

                // 1. Tentukan shift yang valid dan aktif di kantor ini
                $enabledShiftIds = \App\Models\OfficeShiftCapacity::where('office_id', $office->id)
                    ->where('is_enabled', true)
                    ->where('max_capacity', '>', 0)
                    ->pluck('shift_id')
                    ->toArray();

                // Jika tidak ada konfigurasi di OfficeShiftCapacity, gunakan shift dari jadwal yang ada di kantor ini
                if (empty($enabledShiftIds)) {
                    $enabledShiftIds = Schedule::where('office_id', $office->id)
                        ->whereNotNull('shift_id')
                        ->pluck('shift_id')
                        ->unique()
                        ->values()
                        ->toArray();
                }

                // Cek apakah kantor ini hanya memiliki 1 shift tunggal
                $isSingleShiftOffice = (count($enabledShiftIds) === 1);
                $singleShiftId = $isSingleShiftOffice ? $enabledShiftIds[0] : null;

                // 2. Ambil seluruh jadwal aktif pemagang yang terdaftar di kantor ini
                $officeSchedules = Schedule::with(['intern.user.profile', 'intern.division', 'intern.school', 'detailSchedules.shift'])
                    ->where('office_id', $office->id)
                    ->whereDate('start_period', '<=', $effectiveEnd->toDateString())
                    ->whereDate('end_period', '>=', $effectiveStart->toDateString())
                    ->get();

                // Kumpulkan pemagang WFO dan kelompokkan berdasarkan shift
                $internsByShift = [];

                foreach ($officeSchedules as $sched) {
                    $intern = $sched->intern;
                    if (!$intern) {
                        continue;
                    }

                    // Saring: lewati pemagang 100% remote / WFH
                    $isRemote = (bool)($intern->school?->is_remote_school ?? false)
                        || ($intern->work_placement_type === 'wfh_only');
                    if ($isRemote) {
                        continue;
                    }

                    // Ambil detail schedules WFO pemagang ini di rentang aktif
                    $wfoDetailList = $sched->detailSchedules->filter(function ($ds) use ($effectiveStart, $effectiveEnd, $holidays) {
                        $c = Carbon::parse($ds->date);
                        $dStr = $c->toDateString();
                        return $c->between($effectiveStart, $effectiveEnd)
                            && !$c->isSunday()
                            && !in_array($dStr, $holidays, true)
                            && in_array($ds->work_type, ['wfo', 'split_shift']);
                    });

                    $availableDates = $wfoDetailList->pluck('date')
                        ->map(fn($d) => Carbon::parse($d)->toDateString())
                        ->unique()
                        ->values()
                        ->toArray();

                    // Jika tidak ada hari WFO sama sekali dan seluruh jadwalnya WFH, lewati
                    if (empty($availableDates) && $sched->detailSchedules->isNotEmpty()) {
                        $hasAnyWfo = $sched->detailSchedules->whereIn('work_type', ['wfo', 'split_shift'])->isNotEmpty();
                        if (!$hasAnyWfo) {
                            continue;
                        }
                    }

                    // Tentukan shift untuk pemagang ini:
                    // Jika kantor ini adalah kantor 1 shift, paksa gunakan 1 shift tersebut (tidak ada shift lain)
                    if ($isSingleShiftOffice && $singleShiftId) {
                        $assignedShiftId = $singleShiftId;
                    } else {
                        // Jika kantor memiliki beberapa shift, gunakan shift dari detail schedule / schedule pemagang
                        $candShiftId = $wfoDetailList->first()?->shift_id
                            ?: ($sched->shift_id ?: ($intern->shift_id ?: ($defaultShift?->id ?? 1)));
                        // Jika shift pemagang ada dalam daftar enabledShiftIds, gunakan. Jika tidak, gunakan shift pertama yang valid
                        if (!empty($enabledShiftIds) && !in_array($candShiftId, $enabledShiftIds)) {
                            $assignedShiftId = $enabledShiftIds[0];
                        } else {
                            $assignedShiftId = $candShiftId;
                        }
                    }

                    $internsByShift[$assignedShiftId][] = [
                        'intern_id' => $intern->id,
                        'intern' => $intern,
                        'schedule' => $sched,
                        'available_dates' => $availableDates,
                        'shift_id' => $assignedShiftId,
                        'wfo_days_count' => count($availableDates),
                        'wfo_detail_map' => $wfoDetailList->keyBy('date'),
                    ];
                }

                // =========================================================================
                // FORMULA PEMERATAAN MUTLAK PER SHIFT:
                // Mengikuti batas maksimal piket per pemagang per minggu ($officeLimit kali)
                // =========================================================================
                foreach ($internsByShift as $shiftId => $shiftInterns) {
                    $totalInThisShift = count($shiftInterns);
                    if ($totalInThisShift === 0) {
                        continue;
                    }

                    // Siapkan data pemagang dan hitung total kuota penugasan yang dibutuhkan di minggu ini
                    $totalDutiesNeeded = 0;
                    foreach ($shiftInterns as &$iData) {
                        $cDates = array_values(array_unique(array_merge($iData['available_dates'], $allWorkingDates)));
                        $iData['cand_dates'] = $cDates;
                        // Batasi target piket dengan batas kantor, jumlah hari kerja, dan hari WFO pemagang
                        $iData['target_piket_count'] = min($officeLimit, count($cDates), $numWorkingDays);
                        $iData['assigned_dates'] = [];
                        $totalDutiesNeeded += $iData['target_piket_count'];
                    }
                    unset($iData);

                    $baseQuota = intdiv($totalDutiesNeeded, $numWorkingDays);
                    $remainder = $totalDutiesNeeded % $numWorkingDays;

                    $shiftDailyCaps = [];
                    $shiftDailySlots = [];
                    foreach ($workingDays as $idx => $wd) {
                        $d = $wd['date'];
                        $cap = $baseQuota + ($idx < $remainder ? 1 : 0);
                        $shiftDailyCaps[$d] = $cap;
                        $shiftDailySlots[$d] = [];
                    }

                    // Lakukan alokasi multi-pass hingga batas $officeLimit putaran
                    for ($round = 1; $round <= $officeLimit; $round++) {
                        // Saring pemagang yang masih memerlukan penugasan piket pada putaran ini
                        $eligibleInterns = [];
                        foreach ($shiftInterns as $idx => $it) {
                            if (count($it['assigned_dates']) < $it['target_piket_count']) {
                                $eligibleInterns[$idx] = $it;
                            }
                        }

                        if (empty($eligibleInterns)) {
                            break;
                        }

                        // Urutkan pemagang: yang memiliki sisa hari tersedia lebih sedikit diprioritaskan
                        uasort($eligibleInterns, function ($a, $b) {
                            $remA = count(array_diff($a['cand_dates'], $a['assigned_dates']));
                            $remB = count(array_diff($b['cand_dates'], $b['assigned_dates']));
                            if ($remA !== $remB) {
                                return $remA <=> $remB;
                            }
                            return count($a['assigned_dates']) <=> count($b['assigned_dates']);
                        });

                        foreach ($eligibleInterns as $origIdx => $internData) {
                            // Pemagang hanya boleh piket 1x per hari (tidak boleh dobel piket di tanggal yang sama)
                            $availableCandidateDates = array_values(array_diff($internData['cand_dates'], $internData['assigned_dates']));
                            if (empty($availableCandidateDates)) {
                                continue;
                            }

                            // Urutkan kandidat tanggal berdasarkan sisa kapasitas hari yang masih kosong
                            usort($availableCandidateDates, function ($d1, $d2) use ($shiftDailyCaps, $shiftDailySlots) {
                                $rem1 = ($shiftDailyCaps[$d1] ?? 0) - count($shiftDailySlots[$d1] ?? []);
                                $rem2 = ($shiftDailyCaps[$d2] ?? 0) - count($shiftDailySlots[$d2] ?? []);
                                return $rem2 <=> $rem1;
                            });

                            $assigned = false;
                            foreach ($availableCandidateDates as $d) {
                                if (isset($shiftDailyCaps[$d]) && count($shiftDailySlots[$d]) < $shiftDailyCaps[$d]) {
                                    $shiftDailySlots[$d][] = $internData;
                                    $shiftInterns[$origIdx]['assigned_dates'][] = $d;
                                    $assigned = true;
                                    break;
                                }
                            }

                            // Fallback jika semua hari yang tersedia telah memenuhi kapasitas dasar,
                            // tempatkan di hari yang memiliki penugasan terendah pada shift ini
                            if (!$assigned) {
                                $minDay = $availableCandidateDates[0];
                                $minCount = count($shiftDailySlots[$minDay] ?? []);
                                foreach ($availableCandidateDates as $d) {
                                    $cnt = count($shiftDailySlots[$d] ?? []);
                                    if ($cnt < $minCount) {
                                        $minDay = $d;
                                        $minCount = $cnt;
                                    }
                                }
                                $shiftDailySlots[$minDay][] = $internData;
                                $shiftInterns[$origIdx]['assigned_dates'][] = $minDay;
                            }
                        }
                    }

                    // =========================================================================
                    // SIMPAN JADWAL PIKET UNTUK SHIFT INI
                    // =========================================================================
                    $shiftModel = $allShifts->firstWhere('id', $shiftId);
                    $shiftName = $shiftModel?->name ?? 'Reguler';

                    foreach ($workingDays as $wd) {
                        $assignedDate = $wd['date'];
                        $assignedList = $shiftDailySlots[$assignedDate] ?? [];

                        foreach ($assignedList as $item) {
                            $internId = $item['intern_id'];
                            $sched = $item['schedule'];

                            // Cek apakah pemagang sudah memiliki DetailSchedule di tanggal ini
                            $existingDs = $item['wfo_detail_map']->get($assignedDate)
                                ?? DetailSchedule::where('schedule_id', $sched->id)
                                    ->where('date', $assignedDate)
                                    ->first();

                            if ($existingDs) {
                                $finalDsId = $existingDs->id;
                                if ($existingDs->office_id != $office->id || $existingDs->shift_id != $shiftId) {
                                    $existingDs->update([
                                        'office_id' => $office->id,
                                        'shift_id' => $shiftId,
                                    ]);
                                }
                            } else {
                                // Buat Attendance & DetailSchedule resmi agar jadwal kerja terdaftar valid
                                $attd = Attendance::firstOrCreate(
                                    [
                                        'intern_id' => $internId,
                                        'date' => $assignedDate,
                                    ],
                                    []
                                );

                                $newDs = DetailSchedule::create([
                                    'attendance_id' => $attd->id,
                                    'schedule_id' => $sched->id,
                                    'shift_id' => $shiftId,
                                    'office_id' => $office->id,
                                    'date' => $assignedDate,
                                    'work_type' => 'wfo',
                                    'attd_status_id' => 1,
                                    'isChangeSchedule' => 0,
                                ]);
                                $finalDsId = $newDs->id;
                            }

                            $piketRecordsToInsert[] = [
                                'intern_id' => $internId,
                                'office_id' => $office->id,
                                'shift_id' => $shiftId,
                                'detail_schedule_id' => $finalDsId,
                                'date' => $assignedDate,
                                'notes' => 'Piket Reguler Shift ' . $shiftName,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];

                            $assignedInternsCount++;
                            $dailyDistribution[$assignedDate] = ($dailyDistribution[$assignedDate] ?? 0) + 1;
                            $indonesianDay = $wd['day_name'];
                            if (isset($dayNameDistribution[$indonesianDay])) {
                                $dayNameDistribution[$indonesianDay]++;
                            }
                        }
                    }
                }
            }
        }

        // 5. Eksekusi Penyimpanan Atomik ke Database
        DB::beginTransaction();
        try {
            // 5.1. Bersihkan jadwal piket lama pada rentang tanggal dan kantor yang dipilih
            $deleteQuery = PiketSchedule::whereBetween('date', [$start->toDateString(), $end->toDateString()]);
            if ($officeId && $officeId > 0) {
                $deleteQuery->where('office_id', $officeId);
            }
            $deleteQuery->delete();

            // 5.2. Masukkan penugasan piket baru secara massal (chunk 100 entri)
            if (!empty($piketRecordsToInsert)) {
                foreach (array_chunk($piketRecordsToInsert, 100) as $chunk) {
                    PiketSchedule::insert($chunk);
                }
            }

            DB::commit();

            \Illuminate\Support\Facades\Cache::forget('offices_all');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('PiketSchedulingService: Gagal menyimpan jadwal piket: ' . $th->getMessage(), [
                'trace' => $th->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan database saat menyimpan jadwal piket: ' . $th->getMessage(),
            ];
        }

        $appliedLimitText = $maxPiketPerWeek ? "{$maxPiketPerWeek}x per minggu" : "sesuai pengaturan kantor";
        return [
            'success' => true,
            'message' => "Berhasil membagikan {$assignedInternsCount} penugasan piket otomatis secara merata per kantor dari Senin sampai Sabtu (Batas: {$appliedLimitText})!",
            'total_piket' => count($piketRecordsToInsert),
            'interns_assigned' => $assignedInternsCount,
            'max_piket_per_week' => $maxPiketPerWeek,
            'period' => [
                'start' => $start->toDateString(),
                'end' => $end->toDateString(),
            ],
            'day_name_distribution' => $dayNameDistribution,
            'daily_distribution' => $dailyDistribution,
        ];
    }

    /**
     * Ambil roster jadwal piket lengkap dengan relasi untuk ditampilkan di tabel kalender.
     *
     * @param string $startDate Format YYYY-MM-DD
     * @param string $endDate Format YYYY-MM-DD
     * @param int|null $officeId Filter kantor opsional
     * @return Collection
     */
    public function getPiketRoster(string $startDate, string $endDate, ?int $officeId = null): Collection
    {
        $query = PiketSchedule::with([
            'intern.user.profile',
            'intern.division',
            'intern.school',
            'office',
            'shift',
            'detailSchedule'
        ])
        ->whereBetween('date', [$startDate, $endDate])
        ->orderBy('date', 'asc')
        ->orderBy('office_id', 'asc')
        ->orderBy('shift_id', 'asc');

        if ($officeId && $officeId > 0) {
            $query->where('office_id', $officeId);
        }

        return $query->get();
    }

    /**
     * Reset / Hapus jadwal piket pada rentang tanggal tertentu.
     *
     * @param string $startDate
     * @param string $endDate
     * @param int|null $officeId
     * @return int Jumlah entri yang dihapus
     */
    public function resetPiketForPeriod(string $startDate, string $endDate, ?int $officeId = null): int
    {
        $query = PiketSchedule::whereBetween('date', [$startDate, $endDate]);
        if ($officeId && $officeId > 0) {
            $query->where('office_id', $officeId);
        }
        return $query->delete();
    }

    /**
     * Ambil informasi jadwal piket pemagang tertentu pada minggu berjalan.
     * Mendukung 1x maupun multiple penugasan piket per minggu.
     *
     * @param int $internId
     * @param Carbon|null $dateTarget
     * @return array
     */
    public function getInternWeeklyPiketInfo(int $internId, ?Carbon $dateTarget = null): array
    {
        $target = $dateTarget ?: now();
        $startOfWeek = $target->copy()->startOfWeek(Carbon::MONDAY)->toDateString();
        $endOfWeek = $target->copy()->endOfWeek(Carbon::SUNDAY)->toDateString();
        $todayStr = $target->toDateString();

        // 1. Ambil seluruh jadwal piket pemagang ini di minggu ini (bisa 1x atau lebih sesuai setting)
        $myPiketsThisWeek = PiketSchedule::with(['office', 'shift'])
            ->where('intern_id', $internId)
            ->whereBetween('date', [$startOfWeek, $endOfWeek])
            ->orderBy('date', 'asc')
            ->get();

        // 2. Cek apakah ada jadwal piket hari ini
        $myPiketToday = $myPiketsThisWeek->first(function ($p) use ($target) {
            return Carbon::parse($p->date)->isSameDay($target);
        });

        $todayTeammates = collect();
        $isPiketToday = ($myPiketToday !== null);

        if ($isPiketToday) {
            $todayTeammates = PiketSchedule::with(['intern.user.profile', 'intern.division'])
                ->where('date', $todayStr)
                ->where('office_id', $myPiketToday->office_id)
                ->where('shift_id', $myPiketToday->shift_id)
                ->where('intern_id', '!=', $internId)
                ->get();
        }

        // Tentukan 'my_piket' utama untuk kompatibilitas tampilan lama
        // Prioritas: hari ini jika piket hari ini, atau piket yang akan datang, atau piket pertama
        $primaryPiket = $myPiketToday 
            ?: $myPiketsThisWeek->first(fn($p) => Carbon::parse($p->date)->gte($target->copy()->startOfDay()))
            ?: $myPiketsThisWeek->first();

        return [
            'has_piket_this_week' => $myPiketsThisWeek->isNotEmpty(),
            'is_piket_today' => $isPiketToday,
            'my_piket' => $primaryPiket,
            'my_pikets' => $myPiketsThisWeek,
            'piket_count_this_week' => $myPiketsThisWeek->count(),
            'piket_dates' => $myPiketsThisWeek->pluck('date')->map(fn($d) => Carbon::parse($d)->toDateString())->toArray(),
            'today_teammates' => $todayTeammates,
            'week_period' => [
                'start' => $startOfWeek,
                'end' => $endOfWeek,
            ],
        ];
    }
}
