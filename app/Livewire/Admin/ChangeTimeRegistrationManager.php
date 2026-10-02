<?php

namespace App\Livewire\Admin;

use App\Models\ChangeTimeRegistration;
use App\Models\Division;
use App\Models\PopupSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class ChangeTimeRegistrationManager extends Component
{
    use WithPagination;

    public string $search = '';
    public string $divisionId = '';
    public string $regStatus = 'pending';
    public string $dateFrom = '';
    public string $dateTo = '';
    public int $pollInterval = 5;

    protected $listeners = [
        'refreshRegistrations' => '$refresh',
    ];

    public function mount(
        string $search = '',
        string $divisionId = '',
        string $regStatus = '',
        string $dateFrom = '',
        string $dateTo = ''
    ): void {
        $this->search = (string) (request('search', $search) ?: '');
        $this->divisionId = (string) (request('division_id', $divisionId) ?: '');
        $this->regStatus = (string) (request('reg_status', $regStatus) ?: 'pending');

        $today = Carbon::today('Asia/Jakarta')->toDateString();
        $this->dateFrom = (string) (request('date_from', $dateFrom) ?: $today);
        $this->dateTo = (string) (request('date_to', $dateTo) ?: $today);

        $this->pollInterval = (int) PopupSetting::getInterval('admin_change_time_registration', 5);
    }

    public function filterStatus(string $status): void
    {
        $this->regStatus = $status;
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingDivisionId(): void
    {
        $this->resetPage();
    }

    public function updatingRegStatus(): void
    {
        $this->resetPage();
    }

    public function updatingDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatingDateTo(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->divisionId = '';
        $this->regStatus = 'pending';
        $today = Carbon::today('Asia/Jakarta')->toDateString();
        $this->dateFrom = $today;
        $this->dateTo = $today;
        $this->resetPage();
    }

    public function render()
    {
        $applyRegDateFilter = function ($query) {
            if ($this->dateFrom && $this->dateTo) {
                $query->where(function ($sub) {
                    $sub->whereBetween('requested_date', [$this->dateFrom, $this->dateTo])
                        ->orWhereBetween(DB::raw('DATE(created_at)'), [$this->dateFrom, $this->dateTo]);
                });
            } elseif ($this->dateFrom) {
                $query->where(function ($sub) {
                    $sub->whereDate('requested_date', '>=', $this->dateFrom)
                        ->orWhereDate('created_at', '>=', $this->dateFrom);
                });
            } elseif ($this->dateTo) {
                $query->where(function ($sub) {
                    $sub->whereDate('requested_date', '<=', $this->dateTo)
                        ->orWhereDate('created_at', '<=', $this->dateTo);
                });
            }
        };

        // Hitung statistik pra-pendaftaran
        $regPendingCount = ChangeTimeRegistration::where('status', 'pending')->tap($applyRegDateFilter)->count();
        $regApprovedCount = ChangeTimeRegistration::where('status', 'approved')->tap($applyRegDateFilter)->count();
        $regRejectedCount = ChangeTimeRegistration::where('status', 'rejected')->tap($applyRegDateFilter)->count();
        $regCompletedCount = ChangeTimeRegistration::where('status', 'completed')->tap($applyRegDateFilter)->count();

        // Query tabel pra-pendaftaran
        $regQuery = ChangeTimeRegistration::with([
            'intern.user.profile',
            'intern.division',
            'intern.school',
            'shift',
            'office',
            'approver.profile',
            'notes.user.profile',
        ])->latest('id');

        if ($this->regStatus && $this->regStatus !== 'all') {
            $regQuery->where('status', $this->regStatus);
        }

        if (!empty(trim($this->search))) {
            $searchTerm = '%' . trim($this->search) . '%';
            $regQuery->whereHas('intern.user.profile', function ($q) use ($searchTerm) {
                $q->where('full_name', 'like', $searchTerm);
            });
        }

        if (!empty($this->divisionId)) {
            $regQuery->whereHas('intern', function ($q) {
                $q->where('division_id', $this->divisionId);
            });
        }

        $applyRegDateFilter($regQuery);

        $registrations = $regQuery->paginate(15);
        $divisions = Division::orderBy('name')->get();

        return view('livewire.admin.change-time-registration-manager', [
            'registrations' => $registrations,
            'divisions' => $divisions,
            'regPendingCount' => $regPendingCount,
            'regApprovedCount' => $regApprovedCount,
            'regRejectedCount' => $regRejectedCount,
            'regCompletedCount' => $regCompletedCount,
            'pollInterval' => $this->pollInterval,
        ]);
    }
}
