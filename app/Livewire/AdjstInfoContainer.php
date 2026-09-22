<?php

namespace App\Livewire;

use App\Helper\LogConsole;
use App\Models\AdjustableAttd;
use Livewire\Attributes\On;
use Livewire\Component;
use Illuminate\Support\Facades\Log;

class AdjstInfoContainer extends Component
{
    public mixed $adjstData = [];
    public int $currentIndex = 0;
    public ?AdjustableAttd $singleAdjstData;

    public function mount($adjstData = null)
    {
        // Debug: Log what we receive
        Log::info('AdjstInfoContainer mount - adjstData count: ' . count($adjstData ?: []));
        Log::info('AdjstInfoContainer mount - adjstData: ' . json_encode($adjstData));

        $this->adjstData = $adjstData ?: [];

        // Restore currentIndex from session if it exists and is valid
        $sessionIndex = session('adjst_current_index', 0);
        Log::info('AdjstInfoContainer mount - sessionIndex: ' . $sessionIndex);

        if ($sessionIndex >= 0 && $sessionIndex < sizeof($this->adjstData)) {
            $this->currentIndex = $sessionIndex;
        }

        if (sizeof($this->adjstData) > 0) {
            $this->singleAdjstData = $this->adjstData[$this->currentIndex];
            Log::info('AdjstInfoContainer mount - singleAdjstData ID: ' . $this->singleAdjstData->id);
        } else {
            $this->singleAdjstData = null;
            Log::info('AdjstInfoContainer mount - No adjstData available');
        }
    }

    #[On('adjst-info-refresh')]
    public function update(AdjustableAttd $adjstData)
    {
        Log::info('AdjstInfoContainer update - Received adjstData ID: ' . $adjstData->id);

        $isExist = false;
        foreach ($this->adjstData as $idx => $value) {
            if ($value->id == $adjstData->id) {
                $this->adjstData[$idx] = $adjstData;
                $this->singleAdjstData = $this->adjstData[$idx];
                $this->currentIndex = $idx;
                session(['adjst_current_index' => $this->currentIndex]);
                $isExist = true;
                Log::info('AdjstInfoContainer update - Updated existing at index: ' . $idx);
                break;
            }
        }

        if (!$isExist) {
            $this->dispatch("attd-info-ajdst", isAdjustable: true);
            $this->adjstData[] = $adjstData;
            $this->singleAdjstData = $adjstData;
            $this->currentIndex = sizeof($this->adjstData) - 1;
            session(['adjst_current_index' => $this->currentIndex]);
            Log::info('AdjstInfoContainer update - Added new adjstData at index: ' . $this->currentIndex);
        }
    }

    public function moveDataPos(bool $isRight)
    {
        Log::info('AdjstInfoContainer moveDataPos - Direction: ' . ($isRight ? 'right' : 'left') . ', Current Index: ' . $this->currentIndex);

        if ($isRight) {
            if ($this->currentIndex + 1 < sizeof($this->adjstData)) {
                $this->currentIndex++;
                $this->singleAdjstData = $this->adjstData[$this->currentIndex];
                session(['adjst_current_index' => $this->currentIndex]);
                Log::info('AdjstInfoContainer moveDataPos - Moved to index: ' . $this->currentIndex);
            }
        } else {
            if ($this->currentIndex - 1 >= 0) {
                $this->currentIndex--;
                $this->singleAdjstData = $this->adjstData[$this->currentIndex];
                session(['adjst_current_index' => $this->currentIndex]);
                Log::info('AdjstInfoContainer moveDataPos - Moved to index: ' . $this->currentIndex);
            }
        }
    }

    public function render()
    {
        if (sizeof($this->adjstData) == 0) {
            Log::info('AdjstInfoContainer render - No data to display');
            return "<div></div>";
        }

        Log::info('AdjstInfoContainer render - Displaying data for index: ' . $this->currentIndex);
        return view('livewire.adjst-info-container');
    }
}
