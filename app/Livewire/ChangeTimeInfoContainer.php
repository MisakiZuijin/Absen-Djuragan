<?php

namespace App\Livewire;

use App\Models\ChangeTimeSession;
use App\Services\ChangeTimeService;
use Carbon\Carbon;
use Livewire\Component;

class ChangeTimeInfoContainer extends Component
{
    public ?int $sessionId = null;
    public ?array $sessionData = null;
    public bool $hasActiveSession = false;
    public bool $hasPendingSession = false;
    public int $workedMinutes = 0;
    public int $breakMinutes = 0;
    public int $targetDebtMinutes = 0;
    public int $remainingMinutes = 0;
    public float $progressPercentage = 0;
    public string $status = 'none';
    public bool $isOnBreak = false;
    public bool $showChatModal = false;
    public string $chatReplyInput = '';
    public array $chatNotesHistory = [];
    public int $lastSeenAdminNoteId = 0;
    public bool $isInitialRenderDone = false;
    public bool $showRejectedModal = false;
    public string $rejectionReason = '';

    protected $listeners = [
        'change-time-refresh' => 'refreshSession',
        'adjst-info-refresh' => 'refreshSession',
    ];

    public function mount($session = null)
    {
        if ($session instanceof ChangeTimeSession) {
            $this->sessionId = $session->id;
            $this->loadSessionData($session);
        } elseif (is_numeric($session)) {
            $this->sessionId = (int) $session;
            $sessionModel = ChangeTimeSession::with(['shift', 'office', 'targets.detailSchedule.shift'])->find($this->sessionId);
            if ($sessionModel) {
                $this->loadSessionData($sessionModel);
            }
        } else {
            $this->checkCurrentSession();
        }
    }

    public function checkCurrentSession()
    {
        $user = auth()->user();
        if (!$user || !$user->intern) {
            $this->hasActiveSession = false;
            $this->hasPendingSession = false;
            $this->isOnBreak = false;
            return;
        }

        $changeTimeService = app(ChangeTimeService::class);
        $activeSession = $changeTimeService->getActiveSession($user->intern->id);
        $pendingSession = $activeSession ? null : $changeTimeService->getPendingSession($user->intern->id);

        $targetSession = $activeSession ?: $pendingSession;
        if ($targetSession) {
            $this->sessionId = $targetSession->id;
            $this->loadSessionData($targetSession);
        } else {
            $this->sessionData = null;
            $this->hasActiveSession = false;
            $this->hasPendingSession = false;
            $this->status = 'none';
            $this->isOnBreak = false;
        }
    }

    public bool $isBreakHidden = false;

    public function loadSessionData(ChangeTimeSession $session)
    {
        $session->loadMissing(['shift', 'office', 'targets.detailSchedule.shift']);
        $this->sessionId = $session->id;
        $this->status = $session->status;
        $this->hasActiveSession = $session->isActive();
        $this->hasPendingSession = $session->isPendingApproval();

        $this->targetDebtMinutes = (int) $session->total_target_debt_minutes;
        $this->breakMinutes = (int) $session->total_break_minutes;

        // Pengecekan apakah istirahat terlewat / tidak ada istirahat untuk disembunyikan
        $shift = $session->shift;
        $isBreakTaken = !empty($session->break_time);
        $this->isBreakHidden = false;

        if (!$isBreakTaken) {
            // 1. Shift tanpa waktu istirahat
            if ($shift && isset($shift->break_time_in_minute) && (int) $shift->break_time_in_minute <= 0) {
                $this->isBreakHidden = true;
            }
            // 2. Sesi ganti jam sudah selesai / checkout tanpa istirahat
            elseif (!empty($session->end_time) || !$session->isActive()) {
                $this->isBreakHidden = true;
            }
            // 3. Waktu akhir istirahat shift sudah terlewat (now > end_break_time)
            elseif ($shift && !empty($shift->end_break_time)) {
                $nowTime = Carbon::now('Asia/Jakarta')->format('H:i:s');
                if ($nowTime > $shift->end_break_time) {
                    $this->isBreakHidden = true;
                }
            }
        }

        // Hitung menit kerja saat ini
        if ($session->isActive()) {
            $sessionDateStr = $session->session_date->format('Y-m-d');
            $startDateTime = Carbon::parse($sessionDateStr . ' ' . $session->start_time, 'Asia/Jakarta');

            // Jika sedang istirahat (break_time terisi dan back_time masih kosong), progress di-pause
            if (!empty($session->break_time) && empty($session->back_time)) {
                $this->isOnBreak = true;
                $breakDateTime = Carbon::parse($sessionDateStr . ' ' . $session->break_time, 'Asia/Jakarta');
                $this->workedMinutes = max(0, $breakDateTime->diffInMinutes($startDateTime));
            } else {
                $this->isOnBreak = false;
                $now = Carbon::now('Asia/Jakarta');
                $grossMinutes = max(0, $now->diffInMinutes($startDateTime));
                $this->workedMinutes = min(435, max(0, $grossMinutes - $this->breakMinutes));
            }
        } else {
            $this->isOnBreak = false;
            $this->workedMinutes = min(435, (int) $session->total_work_minutes);
        }

        $this->remainingMinutes = max(0, $this->targetDebtMinutes - $this->workedMinutes);
        $this->progressPercentage = $this->targetDebtMinutes > 0 
            ? min(100, round(($this->workedMinutes / $this->targetDebtMinutes) * 100, 1))
            : 100;

        $targetList = $session->targets->map(function ($target) {
            $schedule = $target->detailSchedule;
            $shiftName = $schedule?->shift?->name ?? 'Reguler';
            $dateFormatted = $schedule ? Carbon::parse($schedule->date)->locale('id')->isoFormat('D MMMM Y') : '-';
            $h = floor($target->debt_minutes / 60);
            $m = $target->debt_minutes % 60;
            return [
                'id' => $target->id,
                'schedule_id' => $target->detail_schedule_id,
                'date' => $dateFormatted,
                'shift' => $shiftName,
                'debt_formatted' => sprintf('%02d:%02d', $h, $m),
                'debt_hours' => round($target->debt_minutes / 60, 1),
                'is_fulfilled' => $target->is_fulfilled,
            ];
        })->toArray();

        $this->sessionData = [
            'id' => $session->id,
            'date' => $session->session_date->format('d/m/Y'),
            'status' => $session->status,
            'shift_name' => $session->shift?->name ?? 'Shift Ganti Jam',
            'shift_time' => ($session->shift ? substr($session->shift->start_time, 0, 5) . ' - ' . substr($session->shift->end_time, 0, 5) : '-'),
            'office_name' => $session->office?->name ?? 'Kantor',
            'start_time' => $session->start_time ? substr($session->start_time, 0, 5) : null,
            'break_time' => $session->break_time ? substr($session->break_time, 0, 5) : null,
            'back_time' => $session->back_time ? substr($session->back_time, 0, 5) : null,
            'end_time' => $session->end_time ? substr($session->end_time, 0, 5) : null,
            'start_time_message' => $session->start_time_message,
            'break_time_message' => $session->break_time_message,
            'back_time_message' => $session->back_time_message,
            'end_time_message' => $session->end_time_message,
            'targets' => $targetList,
        ];
    }

    public function refreshSession($sessionData = null)
    {
        if (is_array($sessionData) && isset($sessionData['id'])) {
            $sessionModel = ChangeTimeSession::with(['shift', 'office', 'targets.detailSchedule.shift'])->find($sessionData['id']);
            if ($sessionModel) {
                $this->loadSessionData($sessionModel);
                return;
            }
        }
        $this->checkCurrentSession();
    }

    /**
     * Buka modal chat diskusi ganti jam dengan admin.
     */
    #[\Livewire\Attributes\On('open-change-time-chat')]
    public function openChatModal(): void
    {
        $this->showChatModal = true;
        $this->loadChatNotes();
        $this->markNotesAsRead();
        if (!empty($this->chatNotesHistory)) {
            $last = end($this->chatNotesHistory);
            if (!empty($last['id'])) {
                $this->lastSeenAdminNoteId = max($this->lastSeenAdminNoteId, (int) $last['id']);
            }
        }
        $this->dispatch('intern-chat-status', source: 'session', hasUnread: false);
    }

    /**
     * Tutup modal chat dan tandai sudah dibaca.
     */
    public function closeChatModal(): void
    {
        $this->markNotesAsRead();
        if (!empty($this->chatNotesHistory)) {
            $last = end($this->chatNotesHistory);
            if (!empty($last['id'])) {
                $this->lastSeenAdminNoteId = max($this->lastSeenAdminNoteId, (int) $last['id']);
            }
        }
        $this->showChatModal = false;
        $this->chatReplyInput = '';
        $this->dispatch('intern-chat-status', source: 'session', hasUnread: false);
    }

    /**
     * Memuat riwayat chat dua arah ChangeTimeNote.
     */
    public function loadChatNotes(): void
    {
        $user = auth()->user();
        $internId = $user?->intern?->id;
        if (!$internId || !$this->sessionId) {
            $this->chatNotesHistory = [];
            return;
        }

        $this->chatNotesHistory = \App\Models\ChangeTimeNote::with(['user.profile'])
            ->where('session_id', $this->sessionId)
            ->orderBy('id', 'asc')
            ->get()
            ->map(function ($note) {
                $senderName = $note->is_from_admin 
                    ? ($note->user?->profile?->full_name ?? $note->user?->username ?? 'Admin') 
                    : ($note->user?->profile?->full_name ?? $note->user?->username ?? 'Pemagang');

                $cleanName = trim(preg_replace('/[^a-zA-Z0-9\s]/', '', $senderName));
                $words = array_values(array_filter(preg_split('/\s+/', $cleanName)));
                if (count($words) >= 2) {
                    $initials = strtoupper(mb_substr($words[0], 0, 1) . mb_substr($words[1], 0, 1));
                } elseif (count($words) === 1) {
                    $initials = strtoupper(mb_substr($words[0], 0, 2));
                } else {
                    $initials = $note->is_from_admin ? 'AD' : 'ME';
                }

                return [
                    'id' => $note->id,
                    'message' => $note->message,
                    'is_from_admin' => (bool) $note->is_from_admin,
                    'sender_name' => $note->is_from_admin ? $senderName : 'Anda',
                    'initials' => $initials,
                    'time' => $note->created_at ? $note->created_at->locale('id')->isoFormat('D MMM, HH:mm') : '-',
                ];
            })->toArray();
    }

    /**
     * Tandai semua pesan admin pada sesi ini sebagai sudah dibaca.
     */
    public function markNotesAsRead(): void
    {
        $user = auth()->user();
        $internId = $user?->intern?->id;
        if (!$internId || !$this->sessionId) return;

        \App\Models\ChangeTimeNote::where('session_id', $this->sessionId)
            ->where('is_from_admin', true)
            ->where('is_read', false)
            ->update(['is_read' => true]);
    }

    /**
     * Kirim balasan pesan pemagang ke admin langsung dari modal.
     */
    public function sendChatMessage(): void
    {
        $trimmed = trim($this->chatReplyInput);
        if (empty($trimmed) || !$this->sessionId) {
            return;
        }

        $user = auth()->user();

        \App\Models\ChangeTimeNote::create([
            'session_id' => $this->sessionId,
            'user_id' => $user?->id,
            'message' => $trimmed,
            'is_from_admin' => false,
            'is_read' => false,
        ]);

        $this->markNotesAsRead();
        $this->chatReplyInput = '';
        $this->loadChatNotes();
        $this->dispatch('intern-chat-status', source: 'session', hasUnread: false);
        $this->dispatch('chat-scroll-bottom');
    }

    public function dismissRejectedModal(): void
    {
        if ($this->sessionId) {
            session(['dismissed_rejected_session_' . $this->sessionId => true]);
        }
        $this->showRejectedModal = false;
        $this->dispatch('reload-page');
    }

    public function render()
    {
        // Refresh live working time on each render / cek apakah sesi ditolak admin
        if ($this->sessionId) {
            $sessionModel = ChangeTimeSession::with(['shift', 'office', 'targets.detailSchedule.shift'])->find($this->sessionId);
            if ($sessionModel && $sessionModel->status === 'rejected') {
                $this->showRejectedModal = true;
                $this->rejectionReason = $sessionModel->rejection_note ?: 'Sesi ganti jam telah diberhentikan oleh Admin.';
                $this->status = 'rejected';
                $this->hasActiveSession = false;
                $this->hasPendingSession = false;
            } elseif ($sessionModel && $sessionModel->isActive()) {
                $this->loadSessionData($sessionModel);
            } else {
                $this->checkCurrentSession();
            }
        } else {
            $this->checkCurrentSession();
        }

        $clampedPct = min(100, max(0, (float)$this->progressPercentage));
        $barBgClass = $clampedPct >= 100 ? 'bg-emerald-500' : '';
        $bgStyle = $clampedPct >= 100 
            ? 'background-color: #10b981;' 
            : 'background: linear-gradient(90deg, #ea580c 0%, #f59e0b 100%);';
        $minW = $clampedPct > 0 ? '6px' : '0px';
        $barStyleAttr = 'style="width: ' . $clampedPct . '%; min-width: ' . $minW . '; height: 100%; ' . $bgStyle . '"';

        $user = auth()->user();
        $unreadNotesCount = 0;
        $hasUnreadSession = false;
        if ($user && $user->intern && $this->sessionId && ($this->hasActiveSession || $this->hasPendingSession)) {
            $unreadAdminQuery = \App\Models\ChangeTimeNote::where('session_id', $this->sessionId)
                ->where('is_from_admin', true)
                ->where('is_read', false);

            $unreadNotesCount = $unreadAdminQuery->count();

            // Ambil pesan admin terbaru untuk deteksi pesan baru
            $latestAdminNote = \App\Models\ChangeTimeNote::where('session_id', $this->sessionId)
                ->where('is_from_admin', true)
                ->latest('id')
                ->first();

            $latestAdminNoteId = $latestAdminNote ? (int) $latestAdminNote->id : 0;

            if (!$this->isInitialRenderDone) {
                // Initial render: simpan ID pesan terakhir yang ada
                $this->lastSeenAdminNoteId = $latestAdminNoteId;
                $this->isInitialRenderDone = true;

                // Jika ada pesan yang belum dibaca saat pertama kali buka, tampilkan modal
                if ($unreadNotesCount > 0 && !$this->showChatModal) {
                    $this->showChatModal = true;
                    $this->loadChatNotes();
                    $this->dispatch('chat-scroll-bottom');
                }
            } else {
                // Polling render (setiap 5 detik):
                // Jika terdeteksi ada pesan baru dari admin yang masuk
                if ($latestAdminNoteId > 0 && $latestAdminNoteId > $this->lastSeenAdminNoteId) {
                    $this->lastSeenAdminNoteId = $latestAdminNoteId;

                    if (!$this->showChatModal) {
                        $this->showChatModal = true;
                        $this->loadChatNotes();
                        $this->dispatch('chat-scroll-bottom');
                    } else {
                        $this->loadChatNotes();
                        $this->markNotesAsRead();
                        $this->dispatch('chat-scroll-bottom');
                    }
                } elseif ($this->showChatModal) {
                    $this->loadChatNotes();
                }
            }

            // Status unread untuk loop suara jika ada pesan yang belum dibaca
            $hasUnreadSession = ($unreadNotesCount > 0);
        } else {
            $this->showChatModal = false;
        }

        // Siarkan status unread sesi ganti jam untuk loop suara 5 detik pemagang
        $this->dispatch('intern-chat-status', source: 'session', hasUnread: $hasUnreadSession);

        return view('livewire.change-time-info-container', [
            'sessionData' => $this->sessionData,
            'hasActiveSession' => $this->hasActiveSession,
            'hasPendingSession' => $this->hasPendingSession,
            'isBreakHidden' => $this->isBreakHidden,
            'workedMinutes' => $this->workedMinutes,
            'breakMinutes' => $this->breakMinutes,
            'targetDebtMinutes' => $this->targetDebtMinutes,
            'remainingMinutes' => $this->remainingMinutes,
            'progressPercentage' => $this->progressPercentage,
            'status' => $this->status,
            'clampedPct' => $clampedPct,
            'barBgClass' => $barBgClass,
            'barStyleAttr' => $barStyleAttr,
            'unreadNotesCount' => $unreadNotesCount,
            'showChatModal' => $this->showChatModal,
            'chatNotesHistory' => $this->chatNotesHistory,
            'internNoticeText' => \App\Models\ChangeTimeSetting::getSettings()->intern_notice_text,
            'pollInterval' => \App\Models\PopupSetting::getInterval('change_time_info', 5),
            'showRejectedModal' => $this->showRejectedModal,
            'rejectionReason' => $this->rejectionReason,
        ]);
    }
}
