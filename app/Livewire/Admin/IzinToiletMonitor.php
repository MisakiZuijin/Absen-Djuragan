<?php

namespace App\Livewire\Admin;

use App\Models\Intern;
use Livewire\Component;

class IzinToiletMonitor extends Component
{
    public $allInterns;

    public function mount()
    {
        $this->loadInterns();
    }

    public function loadInterns()
    {
        $interns = Intern::with([
            'user.profile',
            'school',
            'detailProject.project.nameProject',
            'attendances' => function ($query) {
                $query->whereDate('date', today());
            }
        ])->get();

        // Urutkan intern berdasarkan status izin (yang sedang izin di atas)
        $this->allInterns = $interns->sort(function ($a, $b) {
            $aIsOnPermit = $a->attendances
                ->whereNotNull('permit_start')
                ->whereNull('permit_back')
                ->isNotEmpty();

            $bIsOnPermit = $b->attendances
                ->whereNotNull('permit_start')
                ->whereNull('permit_back')
                ->isNotEmpty();

            return $bIsOnPermit <=> $aIsOnPermit;
        });
    }

    public function render()
    {
        return view('livewire.admin.izin-toilet-monitor');
    }
}
