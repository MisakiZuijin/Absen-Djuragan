<?php

namespace App\Livewire;

use App\Models\AdjustableAttd;
use Livewire\Attributes\On;
use Livewire\Component;

class AdjstInfoContainer extends Component
{
    public mixed $adjstData = [];
    public int $currentIndex = 0;
    public ?AdjustableAttd $singleAdjstData;

    public function mount($adjstData = null)
    {
        $this->adjstData = $adjstData ?: [];

        // Restore currentIndex from session if it exists and is valid
        $sessionIndex = session('adjst_current_index', 0);

        if ($sessionIndex >= 0 && $sessionIndex < count($this->adjstData)) {
            $this->currentIndex = $sessionIndex;
        }

        if (count($this->adjstData) > 0) {
            $this->singleAdjstData = $this->adjstData[$this->currentIndex];
        } else {
            $this->singleAdjstData = null;
        }
    }

    #[On('adjst-info-refresh')]
    public function update(AdjustableAttd $adjstData)
    {
        $isExist = false;
        foreach ($this->adjstData as $idx => $value) {
            if ($value->id == $adjstData->id) {
                $this->adjstData[$idx] = $adjstData;
                $this->singleAdjstData = $this->adjstData[$idx];
                $this->currentIndex = $idx;
                session(['adjst_current_index' => $this->currentIndex]);
                $isExist = true;
                break;
            }
        }

        if (!$isExist) {
            $this->dispatch("attd-info-ajdst", isAdjustable: true);
            $this->adjstData[] = $adjstData;
            $this->singleAdjstData = $adjstData;
            $this->currentIndex = count($this->adjstData) - 1;
            session(['adjst_current_index' => $this->currentIndex]);
        }
    }

    public function moveDataPos(bool $isRight)
    {
        if ($isRight) {
            if ($this->currentIndex + 1 < count($this->adjstData)) {
                $this->currentIndex++;
                $this->singleAdjstData = $this->adjstData[$this->currentIndex];
                session(['adjst_current_index' => $this->currentIndex]);
            }
        } else {
            if ($this->currentIndex - 1 >= 0) {
                $this->currentIndex--;
                $this->singleAdjstData = $this->adjstData[$this->currentIndex];
                session(['adjst_current_index' => $this->currentIndex]);
            }
        }
    }

    public function render()
    {
        if (count($this->adjstData) == 0) {
            return "<div></div>";
        }

        return view('livewire.adjst-info-container');
    }
}
