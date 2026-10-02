<?php

namespace App\Livewire;

use App\Services\AttendanceService;
use Livewire\Attributes\On;
use Livewire\Component;

class AutoAttendanceListComponent extends Component
{
    public array $autoAttdData = [];
    public ?array $meta = null;
    public string $searchTerm = '';
    public ?string $dateValue = null;
    public int $currentPage = 1;

    private AttendanceService $attendanceService;

    public function boot(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    public function mount(mixed $autoAttdData = [], mixed $meta = null)
    {
        $this->autoAttdData = is_array($autoAttdData) ? $autoAttdData : [];
        $this->meta = is_array($meta) ? $meta : ['current_page' => 1, 'total_page' => 1, 'total_data' => 0];

        if (empty($this->autoAttdData)) {
            $this->loadData();
        } else {
            $this->dispatch("updatePaginate", meta: $this->meta);
        }
    }

    #[On('searchAutoAttd')]
    public function handleSearch($payload = null, $currentPage = 1, $searchTerm = null, $dateValue = null)
    {
        // Handle if Livewire passes an array payload as first parameter or as named object
        if (is_array($payload)) {
            $this->currentPage = (int) ($payload['currentPage'] ?? $payload['page'] ?? 1);
            $this->searchTerm = (string) ($payload['searchTerm'] ?? $payload['name'] ?? '');
            $this->dateValue = !empty($payload['dateValue']) ? (string) $payload['dateValue'] : (!empty($payload['date']) ? (string) $payload['date'] : null);
        } else {
            // Handle if Livewire passes named or positional arguments
            if (is_numeric($payload)) {
                $this->currentPage = (int) $payload;
            } elseif (is_numeric($currentPage)) {
                $this->currentPage = (int) $currentPage;
            }
            if ($searchTerm !== null) {
                $this->searchTerm = (string) $searchTerm;
            }
            if ($dateValue !== null) {
                $this->dateValue = !empty($dateValue) ? (string) $dateValue : null;
            }
        }

        $this->loadData();
    }

    public function loadData()
    {
        $result = $this->attendanceService->shortAutomaticAttendance(
            page: $this->currentPage,
            name: !empty($this->searchTerm) ? $this->searchTerm : null,
            date: !empty($this->dateValue) ? $this->dateValue : null
        );

        if ($result->isSuccess()) {
            $data = $result->getData();
            $this->autoAttdData = $data['data'] ?? [];
            $this->meta = $data['meta'] ?? ['current_page' => 1, 'total_page' => 1, 'total_data' => 0];
            $this->dispatch("updatePaginate", meta: $this->meta);
        } else {
            $this->autoAttdData = [];
            $this->meta = ['current_page' => 1, 'total_page' => 1, 'total_data' => 0];
            $this->dispatch("updatePaginate", meta: $this->meta);
        }
    }

    public function render()
    {
        return view('livewire.auto-attendance-list-component');
    }
}
