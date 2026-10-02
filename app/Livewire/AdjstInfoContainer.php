<?php

namespace App\Livewire;

use App\Models\AdjustableAttd;
use Livewire\Attributes\On;
use Livewire\Component;

class AdjstInfoContainer extends Component
{
    public mixed $adjstData = [];
    public int $currentIndex = 0;
    public ?AdjustableAttd $singleAdjstData = null;
    public bool $isAdjustable = true;

    public function mount($adjstData = null, bool $isAdjustable = true)
    {
        $this->isAdjustable = $isAdjustable;

        if ($adjstData instanceof \Illuminate\Support\Collection) {
            $this->adjstData = $adjstData->values()->all();
        } elseif (is_array($adjstData)) {
            $this->adjstData = array_values($adjstData);
        } else {
            $this->adjstData = [];
        }

        // Restore currentIndex from session if it exists and is valid
        $sessionIndex = session('adjst_current_index', 0);

        // Find index of active ganti jam if exists
        $activeIdx = null;
        foreach ($this->adjstData as $idx => $item) {
            $endTime = is_object($item) ? ($item->end_time ?? null) : ($item['end_time'] ?? null);
            if (is_null($endTime)) {
                $activeIdx = $idx;
                break;
            }
        }

        if (!is_null($activeIdx)) {
            $this->currentIndex = $activeIdx;
        } elseif ($sessionIndex >= 0 && $sessionIndex < count($this->adjstData)) {
            $this->currentIndex = $sessionIndex;
        } else {
            $this->currentIndex = max(0, count($this->adjstData) - 1);
        }

        if (count($this->adjstData) > 0 && isset($this->adjstData[$this->currentIndex])) {
            $raw = $this->adjstData[$this->currentIndex];
            $this->singleAdjstData = is_array($raw) ? new AdjustableAttd($raw) : $raw;
        } else {
            $this->singleAdjstData = null;
        }
    }

    #[On('adjst-info-refresh')]
    public function update(mixed $adjstData)
    {
        if (is_numeric($adjstData)) {
            $adjstData = AdjustableAttd::find($adjstData);
        } elseif (is_array($adjstData)) {
            $id = $adjstData['id'] ?? null;
            $adjstData = $id ? AdjustableAttd::find($id) : new AdjustableAttd($adjstData);
        }

        if (!$adjstData) {
            return;
        }

        $this->isAdjustable = true;

        $isExist = false;
        foreach ($this->adjstData as $idx => $value) {
            $valId = is_object($value) ? ($value->id ?? null) : ($value['id'] ?? null);
            if ($valId == $adjstData->id) {
                $this->adjstData[$idx] = $adjstData;
                $this->singleAdjstData = is_array($this->adjstData[$idx]) ? new AdjustableAttd($this->adjstData[$idx]) : $this->adjstData[$idx];
                $this->currentIndex = $idx;
                session(['adjst_current_index' => $this->currentIndex]);
                $isExist = true;
                break;
            }
        }

        if (!$isExist) {
            $this->adjstData[] = $adjstData;
            $this->singleAdjstData = $adjstData;
            $this->currentIndex = count($this->adjstData) - 1;
            session(['adjst_current_index' => $this->currentIndex]);
        }

        $this->dispatch("attd-info-ajdst", isAdjustable: true);
    }

    #[On('attd-info-ajdst')]
    public function updateAdjustable(bool $isAdjustable)
    {
        $this->isAdjustable = $isAdjustable;
    }

    public function moveDataPos(bool $isRight)
    {
        if ($isRight) {
            if ($this->currentIndex + 1 < count($this->adjstData)) {
                $this->currentIndex++;
                $raw = $this->adjstData[$this->currentIndex];
                $this->singleAdjstData = is_array($raw) ? new AdjustableAttd($raw) : $raw;
                session(['adjst_current_index' => $this->currentIndex]);
            }
        } else {
            if ($this->currentIndex - 1 >= 0) {
                $this->currentIndex--;
                $raw = $this->adjstData[$this->currentIndex];
                $this->singleAdjstData = is_array($raw) ? new AdjustableAttd($raw) : $raw;
                session(['adjst_current_index' => $this->currentIndex]);
            }
        }
    }

    public function render()
    {
        if (count($this->adjstData) == 0 || !$this->isAdjustable) {
            return "<div></div>";
        }

        return view('livewire.adjst-info-container', [
            'adjstData' => $this->adjstData,
            'singleAdjstData' => $this->singleAdjstData,
            'currentIndex' => $this->currentIndex,
            'isAdjustable' => $this->isAdjustable,
        ]);
    }
}
