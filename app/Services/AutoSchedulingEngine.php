<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\AutomationSetting;
use App\Models\DetailSchedule;
use App\Models\Division;
use App\Models\Holiday;
use App\Models\Intern;
use App\Models\InternConflictRule;
use App\Models\Office;
use App\Models\OfficeDivision;
use App\Models\OfficeShiftCapacity;
use App\Models\Schedule;
use App\Models\Shift;
use App\Services\Scheduling\DistanceCalculator;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * AutoSchedulingEngine
 *
 * Mesin Penjadwalan Otomatis Berbasis Pipeline Multi-Tahap (Multi-Stage Constrained Pipeline).
 *
 * Arsitektur:
 * 1. Pre-flight & Deduplikasi Bersih (Validasi masa magang, koordinat kos, pembersihan data residual).
 * 2. Determinasi Skema Kerja & Kohort Hybrid (WFO vs WFH disaring di awal: Remote lock, 50% A/B isolation).
 * 3. Fair Balanced Load Balancer (Pemerataan proporsional divisi antar shift, batasan kuota 2 lapis).
 * 4. Atomic Deploy & Persistence (Purge date-range lama sebelum insert baru, jaminan 0% duplicate).
 */
class AutoSchedulingEngine
{
    /**
     * Kuota kursi per shift per kantor: [office_id][shift_id] => int.
     * Nilai selalu <= kapasitas meja fisik kantor (offices.capacity).
     */
    protected array $officeShiftCapacity = [];

    /**
     * Peta status aktif shift per kantor: [office_id][shift_id] => bool.
     */
    protected array $officeShiftEnabled = [];

    /**
     * Jalankan simulasi penjadwalan cerdas (Dry Run) tanpa menyimpan perubahan ke database.
     */
    public function simulate(string|Carbon $startDate, string|Carbon $endDate, array $divisionIds = [], array $options = []): array
    {
        return $this->executeEngine($startDate, $endDate, $divisionIds, false, $options);
    }

    /**
     * Terapkan hasil penjadwalan cerdas langsung ke database (Deploy Final).
     */
    public function deploy(string|Carbon $startDate, string|Carbon $endDate, array $divisionIds = [], array $options = []): array
    {
        return $this->executeEngine($startDate, $endDate, $divisionIds, true, $options);
    }

    /**
     * Eksekutor inti pipeline penjadwalan multi-tahap.
     */
    protected function executeEngine(string|Carbon $startDate, string|Carbon $endDate, array $divisionIds, bool $persistToDb, array $options): array
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        if ($end->lt($start)) {
            return [
                'success' => false,
                'message' => 'Tanggal akhir tidak boleh lebih awal dari tanggal awal.',
                'summary' => [],
                'warnings' => [],
                'allocations' => [],
                'unconfigured_interns' => [],
                'unconfigured_count' => 0,
                'unconfigured_note' => null,
            ];
        }

        // =========================================================================
        // TAHAP 1: PRE-FLIGHT, MASTER DATA, DAN PRE-KALKULASI JARAK
        // =========================================================================

        // 1.1. Pengaturan Operasional Kalender
        $anchorDateStr = AutomationSetting::get('rotation_anchor_date', '2026-01-05');
        $anchorDate = Carbon::parse($anchorDateStr)->startOfDay();

        $includeSunday = $options['include_sundays'] ?? AutomationSetting::getBool('include_sundays', false);
        $skipHolidays = $options['skip_holidays'] ?? AutomationSetting::getBool('skip_holidays', true);

        // 1.2. Ambil Hari Libur Nasional dalam Rentang Tanggal
        $holidays = Holiday::whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->pluck('date')
            ->map(fn($d) => Carbon::parse($d)->toDateString())
            ->toArray();

        // Jika opsi lewati hari libur aktif tetapi tabel libur untuk tahun simulasi masih kosong, lakukan auto-sync
        if ($skipHolidays && empty($holidays)) {
            $hasHolidaysForYear = Holiday::whereYear('date', $start->year)->exists();
            if (!$hasHolidaysForYear) {
                try {
                    app(\App\Services\HolidaySyncService::class)->syncHolidays((int)$start->year, false);
                    $holidays = Holiday::whereBetween('date', [$start->toDateString(), $end->toDateString()])
                        ->pluck('date')
                        ->map(fn($d) => Carbon::parse($d)->toDateString())
                        ->toArray();
                } catch (\Throwable $th) {
                    \Illuminate\Support\Facades\Log::warning("AutoSchedulingEngine: Auto-sync holidays failed: " . $th->getMessage());
                }
            }
        }

        // 1.3. Master Data Kantor & Shift
        $offices = Office::with(['divisions', 'shiftCapacities', 'coordinates'])->get()->keyBy('id');
        $shifts = Shift::orderBy('start_time')->get()->keyBy('id');

        // Lengkapi jam WFO/WFH split shift in-memory jika belum terisi
        foreach ($shifts as $s) {
            if ($s->is_split_shift) {
                $s->split_wfo_start = $s->split_wfo_start ?: $s->start_time;
                $s->split_wfo_end = $s->split_wfo_end ?: $s->end_time;
            }
        }

        // 1.4. Peta Shift Aktif & Kuota Kursi Per-Shift per Kantor
        // Batasan Lapis 1: Kuota shift tidak pernah melebihi meja fisik kantor!
        $officeShiftEnabled = [];
        $this->officeShiftCapacity = [];
        $this->officeShiftEnabled = [];
        $rawCapacities = OfficeShiftCapacity::all();
        foreach ($rawCapacities as $cap) {
            $isEnabled = (bool) $cap->is_enabled;
            $officeShiftEnabled[$cap->office_id][$cap->shift_id] = $isEnabled;
            $this->officeShiftEnabled[$cap->office_id][$cap->shift_id] = $isEnabled;

            $physicalCap = (int) ($offices[$cap->office_id]?->capacity ?? 15);
            $shiftCap = (int) $cap->max_capacity;

            // Jika shift dinonaktifkan untuk kantor ini, kuota kursi WAJIB 0
            if (!$isEnabled) {
                $this->officeShiftCapacity[$cap->office_id][$cap->shift_id] = 0;
            } else {
                $this->officeShiftCapacity[$cap->office_id][$cap->shift_id] = ($shiftCap > 0)
                    ? min($shiftCap, $physicalCap)
                    : $physicalCap;
            }
        }

        // 1.5. Matriks Izin Kantor per Divisi
        $divisionOffices = [];
        $officeDivisions = OfficeDivision::where('is_active', true)->get();
        foreach ($officeDivisions as $od) {
            $divisionOffices[$od->division_id][] = (int) $od->office_id;
        }

        // 1.6. Aturan Konflik Pasangan Pemagang
        $conflictRules = InternConflictRule::where('is_active', true)->get();
        $conflictPairs = [];
        foreach ($conflictRules as $cr) {
            $conflictPairs[$cr->intern_id_1][] = $cr->intern_id_2;
            $conflictPairs[$cr->intern_id_2][] = $cr->intern_id_1;
        }

        // 1.7. Ambil Data Pemagang Aktif
        $internQuery = Intern::with([
            'user.profile',
            'school',
            'division.scheduleRule',
            'lockedOffice',
            'blacklistedOffice'
        ])->whereHas('user', function ($q) {
            $q->where('is_active', 1);
        });

        if (!empty($divisionIds)) {
            $internQuery->whereIn('division_id', $divisionIds);
        }

        if (!empty($options['intern_ids']) && is_array($options['intern_ids'])) {
            $internQuery->whereIn('id', $options['intern_ids']);
        }

        $allQueriedInterns = $internQuery->get();

        if ($allQueriedInterns->isEmpty()) {
            return [
                'success' => false,
                'message' => 'Tidak ada pemagang aktif yang ditemukan untuk kriteria yang dipilih.',
                'summary' => [],
                'warnings' => [],
                'allocations' => [],
                'unconfigured_interns' => [],
                'unconfigured_count' => 0,
                'unconfigured_note' => null,
            ];
        }

        // 1.8. Saring Pemagang Valid vs Belum Diset Periode Magang
        $unconfiguredInterns = [];
        $validInterns = collect();

        foreach ($allQueriedInterns as $intern) {
            if (empty($intern->start_date) || empty($intern->end_date)) {
                $unconfiguredInterns[] = [
                    'id' => $intern->id,
                    'name' => $intern->user?->profile?->full_name ?? $intern->user?->username ?? "ID #{$intern->id}",
                    'nim' => $intern->nim ?? '-',
                    'division' => $intern->division?->name ?? 'Umum',
                    'reason' => 'Periode magang belum diset (start_date / end_date kosong)'
                ];
            } else {
                $validInterns->push($intern);
            }
        }

        $unconfiguredCount = count($unconfiguredInterns);
        $unconfiguredNote = $unconfiguredCount > 0
            ? 'Beberapa pemagang belum bisa didaftarkan jadwal karena belum diset periode magangnya.'
            : null;

        if ($validInterns->isEmpty()) {
            return [
                'success' => false,
                'message' => 'Seluruh pemagang yang dipilih belum memiliki periode magang (start_date & end_date) yang valid.',
                'summary' => [],
                'warnings' => [],
                'allocations' => [],
                'unconfigured_interns' => $unconfiguredInterns,
                'unconfigured_count' => $unconfiguredCount,
                'unconfigured_note' => $unconfiguredNote,
            ];
        }

        // 1.9. Pre-kalkulasi Jarak Haversine ke Setiap Kantor
        $distanceMatrix = [];
        $missingKosWarnings = [];

        foreach ($validInterns as $intern) {
            $internId = $intern->id;
            $hasCoords = DistanceCalculator::getInternCoordinates($intern) !== null;

            if (!$hasCoords && !($intern->school?->is_remote_school || $intern->work_placement_type === 'wfh_only')) {
                $missingKosWarnings[$internId] = "Pemagang {$intern->user?->profile?->full_name} belum menentukan titik koordinat kos (jarak diestimasi default).";
            }

            foreach ($offices as $officeId => $office) {
                $distInfo = DistanceCalculator::getInternOfficeDistance($intern, $office);
                $distanceMatrix[$internId][$officeId] = $distInfo['distance_km'];
            }
        }

        // Matriks Okupansi & Penugasan:
        // $dailyOccupancy[date][office_id][shift_id] => int
        // $dailyAssignedInterns[date][office_id][shift_id] => array of intern_id
        $dailyOccupancy = [];
        $dailyAssignedInterns = [];

        // Prefill okupansi dari pemagang lain jika seleksi parsial
        if (!empty($options['intern_ids']) && is_array($options['intern_ids'])) {
            $existingSchedules = DetailSchedule::whereBetween('date', [$start->toDateString(), $end->toDateString()])
                ->whereIn('work_type', ['wfo', 'split_shift'])
                ->whereNotNull('office_id')
                ->whereNotNull('shift_id')
                ->whereHas('schedule', function ($q) use ($options) {
                    $q->whereNotIn('intern_id', $options['intern_ids']);
                })
                ->with('schedule:id,intern_id')
                ->get(['id', 'schedule_id', 'date', 'office_id', 'shift_id']);

            foreach ($existingSchedules as $ex) {
                $d = $ex->date;
                $o = (int) $ex->office_id;
                $s = (int) $ex->shift_id;
                $iId = $ex->schedule?->intern_id;
                $dailyOccupancy[$d][$o][$s] = ($dailyOccupancy[$d][$o][$s] ?? 0) + 1;
                if ($iId) {
                    $dailyAssignedInterns[$d][$o][$s][] = $iId;
                }
            }
        }

        $allocations = [];
        $warnings = array_values($missingKosWarnings);

        // Deteksi pemagang yang masa magangnya berakhir sebelum periode akhir otomatisasi selesai
        $internsEndingEarly = $validInterns->filter(function ($intern) use ($start, $end) {
            $iEnd = \Carbon\Carbon::parse($intern->end_date)->endOfDay();
            return $iEnd->gte($start->copy()->startOfDay()) && $iEnd->lt($end->copy()->endOfDay());
        });

        foreach ($internsEndingEarly as $earlyIntern) {
            $name = $earlyIntern->user?->profile?->full_name ?? $earlyIntern->user?->username ?? "ID #{$earlyIntern->id}";
            $endFormatted = \Carbon\Carbon::parse($earlyIntern->end_date)->locale('id')->translatedFormat('d F Y');
            $warnings[] = "Pemagang {$name} ({$earlyIntern->school?->name}) batas periode magangnya adalah {$endFormatted}. Jadwal otomatis hanya dialokasikan sampai tanggal tersebut dan tidak mengikuti periode akhir otomatisasi.";
        }

        $stats = [
            'total_interns' => $validInterns->count(),
            'unconfigured_interns' => $unconfiguredCount,
            'total_days' => 0,
            'wfo_count' => 0,
            'wfh_count' => 0,
            'hybrid_count' => 0,
            'split_shift_count' => 0,
            'spillover_count' => 0,
            'overcrowded_count' => 0,
        ];

        $period = CarbonPeriod::create($start, $end);
        $defaultShift = $shifts->first();

        // =========================================================================
        // PRE-CALCULATE BASE PROFILE PER PEMAGANG:
        // Menentukan Kantor Utama, Shift Kerja Dasar, dan Kohort Bergilir (Grup A & B)
        // secara deterministik, stabil, dan adil proporsional (50:50).
        // =========================================================================
        $internBaseProfiles = [];
        $allInternsByOffice = [];
        $flexibleInterns = [];

        // Hitung total kapasitas kursi per kantor berdasarkan shift yang aktif
        $officeTotalCapacity = [];
        foreach ($offices as $oId => $o) {
            $officeTotalCapacity[$oId] = 0;
            $vShifts = $this->getOfficeValidShifts($oId, null, $officeShiftEnabled, $shifts);
            foreach ($vShifts as $sId) {
                $officeTotalCapacity[$oId] += ($this->officeShiftCapacity[$oId][$sId] ?? (int)($o->capacity ?? 15));
            }
        }

        $internsByDiv = $validInterns->groupBy('division_id');

        foreach ($internsByDiv as $divId => $divInterns) {
            $divRule = $divInterns->first()->division?->scheduleRule;
            $allowedShiftIds = $divRule?->allowed_shift_ids ?? $shifts->keys()->toArray();
            $permittedOfficeIds = $divisionOffices[$divId] ?? $offices->keys()->toArray();

            foreach ($divInterns as $intern) {
                $isRemoteSchool = (bool) ($intern->school?->is_remote_school ?? false);
                $isWfhPlacement = ($intern->work_placement_type === 'wfh_only');

                if ($isRemoteSchool || $isWfhPlacement) {
                    $internBaseProfiles[$intern->id] = [
                        'is_remote' => true,
                        'primary_office_id' => null,
                        'closest_office_id' => null,
                        'base_shift_id' => ($intern->shift_id && isset($shifts[$intern->shift_id])) ? $intern->shift_id : ($defaultShift?->id ?? 1),
                        'cohort_group' => $intern->effective_hybrid_group ?? 'A',
                        'work_placement_type' => 'wfh_only',
                        'candidate_offices' => [],
                    ];
                    continue;
                }

                // Tentukan daftar kandidat kantor untuk pemagang ini
                if (!empty($options['target_office_id']) && isset($offices[$options['target_office_id']])) {
                    $candOffices = [(int) $options['target_office_id']];
                } elseif (!empty($intern->locked_office_id) && isset($offices[$intern->locked_office_id])) {
                    $candOffices = [(int) $intern->locked_office_id];
                } else {
                    $candOffices = $permittedOfficeIds;
                    if (!empty($intern->blacklisted_office_id)) {
                        $candOffices = array_diff($candOffices, [(int) $intern->blacklisted_office_id]);
                    }
                    if (empty($candOffices)) {
                        $candOffices = $offices->keys()->toArray();
                    }

                    // Saring kantor yang memiliki shift valid & aktif untuk divisi ini
                    $validCandOffices = [];
                    foreach ($candOffices as $cId) {
                        foreach ($allowedShiftIds as $sId) {
                            if (!empty($this->officeShiftCapacity[$cId][$sId])) {
                                $validCandOffices[] = $cId;
                                break;
                            }
                        }
                    }
                    if (!empty($validCandOffices)) {
                        $candOffices = $validCandOffices;
                    }

                    // Urutkan berdasarkan jarak Haversine terdekat
                    usort($candOffices, function ($a, $b) use ($intern, $distanceMatrix) {
                        $distA = $distanceMatrix[$intern->id][$a] ?? 999.0;
                        $distB = $distanceMatrix[$intern->id][$b] ?? 999.0;
                        return $distA <=> $distB;
                    });
                }

                $closestOfficeId = $candOffices[0] ?? $offices->keys()->first();

                // Jika dikunci target kantor atau profil kantor terkunci
                if (!empty($options['target_office_id']) || !empty($intern->locked_office_id)) {
                    $allInternsByOffice[$closestOfficeId][] = [
                        'intern' => $intern,
                        'cand_offices' => $candOffices,
                        'closest_office_id' => $closestOfficeId,
                        'allowed_shift_ids' => $allowedShiftIds,
                        'is_spillover' => false,
                    ];
                } else {
                    // Masukkan ke antrean penyeimbangan kapasitas antar kantor (Multi-Office Load Balancing)
                    $secondClosest = $candOffices[1] ?? $closestOfficeId;
                    $dist1 = $distanceMatrix[$intern->id][$closestOfficeId] ?? 999.0;
                    $dist2 = $distanceMatrix[$intern->id][$secondClosest] ?? 999.0;
                    $penalty = $dist2 - $dist1;

                    $flexibleInterns[] = [
                        'intern' => $intern,
                        'cand_offices' => $candOffices,
                        'closest_office_id' => $closestOfficeId,
                        'allowed_shift_ids' => $allowedShiftIds,
                        'dist1' => $dist1,
                        'dist2' => $dist2,
                        'penalty' => $penalty,
                    ];
                }
            }
        }

        // =========================================================================
        // PENGURUTAN PRIORITAS PENEMPATAN KANTOR (PRIORITAS RING DEKAT 1-2 KM & 4 KM)
        // Aturan:
        // 1. Ring 1 (jarak <= 2.0 km ke kantor terdekat) diprioritaskan PALING AWAL.
        // 2. Ring 2 (jarak > 2.0 km s/d <= 4.0 km) diprioritaskan berikutnya.
        // 3. Ring 3 (jarak > 4.0 km) diproses berikutnya.
        // 4. Di dalam ring yang sama: urutkan dari jarak PALING DEKAT (dist1 terkecil).
        // 5. Jika jarak dist1 identik, pemagang dengan selisih alternatif kantor lebih jauh (penalty besar) didahulukan.
        // =========================================================================
        usort($flexibleInterns, function ($a, $b) {
            $ringA = ($a['dist1'] <= 2.0) ? 1 : (($a['dist1'] <= 4.0) ? 2 : 3);
            $ringB = ($b['dist1'] <= 2.0) ? 1 : (($b['dist1'] <= 4.0) ? 2 : 3);

            if ($ringA !== $ringB) {
                return $ringA <=> $ringB;
            }

            // Di dalam kategori ring yang sama: dahulukan yang jaraknya PALING DEKAT secara absolut
            if (abs($a['dist1'] - $b['dist1']) > 0.05) {
                return $a['dist1'] <=> $b['dist1'];
            }

            // Jika jaraknya hampir identik, dahulukan yang opportunity cost (selisih ke kantor alternatif) lebih besar
            return $b['penalty'] <=> $a['penalty'];
        });

        $officeAssignedCounts = [];
        foreach ($offices->keys() as $oId) {
            $officeAssignedCounts[$oId] = count($allInternsByOffice[$oId] ?? []);
        }

        // =========================================================================
        // ALOKASI KANTOR BERTAHAP (CASCADING PROXIMITY CHECK)
        // Aturan:
        // Cek kantor kandidat berurutan dari yang paling dekat.
        // Jika kantor ring terdekat full (mencapai kapasitas fisik), pemagang akan mengambil
        // jarak kantor lain terdekat berikutnya dan melakukan pengecekan ketersediaan kursi.
        // =========================================================================
        foreach ($flexibleInterns as $item) {
            $assignedOffId = null;
            $closestId = $item['closest_office_id'];

            // Tahap 1: Cek kapasitas fisik murni kantor kandidat berurutan dari yang terdekat
            foreach ($item['cand_offices'] as $oId) {
                $cap = $officeTotalCapacity[$oId] ?? (int)($offices[$oId]?->capacity ?? 15);
                if ($officeAssignedCounts[$oId] < $cap) {
                    $assignedOffId = $oId;
                    break;
                }
            }

            // Tahap 2: Jika seluruh kantor kandidat sudah mencapai kuota fisik penuh,
            // izinkan kantor kandidat terdekat yang mendukung hybrid (50:50) untuk menampung
            // hingga kuota hybrid (2x kapasitas fisik)
            if ($assignedOffId === null) {
                foreach ($item['cand_offices'] as $oId) {
                    $oObj = $offices[$oId] ?? null;
                    $pCap = $officeTotalCapacity[$oId] ?? (int)($oObj?->capacity ?? 15);
                    $mCap = ($oObj?->is_hybrid_enabled ?? true) ? ($pCap * 2) : $pCap;
                    if ($officeAssignedCounts[$oId] < $mCap) {
                        $assignedOffId = $oId;
                        break;
                    }
                }
            }

            // Fallback: jika tetap tidak muat sama sekali, kembalikan ke kantor terdekatnya
            if ($assignedOffId === null) {
                $assignedOffId = $closestId;
            }

            $allInternsByOffice[$assignedOffId][] = [
                'intern' => $item['intern'],
                'cand_offices' => $item['cand_offices'],
                'closest_office_id' => $closestId,
                'allowed_shift_ids' => $item['allowed_shift_ids'],
                'is_spillover' => ($assignedOffId !== $closestId),
            ];
            $officeAssignedCounts[$assignedOffId]++;
        }

        // Tetapkan base_shift_id, pemerataan shift, dan pengecekan alokasi berlebih per kantor (lintas divisi)
        foreach ($allInternsByOffice as $oId => $records) {
            $officeObj = $offices[$oId] ?? null;
            $officeCap = (int) ($officeObj?->capacity ?? 15);
            $isOfficeHybridAllowed = (bool) ($officeObj?->is_hybrid_enabled ?? true);

            $validShifts = $this->getOfficeValidShifts($oId, null, $officeShiftEnabled, $shifts);
            if (empty($validShifts)) {
                $validShifts = [$defaultShift?->id ?? 1];
            }

            // Urutkan shift berdasarkan jam mulai paling awal
            usort($validShifts, function ($a, $b) use ($shifts) {
                $startA = $shifts[$a]?->start_time ?? '00:00:00';
                $startB = $shifts[$b]?->start_time ?? '00:00:00';
                return strcmp($startA, $startB);
            });

            $shiftCount = count($validShifts);
            $shiftQuotas = [];
            foreach ($validShifts as $sId) {
                $shiftQuotas[$sId] = $this->officeShiftCapacity[$oId][$sId] ?? $officeCap;
            }

            $assignedShiftPerIntern = [];
            $shiftInternCounts = array_fill_keys($validShifts, 0);

            if ($shiftCount === 1) {
                $singleShiftId = $validShifts[0];
                foreach ($records as $rec) {
                    $i = $rec['intern'];
                    $assignedShiftPerIntern[$i->id] = $singleShiftId;
                    $shiftInternCounts[$singleShiftId]++;
                }
            } else {
                $unassignedRecs = [];
                foreach ($records as $rec) {
                    $i = $rec['intern'];
                    if (!empty($i->shift_id) && in_array((int)$i->shift_id, $validShifts, true)) {
                        $sId = (int)$i->shift_id;
                        $assignedShiftPerIntern[$i->id] = $sId;
                        $shiftInternCounts[$sId]++;
                    } else {
                        $unassignedRecs[] = $rec;
                    }
                }

                foreach ($unassignedRecs as $rec) {
                    $i = $rec['intern'];
                    $iAllowed = !empty($rec['allowed_shift_ids'])
                        ? array_intersect($validShifts, $rec['allowed_shift_ids'])
                        : $validShifts;
                    if (empty($iAllowed)) {
                        $iAllowed = $validShifts;
                    }

                    $subCounts = array_intersect_key($shiftInternCounts, array_flip($iAllowed));
                    asort($subCounts);
                    $targetShiftId = array_key_first($subCounts);

                    $assignedShiftPerIntern[$i->id] = $targetShiftId;
                    $shiftInternCounts[$targetShiftId]++;
                }
            }

            $recordsByShift = [];
            foreach ($records as $rec) {
                $i = $rec['intern'];
                $sId = $assignedShiftPerIntern[$i->id];
                $recordsByShift[$sId][] = $rec;
            }

            foreach ($recordsByShift as $sId => $shiftRecs) {
                $sQuota = $shiftQuotas[$sId] ?? $officeCap;
                $internCountInShift = count($shiftRecs);

                // Skenario 1: Kapasitas masih muat ATAU kantor tidak mengizinkan skema 50:50
                if ($internCountInShift <= $sQuota || !$isOfficeHybridAllowed) {
                    // Utamakan FULL WFO untuk seluruh pemagang yang muat
                    foreach ($shiftRecs as $rec) {
                        $i = $rec['intern'];
                        $internBaseProfiles[$i->id] = [
                            'is_remote' => false,
                            'primary_office_id' => $oId,
                            'closest_office_id' => $rec['closest_office_id'] ?? $oId,
                            'base_shift_id' => $sId,
                            'cohort_group' => in_array($i->hybrid_group, ['A', 'B'], true) ? $i->hybrid_group : 'A',
                            'needs_hybrid_rotation' => false, // Full WFO tanpa rotasi!
                            'partner_id' => null,
                            'partner_name' => null,
                            'work_placement_type' => $i->work_placement_type ?? 'auto_office',
                            'candidate_offices' => $rec['cand_offices'],
                            'is_spillover' => !empty($rec['is_spillover']),
                        ];
                    }
                    continue;
                }

                // Skenario 2: Alokasi di shift kantor berlebih (> kuota kursi) DAN kantor mengizinkan 50:50
                // "Utamakan full wfo terlebih dahulu, jika ada sisa pemagang yang terpaksa wfh biasa bukan full maka
                // pada divisi apa dan sekolah apa:
                // jika ada yang sekolah sama dan divisi sama maka akan dilakukan 50 50 pada pemagang itu dengan temannya,
                // namun jika satu sekolahan tidak ada maka sesama divisi di shift itu akan dilakukan 50 50.
                // Jadi utamakan full dulu, jika ada sisa sisa tersebut dilakukan 50 50 selang seling dengan temannya"

                $excessCount = $internCountInShift - $sQuota;
                $numPairs = min($excessCount, $sQuota);

                $unpaired = $shiftRecs;
                $countUnpaired = count($unpaired);
                $usedIndices = [];
                $formedPairs = [];

                // Prioritas 1: Dua pemagang dengan Sama Sekolah DAN Sama Divisi
                for ($i = 0; $i < $countUnpaired; $i++) {
                    if (isset($usedIndices[$i]) || count($formedPairs) >= $numPairs) continue;
                    $internA = $unpaired[$i]['intern'];

                    for ($j = $i + 1; $j < $countUnpaired; $j++) {
                        if (isset($usedIndices[$j]) || count($formedPairs) >= $numPairs) continue;
                        $internB = $unpaired[$j]['intern'];

                        if (!empty($internA->school_id) && $internA->school_id == $internB->school_id && $internA->division_id == $internB->division_id) {
                            $formedPairs[] = [$unpaired[$i], $unpaired[$j]];
                            $usedIndices[$i] = true;
                            $usedIndices[$j] = true;
                            break;
                        }
                    }
                }

                // Prioritas 2: Dua pemagang dengan Sama Divisi di shift ini
                if (count($formedPairs) < $numPairs) {
                    for ($i = 0; $i < $countUnpaired; $i++) {
                        if (isset($usedIndices[$i]) || count($formedPairs) >= $numPairs) continue;
                        $internA = $unpaired[$i]['intern'];

                        for ($j = $i + 1; $j < $countUnpaired; $j++) {
                            if (isset($usedIndices[$j]) || count($formedPairs) >= $numPairs) continue;
                            $internB = $unpaired[$j]['intern'];

                            if ($internA->division_id == $internB->division_id) {
                                $formedPairs[] = [$unpaired[$i], $unpaired[$j]];
                                $usedIndices[$i] = true;
                                $usedIndices[$j] = true;
                                break;
                            }
                        }
                    }
                }

                // Prioritas 3: Dua pemagang mana pun yang tersisa di shift ini
                if (count($formedPairs) < $numPairs) {
                    for ($i = 0; $i < $countUnpaired; $i++) {
                        if (isset($usedIndices[$i]) || count($formedPairs) >= $numPairs) continue;

                        for ($j = $i + 1; $j < $countUnpaired; $j++) {
                            if (isset($usedIndices[$j]) || count($formedPairs) >= $numPairs) continue;

                            $formedPairs[] = [$unpaired[$i], $unpaired[$j]];
                            $usedIndices[$i] = true;
                            $usedIndices[$j] = true;
                            break;
                        }
                    }
                }

                $partnersMap = [];
                foreach ($formedPairs as $pair) {
                    $recA = $pair[0];
                    $recB = $pair[1];
                    $internA = $recA['intern'];
                    $internB = $recB['intern'];

                    $nameA = $internA->user?->profile?->full_name ?? $internA->user?->username ?? "ID #{$internA->id}";
                    $schoolA = $internA->school?->name ?? '-';
                    $nameB = $internB->user?->profile?->full_name ?? $internB->user?->username ?? "ID #{$internB->id}";
                    $schoolB = $internB->school?->name ?? '-';

                    // Intern A jadi Grup A, Intern B jadi Grup B (selang-seling berbagi 1 kursi)
                    $partnersMap[$internA->id] = [
                        'cohort' => 'A',
                        'partner_id' => $internB->id,
                        'partner_name' => "{$nameB} • {$schoolB}",
                    ];

                    $partnersMap[$internB->id] = [
                        'cohort' => 'B',
                        'partner_id' => $internA->id,
                        'partner_name' => "{$nameA} • {$schoolA}",
                    ];
                }

                // Buat base profile untuk seluruh pemagang di shift ini
                foreach ($shiftRecs as $rec) {
                    $i = $rec['intern'];
                    $isPaired = isset($partnersMap[$i->id]);

                    if ($isPaired) {
                        $internBaseProfiles[$i->id] = [
                            'is_remote' => false,
                            'primary_office_id' => $oId,
                            'closest_office_id' => $rec['closest_office_id'] ?? $oId,
                            'base_shift_id' => $sId,
                            'cohort_group' => $partnersMap[$i->id]['cohort'],
                            'needs_hybrid_rotation' => true,
                            'partner_id' => $partnersMap[$i->id]['partner_id'],
                            'partner_name' => $partnersMap[$i->id]['partner_name'],
                            'work_placement_type' => $i->work_placement_type ?? 'auto_office',
                            'candidate_offices' => $rec['cand_offices'],
                            'is_spillover' => !empty($rec['is_spillover']),
                        ];
                    } else {
                        // Utamakan FULL WFO (kursi aman tanpa rotasi)
                        $internBaseProfiles[$i->id] = [
                            'is_remote' => false,
                            'primary_office_id' => $oId,
                            'closest_office_id' => $rec['closest_office_id'] ?? $oId,
                            'base_shift_id' => $sId,
                            'cohort_group' => in_array($i->hybrid_group, ['A', 'B'], true) ? $i->hybrid_group : 'A',
                            'needs_hybrid_rotation' => false, // Full WFO!
                            'partner_id' => null,
                            'partner_name' => null,
                            'work_placement_type' => $i->work_placement_type ?? 'auto_office',
                            'candidate_offices' => $rec['cand_offices'],
                            'is_spillover' => !empty($rec['is_spillover']),
                        ];
                    }
                }
            }
        }

        $defaultHybridPattern = AutomationSetting::get('default_hybrid_pattern', 'daily_alternating');

        // =========================================================================
        // EKSEKUSI PIPELINE HARIAN
        // =========================================================================
        foreach ($period as $currentDate) {
            $dateStr = $currentDate->toDateString();
            $isSunday = $currentDate->isSunday();

            if ($isSunday && !$includeSunday) {
                continue;
            }

            $isHoliday = in_array($dateStr, $holidays, true);
            if ($isHoliday && $skipHolidays) {
                continue;
            }

            $stats['total_days']++;
            $dayOfWeek = $currentDate->dayOfWeekIso; // 1 = Senin, 6 = Sabtu, 7 = Minggu

            // Rotasi mingguan berbasis kalender ISO (Selalu berotasi setiap hari SENIN):
            // Hari Senin s/d Minggu dihitung sebagai 1 minggu kerja.
            // Saat berganti ke hari Senin berikutnya, rotasi shift berpindah!
            $periodStartMonday = $start->copy()->startOfWeek(Carbon::MONDAY);
            $currentDateMonday = $currentDate->copy()->startOfWeek(Carbon::MONDAY);
            $weekOffset = (int) $periodStartMonday->diffInWeeks($currentDateMonday);
            $weekDisplay = $weekOffset + 1;
            $weekParity = $weekOffset % 2;
            $dayParity = $dayOfWeek % 2;

            if ($defaultHybridPattern === 'block_3days') {
                $isFirstHalf = ($dayOfWeek >= 1 && $dayOfWeek <= 3); // Senin-Rabu vs Kamis-Sabtu
                $cohortDay = $isFirstHalf ? 1 : 0;
                $activeWfoCohort = (($cohortDay + $weekParity) % 2 === 1) ? 'A' : 'B';
            } else {
                // Bergantian harian adil (Senin A, Selasa B, Rabu A, Kamis B...)
                $activeWfoCohort = (($dayParity + $weekParity) % 2 === 1) ? 'A' : 'B';
            }

            // Saring pemagang aktif pada tanggal ini
            $activeInternsOnDate = $validInterns->filter(function ($intern) use ($currentDate) {
                $iStart = Carbon::parse($intern->start_date)->startOfDay();
                $iEnd = Carbon::parse($intern->end_date)->endOfDay();
                return $currentDate->gte($iStart) && $currentDate->lte($iEnd);
            });

            if ($activeInternsOnDate->isEmpty()) {
                continue;
            }

            // =====================================================================
            // TAHAP 2: DETERMINASI SKEMA KERJA & KOHORT HYBRID (WFO VS WFH FILTER)
            // =====================================================================
            $wfoCandidates = [];

            foreach ($activeInternsOnDate as $intern) {
                $internId = $intern->id;
                $internName = $intern->user?->profile?->full_name ?? $intern->user?->username ?? "ID #{$internId}";
                $internNim = $intern->nim ?? '-';

                $profile = $internBaseProfiles[$internId] ?? null;
                if (!$profile) {
                    continue;
                }

                $baseShiftId = $profile['base_shift_id'];
                $cohortGroup = $profile['cohort_group'];
                $primOffId = $profile['primary_office_id'] ?? ($offices->keys()->first() ?? 1);
                $divRule = $intern->division?->scheduleRule;
                $allowedShiftIdsForIntern = $divRule?->allowed_shift_ids ?? $shifts->keys()->toArray();

                $primValidShifts = $this->getOfficeValidShifts($primOffId, $allowedShiftIdsForIntern, $officeShiftEnabled, $shifts);
                if (!empty($primValidShifts)) {
                    usort($primValidShifts, function ($a, $b) use ($shifts) {
                        $startA = $shifts[$a]?->start_time ?? '00:00:00';
                        $startB = $shifts[$b]?->start_time ?? '00:00:00';
                        return strcmp($startA, $startB);
                    });
                    $pNumShifts = count($primValidShifts);
                    if ($pNumShifts <= 1) {
                        $weeklyRotatedShiftId = $primValidShifts[0];
                    } else {
                        $bIdx = array_search($baseShiftId, $primValidShifts);
                        if ($bIdx === false) $bIdx = 0;
                        $weeklyRotatedShiftId = $primValidShifts[($bIdx + $weekOffset) % $pNumShifts];
                    }
                } else {
                    $weeklyRotatedShiftId = $baseShiftId;
                }
                $weeklyRotatedShiftObj = $shifts[$weeklyRotatedShiftId] ?? ($shifts[$baseShiftId] ?? $defaultShift);

                // 2.1. ATURAN MUTLAK: Sekolah Remote / Kampus Luar Kota atau WFH Penuh
                if ($profile['is_remote'] || $profile['work_placement_type'] === 'wfh_only') {
                    $isRemoteSchool = (bool) ($intern->school?->is_remote_school ?? false);
                    $schoolName = $intern->school?->name ?? 'Kampus Luar Kota';
                    $statusNote = $isRemoteSchool
                        ? "WFH Penuh (Sekolah Remote: {$schoolName})"
                        : "WFH Penuh (Kebijakan Anggota)";

                    $allocations[] = [
                        'intern_id' => $internId,
                        'intern_name' => $internName,
                        'nim' => $internNim,
                        'school_id' => $intern->school_id,
                        'school_name' => $intern->school?->name ?? '-',
                        'division_id' => $intern->division_id,
                        'division_name' => $intern->division?->name ?? 'Umum',
                        'date' => $dateStr,
                        'day_name' => $currentDate->locale('id')->translatedFormat('l'),
                        'week_number' => $weekDisplay,
                        'office_id' => null,
                        'office_name' => 'Remote / Rumah',
                        'shift_id' => $weeklyRotatedShiftId,
                        'shift_name' => $weeklyRotatedShiftObj?->name ?? 'Shift Pagi',
                        'work_type' => 'wfh',
                        'hybrid_pattern' => 'none',
                        'hybrid_group' => null,
                        'execution_tier' => 0,
                        'is_remote' => true,
                        'is_spillover' => false,
                        'is_split_shift' => false,
                        'distance_km' => 0.0,
                        'status_note' => $statusNote,
                    ];
                    $stats['wfh_count']++;
                    continue;
                }

                // 2.2. ATURAN WFO TANPA ROTASI (Kapasitas kantor masih muat atau WFO Only)
                // "untuk kantor yang masih muat tidak perlu dibuat 50 50 selama alokasinya tidak berlebih di kantornya"
                if (!$profile['needs_hybrid_rotation']) {
                    $wfoCandidates[] = [
                        'intern' => $intern,
                        'profile' => $profile,
                    ];
                    continue;
                }

                // 2.3. ATURAN KOHORT BERGILIR (Grup A & B 50:50) untuk Shift yang Alokasinya Berlebih atau Hybrid
                $isWfoDay = ($cohortGroup === $activeWfoCohort);

                if (!$isWfoDay) {
                    $allocations[] = [
                        'intern_id' => $internId,
                        'intern_name' => $internName,
                        'nim' => $internNim,
                        'school_id' => $intern->school_id,
                        'school_name' => $intern->school?->name ?? '-',
                        'division_id' => $intern->division_id,
                        'division_name' => $intern->division?->name ?? 'Umum',
                        'date' => $dateStr,
                        'day_name' => $currentDate->locale('id')->translatedFormat('l'),
                        'week_number' => $weekDisplay,
                        'office_id' => null,
                        'office_name' => 'Remote / Rumah',
                        'shift_id' => $weeklyRotatedShiftId,
                        'shift_name' => $weeklyRotatedShiftObj?->name ?? 'Shift Pagi',
                        'work_type' => 'wfh',
                        'hybrid_pattern' => 'Grup ' . $cohortGroup,
                        'hybrid_group' => $cohortGroup,
                        'execution_tier' => 3,
                        'is_remote' => false,
                        'is_spillover' => false,
                        'is_split_shift' => false,
                        'distance_km' => 0.0,
                        'status_note' => !empty($profile['partner_name'])
                            ? "WFH Bergilir 50:50 (Bergantian dg {$profile['partner_name']})"
                            : "Hybrid Bergilir (Jadwal WFH Grup {$cohortGroup})",
                    ];
                    $stats['wfh_count']++;
                    $stats['hybrid_count']++;
                    continue;
                }

                // 2.4. Pemagang masuk ke jadwal WFO hari ini (Grup yang aktif)
                $wfoCandidates[] = [
                    'intern' => $intern,
                    'profile' => $profile,
                ];
            }

            // =====================================================================
            // TAHAP 3: FAIR BALANCED LOAD BALANCER (PEMERATAAN KURSI & SHIFT)
            // =====================================================================
            $candidatesByDivision = collect($wfoCandidates)->groupBy(function ($item) {
                return $item['intern']->division_id;
            });

            foreach ($candidatesByDivision as $divisionId => $divisionCandidates) {
                $divisionRule = $divisionCandidates->first()['intern']->division?->scheduleRule;
                $allowedShiftIds = $divisionRule?->allowed_shift_ids ?? $shifts->keys()->toArray();
                $allowSplitShift = $divisionRule?->allow_split_shift ?? true;

                $internIndexInDivision = 0;

                foreach ($divisionCandidates as $item) {
                    $intern = $item['intern'];
                    $profile = $item['profile'];
                    $internId = $intern->id;
                    $internName = $intern->user?->profile?->full_name ?? $intern->user?->username ?? "ID #{$internId}";
                    $internNim = $intern->nim ?? '-';
                    $primaryOfficeId = $profile['primary_office_id'];
                    $closestOfficeId = $profile['closest_office_id'] ?? $primaryOfficeId;
                    $preferredShiftId = $profile['base_shift_id'];

                    if (!empty($intern->locked_office_id)) {
                        $candidateOffices = [(int) $intern->locked_office_id];
                    } else {
                        $candidateOffices = array_values(array_unique(array_filter(array_merge([$primaryOfficeId], $profile['candidate_offices'] ?? []))));
                    }

                    $allocated = false;
                    $assignedOfficeId = null;
                    $assignedShiftId = null;
                    $assignedWorkType = 'wfo';
                    $isSpillover = false;
                    $executionTier = 1;
                    $statusNote = '';

                    // Coba tempatkan di kantor kandidat
                    foreach ($candidateOffices as $candOffId) {
                        $candOffice = $offices[$candOffId] ?? null;
                        if (!$candOffice) continue;

                        $candCap = (int) ($candOffice->capacity ?? 15);
                        $validShifts = $this->getOfficeValidShifts($candOffId, $allowedShiftIds, $officeShiftEnabled, $shifts);
                        if (empty($validShifts)) continue;

                        // Urutkan shift berdasarkan jam mulai paling awal (Pagi -> Middle -> Siang)
                        usort($validShifts, function ($a, $b) use ($shifts) {
                            $startA = $shifts[$a]?->start_time ?? '00:00:00';
                            $startB = $shifts[$b]?->start_time ?? '00:00:00';
                            return strcmp($startA, $startB);
                        });

                        $numOfficeShifts = count($validShifts);

                        // Aturan Rotasi Shift Mingguan (Setiap hari SENIN):
                        // 1. Jika kantor hanya menyediakan 1 shift: tidak perlu dirotasi (tetap di shift tersebut).
                        // 2. Jika kantor menyediakan 2 atau 3 shift: rotasi shift mengikuti shift yang disediakan kantor tersebut setiap hari Senin!
                        if ($numOfficeShifts <= 1) {
                            $shiftsToTry = $validShifts;
                        } else {
                            $baseIdx = array_search($profile['base_shift_id'], $validShifts);
                            if ($baseIdx === false) {
                                $baseIdx = $internIndexInDivision % $numOfficeShifts;
                            }
                            $rotatedIdx = ($baseIdx + $weekOffset) % $numOfficeShifts;

                            // Susun urutan shift yang dicoba: shift rotasi minggu ini terlebih dahulu, lalu shift lainnya secara berurutan
                            $shiftsToTry = [];
                            for ($sOff = 0; $sOff < $numOfficeShifts; $sOff++) {
                                $shiftsToTry[] = $validShifts[($rotatedIdx + $sOff) % $numOfficeShifts];
                            }
                        }

                        foreach ($shiftsToTry as $shiftId) {
                            $shiftObj = $shifts[$shiftId] ?? null;
                            if (!$shiftObj) continue;

                            $isSplit = (bool) ($shiftObj->is_split_shift ?? false);
                            if ($isSplit && !$allowSplitShift) continue;

                            if ($this->canAccommodateConcurrent($candOffId, $shiftId, $dateStr, $candCap, $dailyOccupancy, $shifts)) {
                                if (!$this->hasConflict($internId, $candOffId, $shiftId, $dateStr, $conflictPairs, $dailyAssignedInterns)) {
                                    $assignedOfficeId = $candOffId;
                                    $assignedShiftId = $shiftId;
                                    $isSpillover = ($candOffId !== $closestOfficeId);

                                     if ($isSplit) {
                                        $assignedWorkType = 'split_shift';
                                        $executionTier = 2;
                                        if (!empty($profile['needs_hybrid_rotation'])) {
                                            $partnerInfo = !empty($profile['partner_name']) ? " (Bergantian dg {$profile['partner_name']})" : " (Grup {$profile['cohort_group']})";
                                            $statusNote = "WFO Bergilir 50:50{$partnerInfo} [Split: {$shiftObj->name}]";
                                        } else {
                                            $statusNote = $isSpillover
                                                ? "Dialihkan ke {$candOffice->name} [Split: {$shiftObj->name}]"
                                                : "Split ({$shiftObj->name})";
                                        }
                                        $stats['split_shift_count']++;
                                    } else {
                                        $assignedWorkType = 'wfo';
                                        $executionTier = $isSpillover ? 4 : (!empty($profile['needs_hybrid_rotation']) ? 3 : 1);
                                        if (!empty($profile['needs_hybrid_rotation'])) {
                                            $partnerInfo = !empty($profile['partner_name']) ? " (Bergantian dg {$profile['partner_name']})" : " (Grup {$profile['cohort_group']})";
                                            $statusNote = "WFO Bergilir 50:50{$partnerInfo}";
                                        } else {
                                            $statusNote = $isSpillover
                                                ? "Dialihkan ke {$candOffice->name} (Pemerataan Meja)"
                                                : "WFO Prioritas (Kursi Utama)";
                                        }
                                        $stats['wfo_count']++;
                                    }

                                    if ($isSpillover) {
                                        $stats['spillover_count']++;
                                    }

                                    $allocated = true;
                                    break 2;
                                }
                            }
                        }
                    }

                    // Fallback jika kantor utama/cabang penuh
                    if (!$allocated) {
                        if ($profile['work_placement_type'] === 'wfo_only') {
                            $assignedOfficeId = $primaryOfficeId;
                            $assignedShiftId = $preferredShiftId;
                            $assignedWorkType = 'wfo';
                            $executionTier = 5;
                            $statusNote = 'Meja Penuh (WFO Prioritas Dipaksakan)';
                            $stats['wfo_count']++;
                            $stats['overcrowded_count']++;
                            $allocated = true;
                        } else {
                            $assignedOfficeId = null;
                            $assignedShiftId = $preferredShiftId;
                            $assignedWorkType = 'wfh';
                            $executionTier = 4;
                            $statusNote = 'Kapasitas Meja Kantor Penuh (WFH Darurat)';
                            $stats['wfh_count']++;
                            $allocated = true;
                        }
                    }

                    // Catat okupansi kursi kantor jika mendapat WFO / Split
                    if ($assignedOfficeId && in_array($assignedWorkType, ['wfo', 'split_shift'], true)) {
                        $dailyOccupancy[$dateStr][$assignedOfficeId][$assignedShiftId] = ($dailyOccupancy[$dateStr][$assignedOfficeId][$assignedShiftId] ?? 0) + 1;
                        $dailyAssignedInterns[$dateStr][$assignedOfficeId][$assignedShiftId][] = $internId;
                    }

                    $distKm = $assignedOfficeId ? ($distanceMatrix[$internId][$assignedOfficeId] ?? 0.0) : 0.0;
                    $shiftModel = $shifts[$assignedShiftId] ?? null;

                    $internEndDateStr = \Carbon\Carbon::parse($intern->end_date)->toDateString();
                    $isLastDayOfInternship = ($dateStr === $internEndDateStr);
                    if ($isLastDayOfInternship) {
                        $statusNote .= ($statusNote ? ' • ' : '') . 'Hari Terakhir Magang';
                    }

                    $allocations[] = [
                        'intern_id' => $internId,
                        'intern_name' => $internName,
                        'nim' => $internNim,
                        'school_id' => $intern->school_id,
                        'school_name' => $intern->school?->name ?? '-',
                        'division_id' => $intern->division_id,
                        'division_name' => $intern->division?->name ?? 'Umum',
                        'date' => $dateStr,
                        'day_name' => $currentDate->locale('id')->translatedFormat('l'),
                        'week_number' => $weekDisplay,
                        'office_id' => $assignedOfficeId,
                        'office_name' => $offices[$assignedOfficeId]->name ?? ($assignedWorkType === 'wfh' ? 'Remote / Rumah' : 'Kantor'),
                        'shift_id' => $assignedShiftId,
                        'shift_name' => $shiftModel?->name ?? 'Shift',
                        'work_type' => $assignedWorkType,
                        'hybrid_pattern' => $profile['needs_hybrid_rotation'] ? ('Grup ' . $profile['cohort_group']) : 'none',
                        'hybrid_group' => $profile['needs_hybrid_rotation'] ? $profile['cohort_group'] : null,
                        'execution_tier' => $executionTier,
                        'is_remote' => false,
                        'is_spillover' => $isSpillover,
                        'is_split_shift' => ($assignedWorkType === 'split_shift'),
                        'distance_km' => round($distKm, 2),
                        'status_note' => $statusNote,
                        'intern_end_date' => $internEndDateStr,
                        'is_last_day_of_internship' => $isLastDayOfInternship,
                    ];
                }
            }
        }

        // =========================================================================
        // TAHAP 4: ATOMIC DEPLOY & PERSISTENCE (JAMINAN 0% DUPLIKASI DATA)
        // =========================================================================
        if ($persistToDb) {
            DB::beginTransaction();
            try {
                $groupedByIntern = collect($allocations)->groupBy('intern_id');
                $firstDate = $start->toDateString();
                $lastDate = $end->toDateString();

                foreach ($groupedByIntern as $internId => $internAllocations) {
                    $internModel = $validInterns->firstWhere('id', $internId);
                    if (!$internModel) continue;

                    $internStartDate = \Carbon\Carbon::parse($internModel->start_date)->toDateString();
                    $internEndDate = \Carbon\Carbon::parse($internModel->end_date)->toDateString();

                    // Batas periode efektif bagi pemagang ini:
                    // Jadwal otomatis pemagang HANYA sampai batas akhir magangnya ($internEndDate)
                    // dan tidak mengikuti periode akhir otomatisasi jika magang berakhir lebih cepat!
                    $effectiveStart = ($firstDate < $internStartDate) ? $internStartDate : $firstDate;
                    $effectiveEnd = ($lastDate > $internEndDate) ? $internEndDate : $lastDate;

                    // 4.1. Ambil atau Buat Header Jadwal Tunggal
                    $firstAllocOffice = collect($internAllocations)->pluck('office_id')->filter()->first()
                        ?? $internModel->office_id
                        ?? 1;

                    // Tentukan tipe jadwal: jika kantor hanya punya 1 shift aktif, simpan 'daily' (Tetap), jika > 1 shift simpan 'weekly' (Rolling)
                    $targetOffice = $offices->get($firstAllocOffice);
                    $enabledShiftsCount = $targetOffice ? $targetOffice->shiftCapacities->where('is_enabled', true)->where('max_capacity', '>', 0)->count() : 3;
                    $determinedType = ($enabledShiftsCount > 1) ? 'weekly' : 'daily';

                    $schedule = Schedule::firstOrCreate(
                        ['intern_id' => $internId],
                        [
                            'office_id' => $firstAllocOffice,
                            'start_period' => $effectiveStart,
                            'end_period' => $effectiveEnd,
                            'type' => $determinedType
                        ]
                    );

                    $schedule->type = $determinedType;
                    if (empty($schedule->start_period) || $schedule->start_period > $effectiveStart) {
                        $schedule->start_period = $effectiveStart;
                    }
                    if ($schedule->start_period < $internStartDate) {
                        $schedule->start_period = $internStartDate;
                    }

                    // Perbarui end_period ke effectiveEnd jika jadwal sebelumnya lebih pendek,
                    // dan MUTLAK TIDAK BOLEH melebihi batas akhir magang pemagang ($internEndDate)
                    if ($schedule->end_period < $effectiveEnd) {
                        $schedule->end_period = $effectiveEnd;
                    }
                    if ($schedule->end_period > $internEndDate) {
                        $schedule->end_period = $internEndDate;
                    }

                    if (empty($schedule->office_id)) $schedule->office_id = $firstAllocOffice;
                    $schedule->save();

                    // 4.2. PEMBERSIHAN ATOMIK DATA RESIDUAL RENTANG TANGGAL:
                    // Hapus entri detail jadwal pada rentang tanggal ini untuk pemagang terkait,
                    // hanya jika belum ada jam presensi masuk (start_time kosong) agar presensi riil tidak hilang
                    $existingDetails = DetailSchedule::where('schedule_id', $schedule->id)
                        ->whereBetween('date', [$firstDate, $lastDate])
                        ->with('attendance')
                        ->get();

                    foreach ($existingDetails as $ed) {
                        if (empty($ed->attendance?->start_time)) {
                            $ed->delete();
                        }
                    }

                    // Bersihkan juga entri residual yang melampaui batas tanggal magang pemagang
                    DetailSchedule::where('schedule_id', $schedule->id)
                        ->where('date', '>', $internEndDate)
                        ->whereDoesntHave('attendance', function ($q) {
                            $q->whereNotNull('start_time');
                        })
                        ->delete();

                    // 4.3. Masukkan Jadwal Baru yang Bersih
                    foreach ($internAllocations as $alloc) {
                        $attendance = Attendance::firstOrCreate(
                            [
                                'intern_id' => $internId,
                                'date' => $alloc['date']
                            ],
                            []
                        );

                        // Karena kolom detail_schedules.office_id NOT NULL di MySQL,
                        // jika pemagang WFH/remote ($alloc['office_id'] bernilai null),
                        // simpan base office / home office pemagang atau fallback kantor utama.
                        $officeIdForDb = $alloc['office_id']
                            ?: ($internModel->office_id ?: ($schedule->office_id ?: 1));

                        $existingDetail = DetailSchedule::where('schedule_id', $schedule->id)
                            ->whereDate('date', $alloc['date'])
                            ->first();

                        if ($existingDetail) {
                            $existingDetail->update([
                                'attendance_id' => $attendance->id,
                                'office_id' => $officeIdForDb,
                                'shift_id' => $alloc['shift_id'],
                                'work_type' => ($alloc['work_type'] === 'split_shift') ? 'wfo' : $alloc['work_type'],
                                'hybrid_pattern' => $alloc['hybrid_pattern'],
                                'execution_tier' => $alloc['execution_tier'] ?? 1,
                                'is_spillover' => $alloc['is_spillover'] ?? false,
                            ]);
                        } else {
                            DetailSchedule::create([
                                'attendance_id' => $attendance->id,
                                'schedule_id' => $schedule->id,
                                'date' => $alloc['date'],
                                'office_id' => $officeIdForDb,
                                'shift_id' => $alloc['shift_id'],
                                'work_type' => ($alloc['work_type'] === 'split_shift') ? 'wfo' : $alloc['work_type'],
                                'hybrid_pattern' => $alloc['hybrid_pattern'],
                                'execution_tier' => $alloc['execution_tier'] ?? 1,
                                'is_spillover' => $alloc['is_spillover'] ?? false,
                                'attd_status_id' => 1, // Dijadwalkan
                            ]);
                        }
                    }
                }

                DB::commit();

                // 4.4. Otomatisasi Terintegrasi: Auto-Generate Jadwal Piket Kantor untuk Periode Ini
                try {
                    $piketService = app(\App\Services\PiketSchedulingService::class);
                    $piketResult = $piketService->generatePiketForPeriod($firstDate, $lastDate);
                    if (!empty($piketResult['success'])) {
                        Log::info('Auto-generate piket berhasil saat deploy jadwal otomatis.', [
                            'period' => [$firstDate, $lastDate],
                            'total_piket' => count($piketResult['piket_schedules'] ?? []),
                        ]);
                    }
                } catch (\Throwable $piketErr) {
                    Log::warning('Peringatan: Gagal auto-generate piket saat deploy jadwal: ' . $piketErr->getMessage());
                }
            } catch (\Throwable $th) {
                DB::rollBack();
                Log::error('Gagal menerapkan jadwal otomatis: ' . $th->getMessage(), [
                    'trace' => $th->getTraceAsString()
                ]);
                throw $th;
            }
        }

        // =========================================================================
        // REKAPITULASI & LAPORAN UTILISASI KURS KANTOR
        // =========================================================================
        $utilizationReport = [];
        $officeUtilization = [];

        foreach ($offices as $officeId => $office) {
            $officeCap = (int) ($office->capacity ?? 15);
            $peakConcurrentOffice = 0;

            foreach ($dailyOccupancy as $date => $officeMap) {
                $concurrentOnDate = $this->calculateDailyPeakConcurrent($officeId, $date, $dailyOccupancy, $shifts);
                if ($concurrentOnDate > $peakConcurrentOffice) {
                    $peakConcurrentOffice = $concurrentOnDate;
                }
            }

            $officeConcurrentPct = $officeCap > 0 ? round(($peakConcurrentOffice / $officeCap) * 100, 1) : 0;

            $officeUtilization[] = [
                'office_id' => $officeId,
                'office_name' => $office->name,
                'max_capacity' => $officeCap,
                'peak_concurrent' => $peakConcurrentOffice,
                'percentage' => $officeConcurrentPct,
            ];

            foreach ($shifts as $shiftId => $shift) {
                // Lewati shift jika kantor ini memiliki konfigurasi dan shift tersebut dinonaktifkan
                if (isset($officeShiftEnabled[$officeId]) && empty($officeShiftEnabled[$officeId][$shiftId])) {
                    continue;
                }

                $peakUsed = 0;
                foreach ($dailyOccupancy as $date => $officeMap) {
                    $used = $officeMap[$officeId][$shiftId] ?? 0;
                    if ($used > $peakUsed) $peakUsed = $used;
                }

                $shiftQuota = min($this->officeShiftCapacity[$officeId][$shiftId] ?? $officeCap, $officeCap);

                $utilizationReport[] = [
                    'office_id' => $officeId,
                    'office_name' => $office->name,
                    'shift_id' => $shiftId,
                    'shift_name' => $shift->name,
                    'max_capacity' => $officeCap,
                    'shift_capacity' => $shiftQuota,
                    'peak_used' => $peakUsed,
                    'peak_concurrent_office' => $peakConcurrentOffice,
                    'percentage' => $officeConcurrentPct,
                ];
            }
        }

        return [
            'success' => true,
            'message' => $persistToDb ? 'Jadwal cerdas berhasil disetujui & diterapkan ke database!' : 'Simulasi jadwal cerdas berhasil dijalankan (Draft / Preview)! Silakan tinjau dan klik Setujui & Terapkan Jadwal untuk menyimpan ke database.',
            'period' => [
                'start' => $start->toDateString(),
                'end' => $end->toDateString(),
                'total_days' => $stats['total_days'],
            ],
            'summary' => [
                'total_interns' => $stats['total_interns'],
                'total_allocations' => count($allocations),
                'total_wfo' => $stats['wfo_count'],
                'total_wfh' => $stats['wfh_count'],
                'total_split_shift' => $stats['split_shift_count'],
                'spillover_count' => $stats['spillover_count'],
                'hybrid_count' => $stats['hybrid_count'],
                'overcrowded_count' => $stats['overcrowded_count'],
            ],
            'office_utilization' => $officeUtilization,
            'utilization_report' => $utilizationReport,
            'allocations' => $allocations,
            'warnings' => $warnings,
            'unconfigured_interns' => $unconfiguredInterns,
            'unconfigured_count' => $unconfiguredCount,
            'unconfigured_note' => $unconfiguredNote,
        ];
    }

    /**
     * Rentang waktu kehadiran fisik orang di kantor.
     * Untuk split-shift, hanya rentang split_wfo_start s/d split_wfo_end yang menempati meja.
     */
    protected function getOfficePresenceWindow(Shift $shift): array
    {
        if ($shift->is_split_shift && !empty($shift->split_wfo_start) && !empty($shift->split_wfo_end)) {
            return [
                'start' => $shift->split_wfo_start,
                'end' => $shift->split_wfo_end,
            ];
        }

        return [
            'start' => $shift->start_time,
            'end' => $shift->end_time,
        ];
    }

    /**
     * Pengecekan Kuota Dua Lapis:
     * Lapis 1: Kuota kursi shift di kantor ini.
     * Lapis 2: Batas fisik meja kantor untuk orang yang hadir bersamaan (concurrent).
     */
    protected function canAccommodateConcurrent(
        int $officeId,
        int $candidateShiftId,
        string $dateStr,
        int $officeCapacity,
        array $dailyOccupancy,
        Collection $shifts
    ): bool {
        $candidateShift = $shifts[$candidateShiftId] ?? null;
        if (!$candidateShift) {
            return false;
        }

        $activeShiftCounts = $dailyOccupancy[$dateStr][$officeId] ?? [];
        $testCounts = $activeShiftCounts;
        $testCounts[$candidateShiftId] = ($testCounts[$candidateShiftId] ?? 0) + 1;

        // LAPIS 1: Kuota kursi shift di kantor ini (Wajib aktif & kuota > 0)
        if (isset($this->officeShiftCapacity[$officeId])) {
            $shiftQuota = $this->officeShiftCapacity[$officeId][$candidateShiftId] ?? 0;
            if ($shiftQuota <= 0) {
                return false;
            }
        } else {
            $shiftQuota = $officeCapacity;
        }

        if ($testCounts[$candidateShiftId] > $shiftQuota) {
            return false;
        }

        // LAPIS 2: Batas meja fisik bersamaan (concurrent)
        $candWin = $this->getOfficePresenceWindow($candidateShift);
        $candStart = $candWin['start'];
        $candEnd = $candWin['end'];

        $criticalTimes = [$candStart];
        foreach ($testCounts as $sId => $count) {
            if ($count <= 0 || !isset($shifts[$sId])) continue;
            $sWin = $this->getOfficePresenceWindow($shifts[$sId]);
            if ($sWin['start'] < $candEnd && $sWin['end'] > $candStart) {
                if ($sWin['start'] >= $candStart && $sWin['start'] < $candEnd) {
                    $criticalTimes[] = $sWin['start'];
                }
            }
        }
        $criticalTimes = array_unique($criticalTimes);

        foreach ($criticalTimes as $time) {
            $concurrentPeople = 0;
            foreach ($testCounts as $sId => $count) {
                if ($count <= 0 || !isset($shifts[$sId])) continue;
                $sWin = $this->getOfficePresenceWindow($shifts[$sId]);
                if ($sWin['start'] <= $time && $sWin['end'] > $time) {
                    $concurrentPeople += $count;
                }
            }

            if ($concurrentPeople > $officeCapacity) {
                return false;
            }
        }

        return true;
    }

    /**
     * Hitung puncak kehadiran bersamaan di kantor pada suatu tanggal.
     */
    protected function calculateDailyPeakConcurrent(int $officeId, string $dateStr, array $dailyOccupancy, Collection $shifts): int
    {
        $activeShiftCounts = $dailyOccupancy[$dateStr][$officeId] ?? [];
        if (empty($activeShiftCounts)) {
            return 0;
        }

        $allTimes = [];
        foreach ($activeShiftCounts as $shiftId => $count) {
            if ($count <= 0 || !isset($shifts[$shiftId])) continue;
            $win = $this->getOfficePresenceWindow($shifts[$shiftId]);
            $allTimes[] = $win['start'];
        }
        $allTimes = array_unique($allTimes);
        if (empty($allTimes)) {
            return 0;
        }

        $peak = 0;
        foreach ($allTimes as $time) {
            $concurrent = 0;
            foreach ($activeShiftCounts as $shiftId => $count) {
                if ($count <= 0 || !isset($shifts[$shiftId])) continue;
                $win = $this->getOfficePresenceWindow($shifts[$shiftId]);
                if ($win['start'] <= $time && $win['end'] > $time) {
                    $concurrent += $count;
                }
            }
            if ($concurrent > $peak) {
                $peak = $concurrent;
            }
        }

        return $peak;
    }

    /**
     * Pengecekan aturan konflik pasangan (tidak boleh satu kantor/shift di hari yang sama).
     */
    protected function hasConflict(
        int $internId,
        int $officeId,
        int $shiftId,
        string $dateStr,
        array $conflictPairs,
        array $dailyAssignedInterns
    ): bool {
        $conflictingInterns = $conflictPairs[$internId] ?? [];
        if (empty($conflictingInterns)) {
            return false;
        }

        $assignedOnThisShift = $dailyAssignedInterns[$dateStr][$officeId][$shiftId] ?? [];
        foreach ($conflictingInterns as $conflictId) {
            if (in_array($conflictId, $assignedOnThisShift, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Dapatkan daftar shift yang valid dan aktif di kantor ini untuk divisi terkait.
     */
    protected function getOfficeValidShifts(int $officeId, ?array $allowedShiftIds, array $officeShiftEnabled, Collection $allShifts): array
    {
        $valid = [];
        $hasOfficeConfig = isset($officeShiftEnabled[$officeId]) && !empty($officeShiftEnabled[$officeId]);
        $shiftsToCheck = ($allowedShiftIds !== null) ? $allowedShiftIds : $allShifts->keys()->all();

        foreach ($shiftsToCheck as $shiftId) {
            if (!isset($allShifts[$shiftId])) continue;

            if ($hasOfficeConfig) {
                $isEnabled = !empty($officeShiftEnabled[$officeId][$shiftId]);
                $cap = $this->officeShiftCapacity[$officeId][$shiftId] ?? 0;
                if ($isEnabled && $cap > 0) {
                    $valid[] = $shiftId;
                }
            } else {
                $valid[] = $shiftId;
            }
        }

        return $valid;
    }

    /**
     * Pemerataan Shift Adil & Afinitas Kohort:
     * - Jika kantor memiliki >= 2 shift:
     *   - Pemagang Grup A diprioritaskan ke Shift 1 (Pagi/paling awal)
     *   - Pemagang Grup B diprioritaskan ke Shift 2 (Siang/berikutnya)
     *   Sehingga Grup A dan Grup B TIDAK DIGABUNG MENJADI 1 GRUP DI 1 SHIFT!
     * - Jika shift memiliki okupansi seimbang atau hanya 1 shift: urutkan okupansi terendah.
     */
    protected function sortShiftsFair(
        array $validShifts,
        int $officeId,
        string $dateStr,
        int $internIndex,
        array $dailyOccupancy,
        string $group = 'A',
        ?Collection $allShifts = null
    ): array {
        if (count($validShifts) <= 1) {
            return $validShifts;
        }

        // 1. Urutkan shift berdasarkan waktu mulai (earliest first)
        if ($allShifts) {
            usort($validShifts, function ($a, $b) use ($allShifts) {
                $startA = $allShifts[$a]?->start_time ?? '00:00:00';
                $startB = $allShifts[$b]?->start_time ?? '00:00:00';
                return strcmp($startA, $startB);
            });
        }

        // 2. PEMISAHAN KOHORT MUTLAK (Grup A -> Shift 1 Pagi, Grup B -> Shift 2 Siang):
        // Jika kantor menyediakan >= 2 shift aktif:
        // - Pemagang Grup A diprioritaskan penuh ke Shift 1 (Pagi)
        // - Pemagang Grup B diprioritaskan penuh ke Shift 2 (Siang)
        // Hal ini menjamin Grup A dan Grup B TIDAK BERCAMPUR di dalam 1 shift yang sama!
        if (count($validShifts) >= 2) {
            $shift1 = $validShifts[0]; // misal Shift Pagi
            $shift2 = $validShifts[1]; // misal Shift Siang
            $remaining = array_slice($validShifts, 2);

            if ($group === 'A') {
                return array_merge([$shift1, $shift2], $remaining);
            } else {
                return array_merge([$shift2, $shift1], $remaining);
            }
        }

        // 3. Fallback jika hanya 1 shift atau variasi lainnya: urutkan berdasarkan okupansi saat ini (asc)
        usort($validShifts, function ($s1, $s2) use ($dailyOccupancy, $dateStr, $officeId, $internIndex) {
            $occ1 = $dailyOccupancy[$dateStr][$officeId][$s1] ?? 0;
            $occ2 = $dailyOccupancy[$dateStr][$officeId][$s2] ?? 0;

            if ($occ1 === $occ2) {
                return ($internIndex % 2 === 0) ? ($s1 <=> $s2) : ($s2 <=> $s1);
            }

            return $occ1 <=> $occ2;
        });

        return $validShifts;
    }
}
