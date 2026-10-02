<?php

namespace App\Livewire;

use App\Helper\LogConsole;
use App\Models\AttdStatus;
use App\Models\Attendance;
use App\Models\ChangeTimeSession;
use App\Models\PopupSetting;
use Livewire\Attributes\On;
use Livewire\Component;

class AttdInfoContainer extends Component
{
    public ?Attendance $attdData = null;
    public bool $isAdjustable = false;
    public bool $isWithoutBreak = false;
    public int $pollInterval = 5;

    protected $listeners = [
        'attd-info-refresh' => 'update',
        'attd-info-ajdst' => 'updateAdjustable',
        'change-time-refresh' => 'checkStatus',
    ];

    public function mount($attdData = null, bool $isWithoutBreak = false, bool $isAdjustable = false)
    {
        $this->pollInterval = (int) PopupSetting::getInterval('attd_status_button', 5);
        $this->attdData = $attdData;
        $this->isAdjustable = $isAdjustable;
        $this->isWithoutBreak = $isWithoutBreak;
        $this->checkStatus();
    }

    public function checkStatus(): void
    {
        $user = auth()->user();
        if ($user && $user->intern) {
            $changeTimeService = app(\App\Services\ChangeTimeService::class);
            $activeSession = $changeTimeService->getActiveSession($user->intern->id);
            $this->isAdjustable = (bool) $activeSession;

            if (!$activeSession) {
                $todayAttd = Attendance::where('intern_id', $user->intern->id)
                    ->whereDate('date', \Carbon\Carbon::today('Asia/Jakarta')->toDateString())
                    ->first();
                if ($todayAttd) {
                    $this->attdData = $todayAttd;
                }
            }
        }
    }

    #[On(event: 'attd-info-refresh')]
    public function update(mixed $attdData, bool|null $isWithoutBreak = null)
    {
        if (is_numeric($attdData)) {
            $attdData = Attendance::find($attdData);
        } elseif (is_array($attdData)) {
            $id = $attdData['id'] ?? null;
            $attdData = $id ? Attendance::find($id) : new Attendance($attdData);
        }

        $this->attdData = $attdData;
        $this->isAdjustable = false;
        if ($isWithoutBreak != null) $this->isWithoutBreak = $isWithoutBreak;
    }

    #[On('attd-info-ajdst')]
    public function updateAdjustable(bool $isAdjustable)
    {
        $this->isAdjustable = $isAdjustable;
    }

    public function render()
    {
        $this->checkStatus();

        return view('livewire.attd-info-container', [
            'attdData' => $this->attdData,
            'isWithoutBreak' => $this->isWithoutBreak,
            'isAdjustable' => $this->isAdjustable,
            'pollInterval' => $this->pollInterval,
        ]);
    }
}
