<?php

namespace App\Livewire\Admin\Permit;

use Livewire\Component;

class ToiletMonitor extends Component
{
    public function render()
    {
        return view('livewire.admin.permit.toilet-monitor', [
            'pollInterval' => \App\Models\PopupSetting::getInterval('toilet_monitor', 15),
        ]);
    }
}
