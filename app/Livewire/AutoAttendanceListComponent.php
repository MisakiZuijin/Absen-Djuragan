<?php

namespace App\Livewire;

use App\Helper\LogConsole;
use App\Models\Attendance;
use App\Services\AttendanceService;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\Livewire;

class AutoAttendanceListComponent extends Component
{
    public mixed $autoAttdData;
    public mixed $meta;
    private AttendanceService $attendanceService;

    public function boot(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    public function mount(mixed $autoAttdData, mixed $meta = null)
    {
        $this->autoAttdData = $autoAttdData;
        $this->meta = $meta;
        $this->dispatch("updatePaginate", meta: $meta);
    }

    #[On('searchAutoAttd')]
    public function updatedSearchTerm($currentPage = 1, $searchTerm = null, $dateValue = null)
    {
        $result = $this->attendanceService->shortAutomaticAttendance(page: $currentPage, name: $searchTerm, date: $dateValue);

        if ($result->isSuccess()) {
            $this->autoAttdData = $result->getData()['data'];

            $this->dispatch("updatePaginate", meta: $result->getData()['meta']);
        }
    }

    public function render()
    {
        return view('livewire.auto-attendance-list-component');
    }
}
