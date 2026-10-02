<?php

namespace App\Http\Controllers;

use App\Models\ChangeTimeSession;
use App\Models\Division;
use App\Models\Attendance;
use App\Models\DetailSchedule;
use App\Helper\ActivityLogger;
use App\Services\ChangeTimeService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class AdminChangeTimeController extends Controller
{
    protected ChangeTimeService $changeTimeService;

    public function __construct(ChangeTimeService $changeTimeService)
    {
        $this->changeTimeService = $changeTimeService;
    }

    /**
     * Menampilkan daftar pra-pendaftaran dan sesi ganti jam pemagang beserta status persetujuannya.
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $tab = $request->input('tab', 'sessions');
        $search = $request->input('search');
        $divisionId = $request->input('division_id');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        // Jika tidak ada input filter tanggal sama sekali, default ke hari ini
        if (!$request->has('date_from') && !$request->has('date_to')) {
            $dateFrom = Carbon::today('Asia/Jakarta')->toDateString();
            $dateTo = Carbon::today('Asia/Jakarta')->toDateString();
        }

        // Helper filter tanggal untuk Pra-Pendaftaran
        $applyRegDateFilter = function ($query) use ($dateFrom, $dateTo) {
            if ($dateFrom && $dateTo) {
                $query->where(function ($sub) use ($dateFrom, $dateTo) {
                    $sub->whereBetween('requested_date', [$dateFrom, $dateTo])
                        ->orWhereBetween(DB::raw('DATE(created_at)'), [$dateFrom, $dateTo]);
                });
            } elseif ($dateFrom) {
                $query->where(function ($sub) use ($dateFrom) {
                    $sub->whereDate('requested_date', '>=', $dateFrom)
                        ->orWhereDate('created_at', '>=', $dateFrom);
                });
            } elseif ($dateTo) {
                $query->where(function ($sub) use ($dateTo) {
                    $sub->whereDate('requested_date', '<=', $dateTo)
                        ->orWhereDate('created_at', '<=', $dateTo);
                });
            }
        };

        // Helper filter tanggal untuk Sesi Ganti Jam
        $applySessionDateFilter = function ($query) use ($dateFrom, $dateTo) {
            if ($dateFrom && $dateTo) {
                $query->where(function ($sub) use ($dateFrom, $dateTo) {
                    $sub->whereBetween('session_date', [$dateFrom, $dateTo])
                        ->orWhere(function ($s) use ($dateFrom, $dateTo) {
                            $s->whereNull('session_date')
                              ->whereBetween(DB::raw('DATE(created_at)'), [$dateFrom, $dateTo]);
                        });
                });
            } elseif ($dateFrom) {
                $query->where(function ($sub) use ($dateFrom) {
                    $sub->whereDate('session_date', '>=', $dateFrom)
                        ->orWhere(function ($s) use ($dateFrom) {
                            $s->whereNull('session_date')
                              ->whereDate('created_at', '>=', $dateFrom);
                        });
                });
            } elseif ($dateTo) {
                $query->where(function ($sub) use ($dateTo) {
                    $sub->whereDate('session_date', '<=', $dateTo)
                        ->orWhere(function ($s) use ($dateTo) {
                            $s->whereNull('session_date')
                              ->whereDate('created_at', '<=', $dateTo);
                        });
                });
            }
        };

        // 1. Data Pra-Pendaftaran Ganti Jam (ChangeTimeRegistration)
        $regStatus = $request->input('reg_status', 'pending');
        $regQuery = \App\Models\ChangeTimeRegistration::with([
            'intern.user.profile',
            'intern.division',
            'shift',
            'office',
            'approver.profile',
            'notes.user.profile',
        ])->latest('id');

        if ($regStatus && $regStatus !== 'all') {
            $regQuery->where('status', $regStatus);
        }

        if ($search) {
            $regQuery->whereHas('intern.user.profile', function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%");
            });
        }

        if ($divisionId) {
            $regQuery->whereHas('intern', function ($q) use ($divisionId) {
                $q->where('division_id', $divisionId);
            });
        }

        // Terapkan filter periode tanggal ke query pendaftaran
        $applyRegDateFilter($regQuery);

        $registrations = $regQuery->paginate(15, ['*'], 'reg_page')->appends($request->query());

        // Hitung statistik pendaftaran sesuai periode tanggal yang dipilih / hari ini
        $regPendingCount = \App\Models\ChangeTimeRegistration::where('status', 'pending')->tap($applyRegDateFilter)->count();
        $regApprovedCount = \App\Models\ChangeTimeRegistration::where('status', 'approved')->tap($applyRegDateFilter)->count();
        $regRejectedCount = \App\Models\ChangeTimeRegistration::where('status', 'rejected')->tap($applyRegDateFilter)->count();
        $regCompletedCount = \App\Models\ChangeTimeRegistration::where('status', 'completed')->tap($applyRegDateFilter)->count();

        // 2. Data Sesi Ganti Jam Aktif / Pelunasan (ChangeTimeSession)
        $status = $request->input('status', 'pending_approval');
        $sessionQuery = ChangeTimeSession::with([
            'intern.user.profile',
            'intern.division',
            'shift',
            'office',
            'approver.profile',
            'targets.detailSchedule.shift',
            'notes.user.profile',
        ])->latest('session_date')->latest('id');

        if ($status && $status !== 'all') {
            $sessionQuery->where('status', $status);
        }

        if ($search) {
            $sessionQuery->whereHas('intern.user.profile', function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%");
            });
        }

        if ($divisionId) {
            $sessionQuery->whereHas('intern', function ($q) use ($divisionId) {
                $q->where('division_id', $divisionId);
            });
        }

        // Terapkan filter periode tanggal ke query sesi ganti jam
        $applySessionDateFilter($sessionQuery);

        $sessions = $sessionQuery->paginate(15, ['*'], 'session_page')->appends($request->query());

        // Prefetch registrations untuk sesi pada halaman aktif guna mencegah N+1 query
        $sessionInternIds = $sessions->pluck('intern_id')->filter()->unique()->toArray();
        $registrationsByIntern = !empty($sessionInternIds)
            ? \App\Models\ChangeTimeRegistration::whereIn('intern_id', $sessionInternIds)
                ->with(['shift', 'office'])
                ->latest('id')
                ->get()
                ->groupBy('intern_id')
            : collect();

        $sessions->getCollection()->transform(function ($session) use ($registrationsByIntern) {
            $internRegs = $registrationsByIntern->get($session->intern_id, collect());
            $sessionDateStr = $session->session_date ? \Carbon\Carbon::parse($session->session_date)->toDateString() : null;

            // 1. Cocokkan berdasarkan requested_date yang sama persis dengan session_date
            $matchedReg = $internRegs->first(function ($reg) use ($sessionDateStr) {
                $reqDate = $reg->requested_date ? \Carbon\Carbon::parse($reg->requested_date)->toDateString() : null;
                return $reqDate === $sessionDateStr;
            });

            // 2. Fallback: Cocokkan jika tanggal dibuatnya pendaftaran sama dengan session_date
            if (!$matchedReg) {
                $matchedReg = $internRegs->first(function ($reg) use ($sessionDateStr) {
                    return $reg->created_at && $reg->created_at->toDateString() === $sessionDateStr;
                });
            }

            // 3. Fallback: Pendaftaran aktif/approved/completed terdekat
            if (!$matchedReg) {
                $matchedReg = $internRegs->first(function ($reg) {
                    return in_array($reg->status, ['approved', 'completed', 'pending']);
                });
            }

            $session->matched_registration = $matchedReg;
            return $session;
        });

        $pendingCount = ChangeTimeSession::where('status', 'pending_approval')->tap($applySessionDateFilter)->count();
        $approvedCount = ChangeTimeSession::where('status', 'approved')->tap($applySessionDateFilter)->count();
        $rejectedCount = ChangeTimeSession::where('status', 'rejected')->tap($applySessionDateFilter)->count();
        $activeCount = ChangeTimeSession::where('status', 'active')->tap($applySessionDateFilter)->count();

        $divisions = Division::orderBy('name')->get();
        $shifts = \App\Models\Shift::where('id', '>', 1)->orderBy('start_time')->get();
        $offices = \App\Models\Office::orderBy('name')->get();

        return view('admin.ganti-jam.index', compact(
            'tab',
            'registrations',
            'regStatus',
            'regPendingCount',
            'regApprovedCount',
            'regRejectedCount',
            'regCompletedCount',
            'sessions',
            'status',
            'search',
            'divisionId',
            'dateFrom',
            'dateTo',
            'pendingCount',
            'approvedCount',
            'rejectedCount',
            'activeCount',
            'divisions',
            'shifts',
            'offices'
        ));
    }

    /**
     * Admin menyetujui sesi ganti jam dan melunaskan jadwal hutang target.
     *
     * @param int $id
     * @return RedirectResponse
     */
    public function approve(int $id): RedirectResponse
    {
        $adminUserId = auth()->id();
        $result = $this->changeTimeService->approveSession($id, $adminUserId);

        if ($result->isSuccess()) {
            return redirect()->back()->with('success', $result->getMessage());
        }

        return redirect()->back()->with('error', $result->getMessage());
    }

    /**
     * Admin menolak pengajuan sesi ganti jam pemagang.
     *
     * @param Request $request
     * @param int $id
     * @return RedirectResponse
     */
    public function reject(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'rejection_note' => 'required|string|max:500',
        ], [
            'rejection_note.required' => 'Harap isi alasan penolakan sesi ganti jam.',
        ]);

        $adminUserId = auth()->id();
        $result = $this->changeTimeService->rejectSession($id, $adminUserId, $request->input('rejection_note'));

        if ($result->isSuccess()) {
            return redirect()->back()->with('success', 'Sesi ganti jam telah berhasil ditolak.');
        }

        return redirect()->back()->with('error', $result->getMessage());
    }

    /**
     * Admin memperbarui waktu Masuk, Istirahat, Kembali, atau Pulang pada sesi ganti jam.
     *
     * @param Request $request
     * @param int $id
     * @return RedirectResponse
     */
    public function updateTime(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'field' => 'required|in:start_time,break_time,back_time,end_time',
            'time' => 'nullable|string',
        ]);

        $session = ChangeTimeSession::findOrFail($id);
        $field = $request->input('field');
        $time = $request->input('time');

        if (!empty($time)) {
            $time = trim($time);
            if (strlen($time) === 5) {
                $time .= ':00';
            }
        } else {
            $time = null;
        }

        $session->$field = $time;

        // Hitung ulang total waktu istirahat jika ada
        if ($session->break_time && $session->back_time) {
            $sessionDateStr = $session->session_date->format('Y-m-d');
            $breakStart = \Carbon\Carbon::parse($sessionDateStr . ' ' . $session->break_time);
            $breakEnd = \Carbon\Carbon::parse($sessionDateStr . ' ' . $session->back_time);
            if ($breakEnd->isAfter($breakStart)) {
                $session->total_break_minutes = $breakEnd->diffInMinutes($breakStart);
            }
        }

        // Hitung ulang total menit kerja jika sudah ada start dan end
        if ($session->start_time && $session->end_time) {
            $sessionDateStr = $session->session_date->format('Y-m-d');
            $workStart = \Carbon\Carbon::parse($sessionDateStr . ' ' . $session->start_time);
            $workEnd = \Carbon\Carbon::parse($sessionDateStr . ' ' . $session->end_time);
            if ($workEnd->isAfter($workStart)) {
                $gross = $workEnd->diffInMinutes($workStart);
                $session->total_work_minutes = min(435, max(0, $gross - (int) $session->total_break_minutes));
            }
        }

        // Jika jam pulang (end_time) diisi pada sesi aktif, ubah status menjadi pending_approval agar admin bisa langsung menyetujui
        if (!empty($session->end_time) && $session->status === 'active') {
            $session->status = 'pending_approval';
        } elseif (empty($session->end_time) && $session->status === 'pending_approval') {
            $session->status = 'active';
        }

        $session->save();

        return redirect()->back()->with('success', 'Waktu sesi ganti jam berhasil diperbarui.');
    }

    /**
     * Admin mengirim catatan / pesan ke pemagang terkait sesi ganti jam.
     *
     * @param Request $request
     * @param int $id
     * @return RedirectResponse|JsonResponse
     */
    public function sendSessionNote(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ], [
            'message.required' => 'Pesan tidak boleh kosong.',
            'message.max' => 'Pesan maksimal 1000 karakter.',
        ]);

        $session = ChangeTimeSession::with('intern.user.profile')->findOrFail($id);

        $note = \App\Models\ChangeTimeNote::create([
            'session_id' => $session->id,
            'user_id' => \Illuminate\Support\Facades\Auth::id(),
            'message' => $request->input('message'),
            'is_from_admin' => true,
            'is_read' => false,
        ]);

        $internName = $session->intern?->user?->profile?->full_name ?? 'Pemagang';
        $adminName = \Illuminate\Support\Facades\Auth::user()?->profile?->full_name ?? \Illuminate\Support\Facades\Auth::user()?->username ?? 'Admin';

        // Tandai pesan pemagang pada sesi ini sebagai sudah dibaca admin
        \App\Models\ChangeTimeNote::where('session_id', $session->id)
            ->where('is_from_admin', false)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        \App\Helper\ActivityLogger::log(
            'CREATE',
            'Change Time Note',
            "Admin {$adminName} mengirim pesan diskusi sesi ganti jam ke {$internName}",
            ['session_id' => $session->id]
        );

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'note' => [
                    'id' => $note->id,
                    'message' => $note->message,
                    'is_from_admin' => true,
                    'sender_name' => $adminName,
                    'time' => $note->created_at ? $note->created_at->locale('id')->isoFormat('D MMM, HH:mm') : '-',
                ],
            ]);
        }

        return redirect()->back();
    }

    /**
     * Admin menandai semua pesan pemagang pada sesi ganti jam ini sebagai sudah dibaca.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function markSessionNotesRead(int $id): JsonResponse
    {
        \App\Models\ChangeTimeNote::where('session_id', $id)
            ->where('is_from_admin', false)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }

    /**
     * Endpoint polling realtime pesan chat pemagang yang belum dibaca admin.
     *
     * @return JsonResponse
     */
    public function getUnreadChats(): JsonResponse
    {
        $unreadNotes = \App\Models\ChangeTimeNote::where('is_from_admin', false)
            ->where('is_read', false)
            ->where(function ($q) {
                $q->whereHas('session', function ($sq) {
                    $sq->whereIn('status', ['active', 'pending_approval']);
                })->orWhereHas('registration', function ($rq) {
                    $rq->whereIn('status', ['pending', 'approved']);
                });
            })
            ->with('user.profile')
            ->latest('id')
            ->get();

        $sessionIds = $unreadNotes->whereNotNull('session_id')->pluck('session_id')->unique()->values();
        $registrationIds = $unreadNotes->whereNotNull('registration_id')->pluck('registration_id')->unique()->values();
        $latestNote = $unreadNotes->first();

        $unreadSessionNotesCount = $unreadNotes->whereNotNull('session_id')->count();
        $unreadRegNotesCount = $unreadNotes->whereNotNull('registration_id')->count();

        $pendingRegCount = \App\Models\ChangeTimeRegistration::where('status', 'pending')->count();
        $pendingSessionCount = \App\Models\ChangeTimeSession::where('status', 'pending_approval')->count();
        $totalPendingCount = $pendingRegCount + $pendingSessionCount;

        return response()->json([
            'unread_count' => $unreadNotes->count(),
            'unread_session_count' => $unreadSessionNotesCount,
            'unread_reg_count' => $unreadRegNotesCount,
            'pending_reg_count' => $pendingRegCount,
            'pending_session_count' => $pendingSessionCount,
            'total_pending_count' => $totalPendingCount,
            'session_ids' => $sessionIds,
            'registration_ids' => $registrationIds,
            'latest_note' => $latestNote ? [
                'id' => $latestNote->id,
                'session_id' => $latestNote->session_id,
                'registration_id' => $latestNote->registration_id,
                'message' => $latestNote->message,
                'sender_name' => $latestNote->user?->profile?->full_name ?? $latestNote->user?->username ?? 'Pemagang',
                'time' => $latestNote->created_at ? $latestNote->created_at->locale('id')->isoFormat('D MMM, HH:mm') : '-',
            ] : null,
        ]);
    }

    /**
     * Admin menghapus data sesi ganti jam pemagang.
     *
     * @param int $id
     * @return RedirectResponse
     */
    public function destroy(int $id): RedirectResponse
    {
        $session = ChangeTimeSession::with(['intern.user.profile', 'targets.detailSchedule'])->find($id);

        if (!$session) {
            return redirect()->back()->with('error', 'Data sesi ganti jam sudah tidak ditemukan atau telah dihapus sebelumnya.');
        }

        if ($session->status === 'active') {
            return redirect()->back()->with('error', 'Sesi ganti jam yang sedang aktif berjalan tidak dapat dihapus.');
        }

        $internName = $session->intern?->user?->profile?->full_name ?? $session->intern?->user?->username ?? 'Pemagang';
        $adminName = Auth::user()?->profile?->full_name ?? Auth::user()?->username ?? 'Admin';
        $dateStr = $session->session_date ? Carbon::parse($session->session_date)->format('d/m/Y') : '-';

        DB::beginTransaction();
        try {
            // Jika sesi approved dan pernah membuat/melunasi attendance, bersihkan attendance tersebut
            if ($session->status === 'approved') {
                foreach ($session->targets as $target) {
                    $ds = $target->detailSchedule;
                    if ($target->attendance_id) {
                        $att = Attendance::find($target->attendance_id);
                        if ($att && $att->debt_fulfilled_session_id == $session->id) {
                            $isCreatedByChangeTime = ($att->start_time_message === 'Ganti Jam Selesai & Disetujui' && $att->end_time_message === 'Ganti Jam Selesai & Disetujui');

                            if ($ds) {
                                $ds->isChangeSchedule = $ds->permit_reason_id ? 2 : 0;
                                $ds->is_change_schedule_approved = 0;
                                $ds->start_time = null;
                                $ds->end_time = null;
                                $ds->attendance_id = null;
                                $ds->attd_status_id = $ds->permit_reason_id ? 3 : 5;
                                $ds->save();
                            }

                            // Selalu lepaskan kaitan foreign key di detail_schedules sebelum menghapus attendance
                            DetailSchedule::where('attendance_id', $att->id)->update([
                                'attendance_id' => null,
                                'start_time' => null,
                                'end_time' => null,
                                'isChangeSchedule' => 0,
                                'is_change_schedule_approved' => 0,
                                'attd_status_id' => 5,
                            ]);

                            if ($isCreatedByChangeTime) {
                                $att->delete();
                            } else {
                                $att->update([
                                    'start_time' => null,
                                    'end_time' => null,
                                    'break_time' => null,
                                    'back_time' => null,
                                    'total_min' => 0,
                                    'total_break_min' => 0,
                                    'is_debt_fulfilled' => false,
                                    'debt_fulfilled_session_id' => null,
                                    'debt_fulfilled_at' => null,
                                    'keterangan' => null,
                                ]);
                            }
                        }
                    } elseif ($ds) {
                        $ds->isChangeSchedule = $ds->permit_reason_id ? 2 : 0;
                        $ds->is_change_schedule_approved = 0;
                        $ds->start_time = null;
                        $ds->end_time = null;
                        $ds->save();
                    }
                }

                // Kembalikan status pendaftaran pada tanggal terkait (jika ada) ke approved
                if ($session->session_date) {
                    \App\Models\ChangeTimeRegistration::where('intern_id', $session->intern_id)
                        ->whereDate('requested_date', $session->session_date)
                        ->where('status', 'completed')
                        ->update(['status' => 'approved']);
                }
            }

            $session->targets()->delete();
            \App\Models\ChangeTimeNote::where('session_id', $session->id)->delete();
            $session->delete();

            DB::commit();

            ActivityLogger::log(
                'DELETE',
                'Change Time Session',
                "Admin {$adminName} menghapus sesi ganti jam untuk {$internName} (tanggal: {$dateStr})",
                ['session_id' => $id]
            );

            return redirect()->back()->with('success', "Data sesi ganti jam untuk {$internName} berhasil dihapus.");
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error deleting change time session: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', 'Gagal menghapus sesi ganti jam: ' . $e->getMessage());
        }
    }
}

