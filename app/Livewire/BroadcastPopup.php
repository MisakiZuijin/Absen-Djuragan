<?php

namespace App\Livewire;

use App\Models\Broadcast;
use App\Models\BroadcastReport;
use App\Models\DetailSchedule;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class BroadcastPopup extends Component
{
    public ?Broadcast $currentBroadcast = null;
    public ?int $currentBroadcastId = null;
    public bool $isOpen = false;
    public bool $isMandatory = false;
    public string $reportText = '';
    public ?int $shiftId = null;
    public ?int $officeId = null;
    public bool $scheduleChecked = false;

    // Follow-up personal chat properties
    public ?BroadcastReport $activeFollowUpReport = null;
    public array $followUpChatHistory = [];
    public string $followUpReplyText = '';
    public bool $isFollowUpOpen = false;

    protected $rules = [
        'reportText' => 'required|string|min:5|max:2000',
    ];

    protected $messages = [
        'reportText.required' => 'Jawaban / tanggapan wajib diisi sebelum menutup pesan ini.',
        'reportText.min' => 'Jawaban minimal 5 karakter.',
        'reportText.max' => 'Jawaban maksimal 2000 karakter.',
    ];

    public function mount(?int $shiftId = null, ?int $officeId = null, bool $scheduleChecked = false)
    {
        $this->shiftId = $shiftId;
        $this->officeId = $officeId;
        $this->scheduleChecked = $scheduleChecked;
        $this->checkForBroadcasts();
    }

    /**
     * Memeriksa apakah ada broadcast aktif / pertanyaan baru untuk pemagang ini.
     * Dipanggil saat mount dan via wire:poll secara periodik.
     */
    public function checkForBroadcasts()
    {
        // Jika popup saat ini sedang terbuka dan aktif di layar, jangan interupsi pemagang
        if ($this->isOpen || $this->isFollowUpOpen) {
            return;
        }

        $user = Auth::user();
        if (!$user) {
            $this->isOpen = false;
            $this->isFollowUpOpen = false;
            $this->currentBroadcast = null;
            $this->currentBroadcastId = null;
            return;
        }

        // 1. Cek apakah ada Follow-up Chat Baru dari Admin untuk laporan pemagang ini
        $unreadFollowUpChat = \App\Models\BroadcastReportChat::where('is_from_admin', true)
            ->where('is_read', false)
            ->whereHas('report', fn($q) => $q->where('user_id', $user->id))
            ->with(['report.broadcast', 'report.chats.user.profile'])
            ->latest('id')
            ->first();

        if ($unreadFollowUpChat && $unreadFollowUpChat->report) {
            $this->activeFollowUpReport = $unreadFollowUpChat->report;
            $this->loadFollowUpChats();
            $this->isFollowUpOpen = true;
            $this->isOpen = false;
            return;
        }

        if ($this->scheduleChecked) {
            $shiftId = $this->shiftId ?? $user->intern?->shift_id;
            $officeId = $this->officeId ?? $user->intern?->office_id;
        } else {
            // Cari detail schedule hari ini untuk deteksi shift dan office pemagang
            $todaysSchedule = DetailSchedule::whereHas('schedule.intern', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
                ->whereDate('date', today())
                ->first();

            $shiftId = $todaysSchedule?->shift_id ?? $user->intern?->shift_id;
            $officeId = $todaysSchedule?->office_id ?? $user->intern?->office_id;
        }
        $divisionId = $user->intern?->division_id;

        // Ambil ID semua broadcast yang sudah pernah dijawab oleh pemagang ini atau di-dismiss di session
        $answeredBroadcastIds = BroadcastReport::where('user_id', $user->id)
            ->pluck('broadcast_id')
            ->toArray();

        $dismissedIds = session('dismissed_broadcast_ids', []);
        $excludedIds = array_unique(array_merge($answeredBroadcastIds, $dismissedIds));

        // Ambil hanya 1 broadcast terbaru yang relevan dan belum dijawab langsung dari SQL database (hanya muncul pada hari H)
        $pendingBroadcast = Broadcast::with('images')
            ->when(!empty($excludedIds), fn($q) => $q->whereNotIn('id', $excludedIds))
            ->where(function ($q) {
                $q->where(function ($sq) {
                    $sq->whereNotNull('scheduled_at')
                       ->whereDate('scheduled_at', today())
                       ->where('scheduled_at', '<=', now());
                })->orWhere(function ($sq) {
                    $sq->whereNull('scheduled_at')
                       ->whereDate('created_at', today());
                });
            })
            ->where(function ($q) use ($shiftId) {
                $q->doesntHave('shifts')
                  ->orWhereHas('shifts', function ($sq) use ($shiftId) {
                      if ($shiftId) {
                          $sq->where('shifts.id', $shiftId);
                      } else {
                          $sq->whereRaw('1 = 0');
                      }
                  });
            })
            ->where(function ($q) use ($user, $officeId, $divisionId) {
                $q->whereIn('broadcast_type', ['all', 'shift'])
                  ->orWhere(function ($sq) use ($divisionId) {
                      $sq->where('broadcast_type', 'division');
                      if ($divisionId) {
                          $sq->whereHas('divisions', fn($d) => $d->where('divisions.id', $divisionId));
                      } else {
                          $sq->whereRaw('1 = 0');
                      }
                  })
                  ->orWhere(function ($sq) use ($user) {
                      $sq->where('broadcast_type', 'specific')
                         ->whereHas('users', fn($u) => $u->where('users.id', $user->id));
                  })
                  ->orWhere(function ($sq) use ($officeId) {
                      $sq->where('broadcast_type', 'office');
                      if ($officeId) {
                          $sq->whereHas('offices', fn($o) => $o->where('offices.id', $officeId));
                      } else {
                          $sq->whereRaw('1 = 0');
                      }
                  });
            })
            ->latest('id')
            ->first();

        if ($pendingBroadcast) {
            $this->currentBroadcast = $pendingBroadcast;
            $this->currentBroadcastId = $pendingBroadcast->id;
            // Untuk pemagang, semua broadcast berstatus wajib diisi/dikonfirmasi
            $this->isMandatory = true;
            $this->isOpen = true;
        } else {
            $this->isOpen = false;
            $this->currentBroadcast = null;
            $this->currentBroadcastId = null;
            $this->isMandatory = false;
        }
    }

    /**
     * Mengirimkan laporan / jawaban atas pertanyaan broadcast.
     */
    public function submitReport()
    {
        $this->validate();

        $broadcastId = $this->currentBroadcastId ?? $this->currentBroadcast?->id;
        if (!$broadcastId) {
            return;
        }

        BroadcastReport::firstOrCreate(
            ['broadcast_id' => $broadcastId, 'user_id' => Auth::id()],
            ['report' => trim($this->reportText)]
        );

        // Tandai juga di session dismissed_broadcast_ids agar popup ini tidak muncul lagi
        $dismissed = session('dismissed_broadcast_ids', []);
        $dismissed[] = $broadcastId;
        session(['dismissed_broadcast_ids' => array_unique($dismissed)]);

        $this->reportText = '';
        $this->isOpen = false;
        $this->isMandatory = false;
        $this->currentBroadcast = null;
        $this->currentBroadcastId = null;

        session()->flash('broadcast_success_msg', 'Jawaban / tanggapan Anda berhasil terkirim. Terima kasih!');

        // Periksa apakah masih ada broadcast lain yang menunggu
        $this->checkForBroadcasts();
    }

    /**
     * Menutup popup broadcast biasa (bukan mandatory).
     */
    public function dismiss()
    {
        if ($this->isMandatory) {
            return;
        }

        $broadcastId = $this->currentBroadcastId ?? $this->currentBroadcast?->id;
        if ($broadcastId) {
            $dismissed = session('dismissed_broadcast_ids', []);
            $dismissed[] = $broadcastId;

            // Tandai juga broadcast non-mandatory yang lebih lama dari broadcast ini agar tidak cascading
            $olderIds = Broadcast::scheduledBroadcasts()
                ->where('id', '<=', $broadcastId)
                ->where('requires_report', false)
                ->pluck('id')
                ->toArray();

            $dismissed = array_merge($dismissed, $olderIds);
            session(['dismissed_broadcast_ids' => array_unique($dismissed)]);
        }

        $this->isOpen = false;
        $this->currentBroadcast = null;
        $this->currentBroadcastId = null;

        // Cek jika ada broadcast mandatory yang masih menunggu
        $this->checkForBroadcasts();
    }

    public function loadFollowUpChats()
    {
        if (!$this->activeFollowUpReport) return;
        $this->activeFollowUpReport->loadMissing(['broadcast', 'chats.user.profile']);
        $this->followUpChatHistory = $this->activeFollowUpReport->chats->map(function ($c) {
            return [
                'id' => $c->id,
                'message' => $c->message,
                'is_from_admin' => (bool) $c->is_from_admin,
                'sender_name' => $c->is_from_admin 
                    ? ($c->user?->profile?->full_name ?? $c->user?->username ?? 'Admin')
                    : 'Saya',
                'time' => $c->created_at ? $c->created_at->format('d/m H:i') : '-',
            ];
        })->toArray();
    }

    public function replyFollowUpMessage()
    {
        $trimmed = trim($this->followUpReplyText);
        if (empty($trimmed) || !$this->activeFollowUpReport) {
            return;
        }

        $user = Auth::user();
        \App\Models\BroadcastReportChat::create([
            'broadcast_report_id' => $this->activeFollowUpReport->id,
            'user_id' => $user->id,
            'message' => $trimmed,
            'is_from_admin' => false,
            'is_read' => false,
        ]);

        // Tandai pesan admin sebagai sudah dibaca
        \App\Models\BroadcastReportChat::where('broadcast_report_id', $this->activeFollowUpReport->id)
            ->where('is_from_admin', true)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $this->followUpReplyText = '';
        $this->isFollowUpOpen = false;
        $this->activeFollowUpReport = null;

        session()->flash('broadcast_success_msg', 'Tanggapan lanjutan Anda telah terkirim ke Admin.');
    }

    public function dismissFollowUp()
    {
        if ($this->activeFollowUpReport) {
            \App\Models\BroadcastReportChat::where('broadcast_report_id', $this->activeFollowUpReport->id)
                ->where('is_from_admin', true)
                ->where('is_read', false)
                ->update(['is_read' => true]);
        }
        $this->isFollowUpOpen = false;
        $this->activeFollowUpReport = null;
        $this->followUpReplyText = '';
    }

    public function render()
    {
        return view('livewire.broadcast-popup', [
            'pollInterval' => \App\Models\PopupSetting::getInterval('broadcast_popup', 5),
        ]);
    }
}
