<?php

namespace App\Livewire;

use App\Helper\LogConsole;
use App\Models\AttdStatus;
use App\Models\Attendance;
use Livewire\Attributes\On;
use Livewire\Component;

class AttdInfoContainer extends Component
{
    public ?Attendance $attdData = null;
    public bool $isAdjustable = true;
    public bool $isWithoutBreak;

    public function mount($attdData = null, bool $isWithoutBreak = false, bool $isAdjustable = false)
    {
        $this->attdData = $attdData;
        $this->isAdjustable = $isAdjustable;
        $this->isWithoutBreak = $isWithoutBreak;
    }

    #[On(event: 'attd-info-refresh')]
    public function update(Attendance $attdData, bool|null $isWithoutBreak = null)
    {
        $this->attdData = $attdData;
        if ($isWithoutBreak != null) $this->isWithoutBreak = $isWithoutBreak;
    }

    #[On('attd-info-ajdst')]
    public function updateAdjustable(bool $isAdjustable)
    {
        $this->isAdjustable = $isAdjustable;
    }

    public function render()
    {
        return view('livewire.attd-info-container');
    }
}
