<?php

namespace App\Livewire;

use App\Helper\ActivityLogger;
use App\Models\ChangeTimeNote;
use App\Models\ChangeTimeRegistration;
use App\Models\PopupSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class ChangeTimeRegistrationChat extends Component
{
    public ?int $registrationId = null;
    public ?array $registrationData = null;
    public array $notes = [];
    public int $unreadCount = 0;
    public bool $isOpen = false;
    public string $message = '';
    public int $lastSeenAdminNoteId = 0;
    public bool $isInitialRenderDone = false;
    public int $pollInterval = 5;

    // Properti Popup Pemberitahuan: Sesi Diberhentikan / Pendaftaran Ditolak
    public bool $showNotificationModal = false;
    public string $notificationModalType = ''; // 'session_stopped' | 'registration_rejected'
    public string $notificationModalTitle = '';
    public string $notificationModalSubtitle = '';
    public string $notificationModalReason = '';
    public string $notificationModalNote = '';
    public ?int $rejectedSessionId = null;
    public ?int $rejectedRegId = null;

    protected $listeners = [
        'refreshRegistration' => '$refresh',
        'openChangeTimeRegChat' => 'openModal',
    ];

    public function mount(): void
    {
        $this->pollInterval = (int) PopupSetting::getInterval('change_time_registration_chat', 5);
        $this->fetchRegistration();

        if ($this->registrationId) {
            $latestAdminNote = ChangeTimeNote::where('registration_id', $this->registrationId)
                ->where('is_from_admin', true)
                ->latest('id')
                ->first();
            $this->lastSeenAdminNoteId = $latestAdminNote ? (int) $latestAdminNote->id : 0;
        }
    }

    public function fetchRegistration(): void
    {
        $user = Auth::user();
        $internId = $user?->intern?->id;
        if (!$internId) {
            $this->registrationId = null;
            $this->registrationData = null;
            $this->notes = [];
            $this->unreadCount = 0;
            return;
        }

        // 1. Cek Sesi Ganti Jam yang baru saja DIBERHENTIKAN / DITOLAK oleh Admin
        $rejectedSession = \App\Models\ChangeTimeSession::where('intern_id', $internId)
            ->where('status', 'rejected')
            ->whereDate('session_date', '>=', today()->subDays(1))
            ->latest('id')
            ->first();

        if ($rejectedSession && !session()->has('dismissed_rejected_session_' . $rejectedSession->id)) {
            $this->showNotificationModal = true;
            $this->notificationModalType = 'session_stopped';
            $this->notificationModalTitle = 'Sesi Ganti Jam Diberhentikan';
            $this->notificationModalSubtitle = 'Admin telah memberhentikan atau menolak sesi ganti jam Anda.';
            $this->notificationModalReason = $rejectedSession->rejection_note ?: 'Sesi ganti jam telah diberhentikan oleh Admin.';
            $this->notificationModalNote = 'Akumulasi jam kerja pada sesi ini tidak dihitung sebagai pelunasan hutang jam.';
            $this->rejectedSessionId = $rejectedSession->id;
        }

        // 2. Cek Pendaftaran Rencana Ganti Jam yang DITOLAK oleh Admin
        $rejectedReg = ChangeTimeRegistration::where('intern_id', $internId)
            ->where('status', 'rejected')
            ->whereDate('created_at', '>=', today()->subDays(1))
            ->latest('id')
            ->first();

        if ($rejectedReg && !session()->has('dismissed_rejected_reg_' . $rejectedReg->id)) {
            if (!$this->showNotificationModal) {
                $this->showNotificationModal = true;
                $this->notificationModalType = 'registration_rejected';
                $this->notificationModalTitle = 'Pendaftaran Ganti Jam Ditolak';
                $this->notificationModalSubtitle = 'Pengajuan rencana ganti jam Anda tidak disetujui oleh Admin.';
                $this->notificationModalReason = $rejectedReg->admin_notes ?: 'Pengajuan rencana ganti jam tidak disetujui oleh Admin.';
                $this->notificationModalNote = 'Silakan periksa kembali atau ajukan pendaftaran baru dengan jadwal/shift yang sesuai.';
            }
            $this->rejectedRegId = $rejectedReg->id;
        }

        // 3. Ambil pendaftaran aktif (pending / approved)
        $reg = ChangeTimeRegistration::where('intern_id', $internId)
            ->whereIn('status', ['pending', 'approved'])
            ->where(function ($q) {
                $q->whereNull('requested_date')
                  ->orWhereDate('requested_date', '>=', today());
            })
            ->with(['shift', 'office', 'approver.profile', 'notes.user.profile'])
            ->orderByRaw("CASE WHEN status = 'approved' THEN 1 ELSE 2 END")
            ->latest('id')
            ->first();

        if ($reg) {
            $this->registrationId = $reg->id;
            $this->registrationData = [
                'id' => $reg->id,
                'status' => $reg->status,
                'requested_date' => $reg->requested_date ? $reg->requested_date->format('Y-m-d') : null,
                'requested_date_formatted' => $reg->requested_date ? Carbon::parse($reg->requested_date)->locale('id')->isoFormat('dddd, D MMMM Y') : null,
                'shift_name' => $reg->shift?->name,
                'shift_time' => $reg->shift ? substr($reg->shift->start_time, 0, 5) . ' - ' . substr($reg->shift->end_time, 0, 5) : '',
                'office_name' => $reg->office?->name,
                'reason' => $reg->reason,
                'admin_notes' => $reg->admin_notes,
                'created_at_formatted' => $reg->created_at ? $reg->created_at->locale('id')->isoFormat('D MMM Y, HH:mm') : '',
            ];

            $this->notes = $reg->notes->map(function ($note) {
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
                    'time' => $note->created_at ? $note->created_at->locale('id')->isoFormat('HH:mm') : '-',
                    'is_read' => (bool) $note->is_read,
                ];
            })->toArray();

            $this->unreadCount = $reg->notes()->where('is_from_admin', true)->where('is_read', false)->count();
        } else {
            $this->registrationId = null;
            $this->registrationData = null;
            $this->notes = [];
            $this->unreadCount = 0;
            $this->isOpen = false;
        }
    }

    #[On('open-change-time-reg-modal')]
    public function openModal(): void
    {
        $this->isOpen = true;
        $this->markNotesAsRead();
        $this->fetchRegistration();
        $this->dispatch('intern-chat-status', source: 'reg', hasUnread: false);
        $this->dispatch('chat-reg-scroll-bottom');
    }

    public function closeModal(): void
    {
        $this->markNotesAsRead();
        $this->isOpen = false;
        $this->message = '';
        $this->dispatch('intern-chat-status', source: 'reg', hasUnread: false);
    }

    public function markNotesAsRead(): void
    {
        if ($this->registrationId) {
            ChangeTimeNote::where('registration_id', $this->registrationId)
                ->where('is_from_admin', true)
                ->where('is_read', false)
                ->update(['is_read' => true]);
            $this->unreadCount = 0;
        }
    }

    public function sendReply(): void
    {
        $trimmed = trim($this->message);
        if (empty($trimmed) || !$this->registrationId) {
            return;
        }

        $user = Auth::user();

        ChangeTimeNote::create([
            'registration_id' => $this->registrationId,
            'user_id' => $user?->id,
            'message' => $trimmed,
            'is_from_admin' => false,
            'is_read' => false,
        ]);

        $this->markNotesAsRead();
        $this->message = '';
        $this->fetchRegistration();
        $this->dispatch('chat-reg-scroll-bottom');
    }

    public function cancelRegistration(): void
    {
        if (!$this->registrationId) return;

        $user = Auth::user();
        $reg = ChangeTimeRegistration::where('id', $this->registrationId)
            ->where('intern_id', $user?->intern?->id)
            ->whereIn('status', ['pending', 'approved'])
            ->first();

        if ($reg) {
            $reg->update(['status' => 'cancelled']);
            ChangeTimeNote::where('registration_id', $reg->id)->delete();

            ActivityLogger::log(
                'CANCEL',
                'Change Time Registration',
                "Pemagang " . ($user->profile->full_name ?? $user->username) . " membatalkan pendaftaran ganti jam #{$reg->id}",
                ['registration_id' => $reg->id]
            );

            session()->flash('success', 'Pendaftaran ganti jam berhasil dibatalkan.');
        }

        $this->isOpen = false;
        $this->fetchRegistration();
        $this->dispatch('refreshRegistration');
    }

    public function dismissNotificationModal(): void
    {
        if ($this->notificationModalType === 'registration_rejected') {
            if ($this->rejectedRegId) {
                session(['dismissed_rejected_reg_' . $this->rejectedRegId => true]);
                $reg = ChangeTimeRegistration::find($this->rejectedRegId);
                if ($reg && $reg->status === 'rejected') {
                    $reg->update(['status' => 'cancelled']);
                }
            }
            $this->showNotificationModal = false;
            $this->rejectedRegId = null;
            $this->fetchRegistration();
            $this->dispatch('refreshRegistration');
        } elseif ($this->notificationModalType === 'session_stopped') {
            if ($this->rejectedSessionId) {
                session(['dismissed_rejected_session_' . $this->rejectedSessionId => true]);
            }
            $this->showNotificationModal = false;
            $this->dispatch('reload-page');
        }
    }

    public function render()
    {
        $this->fetchRegistration();

        $hasUnreadReg = false;
        if ($this->registrationId) {
            $latestAdminNote = ChangeTimeNote::where('registration_id', $this->registrationId)
                ->where('is_from_admin', true)
                ->latest('id')
                ->first();

            $latestAdminNoteId = $latestAdminNote ? (int) $latestAdminNote->id : 0;

            if (!$this->isInitialRenderDone) {
                $this->lastSeenAdminNoteId = $latestAdminNoteId;
                $this->isInitialRenderDone = true;
            } else {
                // Polling 5s: Deteksi jika admin baru mengirim pesan
                if ($latestAdminNoteId > 0 && $latestAdminNoteId > $this->lastSeenAdminNoteId) {
                    $this->lastSeenAdminNoteId = $latestAdminNoteId;

                    if ($this->isOpen) {
                        $this->markNotesAsRead();
                        $this->dispatch('chat-reg-scroll-bottom');
                    }
                }
            }

            $hasUnreadReg = ($this->unreadCount > 0 && !$this->isOpen);
        }

        // Siarkan status unread untuk loop suara 5 detik pemagang
        $this->dispatch('intern-chat-status', source: 'reg', hasUnread: $hasUnreadReg);

        return view('livewire.change-time-registration-chat', [
            'registrationData' => $this->registrationData,
            'notes' => $this->notes,
            'unreadCount' => $this->unreadCount,
            'isOpen' => $this->isOpen,
            'pollInterval' => $this->pollInterval,
            'showNotificationModal' => $this->showNotificationModal,
            'notificationModalType' => $this->notificationModalType,
            'notificationModalTitle' => $this->notificationModalTitle,
            'notificationModalSubtitle' => $this->notificationModalSubtitle,
            'notificationModalReason' => $this->notificationModalReason,
            'notificationModalNote' => $this->notificationModalNote,
        ]);
    }
}
