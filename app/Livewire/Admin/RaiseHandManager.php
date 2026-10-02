<?php

namespace App\Livewire\Admin;

use App\Models\HandRaise;
use Livewire\Component;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

class RaiseHandManager extends Component
{
    use WithPagination;

    #[Url(as: 'tab')]
    public string $activeTab = 'question';

    #[Url(as: 'h_tab')]
    public string $historyTab = 'question'; // 'question', 'new_task', 'presentation'

    #[Url(as: 'div')]
    public string $selectedDivision = '';

    #[Url(as: 'h_name')]
    public string $historySearchName = '';

    #[Url(as: 'h_uid')]
    public ?int $selectedUserId = null;

    public string $search = '';

    public function mount(): void
    {
        $tab = request('tab');
        if (auth()->check() && (int) auth()->user()->role_id === 6 && $tab === 'history') {
            $tab = 'question';
        }
        if ($tab && in_array($tab, ['question', 'new_task', 'presentation', 'history'])) {
            $this->activeTab = $tab;
        }

        $hTab = request('h_tab');
        if ($hTab && in_array($hTab, ['question', 'new_task', 'presentation'])) {
            $this->historyTab = $hTab;
        }

        if (request()->filled('div')) {
            $this->selectedDivision = (string) request('div');
        }

        if (request()->filled('h_name')) {
            $this->historySearchName = (string) request('h_name');
        }

        if (request()->filled('h_uid')) {
            $this->selectedUserId = (int) request('h_uid');
        }
    }

    public function selectHistoryUser(int $userId, string $userName): void
    {
        $this->selectedUserId = $userId;
        $this->historySearchName = $userName;
        $this->resetPage();
    }

    public function clearHistoryUser(): void
    {
        $this->selectedUserId = null;
        $this->historySearchName = '';
        $this->resetPage();
    }

    public function updatedHistorySearchName(): void
    {
        // Reset ID jika admin mengubah input teks secara manual
        $this->selectedUserId = null;
        $this->resetPage();
    }

    public function updatedSelectedDivision(): void
    {
        $this->resetPage();
    }

    public function switchTab(string $tab): void
    {
        // Assistant Admin (role_id 6) tidak memiliki akses ke tab history
        if (auth()->check() && (int) auth()->user()->role_id === 6 && $tab === 'history') {
            $this->activeTab = 'question';
            return;
        }

        if (in_array($tab, ['question', 'new_task', 'presentation', 'history'])) {
            $this->activeTab = $tab;
            if ($tab === 'history') {
                $this->resetPage();
            }
        }
    }

    public function switchHistoryTab(string $subTab): void
    {
        if (in_array($subTab, ['question', 'new_task', 'presentation'])) {
            $this->historyTab = $subTab;
            $this->resetPage();
        }
    }

    public function render()
    {
        $baseRelations = [
            'user.profile',
            'user.intern.school',
            'user.intern.division',
            'user.intern.shift',
            'user.intern.todayDetailSchedule.shift',
            'user.intern.schedules.shift',
            'user.intern.detailProject.project.nameProject',
            'project.nameProject',
            'resolver.profile',
            'messages.user.profile'
        ];

        // 1. Live Counters across all categories in 1 single conditional aggregation query
        $today = today()->toDateString();
        $counts = HandRaise::selectRaw("
            COUNT(CASE WHEN is_raised = 1 AND status != 'done' AND (type = 'question' OR type IS NULL) THEN 1 END) as count_questions,
            COUNT(CASE WHEN is_raised = 1 AND status != 'done' AND type = 'new_task' THEN 1 END) as count_new_tasks,
            COUNT(CASE WHEN is_raised = 1 AND status != 'done' AND type = 'presentation' THEN 1 END) as count_presentations,
            COUNT(CASE WHEN is_raised = 1 AND status != 'done' AND type = 'presentation' AND (status = 'urgent' OR DATE(presentation_date) = ?) THEN 1 END) as count_urgent_presentations,
            COUNT(CASE WHEN status = 'done' OR (is_raised = 0 AND resolved_at IS NOT NULL) THEN 1 END) as count_done,
            COUNT(CASE WHEN (status = 'done' OR (is_raised = 0 AND resolved_at IS NOT NULL)) AND (type = 'question' OR type IS NULL) THEN 1 END) as count_history_question,
            COUNT(CASE WHEN (status = 'done' OR (is_raised = 0 AND resolved_at IS NOT NULL)) AND type = 'new_task' THEN 1 END) as count_history_new_task,
            COUNT(CASE WHEN (status = 'done' OR (is_raised = 0 AND resolved_at IS NOT NULL)) AND type = 'presentation' THEN 1 END) as count_history_presentation
        ", [$today])->first();

        $countQuestions = (int) ($counts->count_questions ?? 0);
        $countNewTasks = (int) ($counts->count_new_tasks ?? 0);
        $countPresentations = (int) ($counts->count_presentations ?? 0);
        $countUrgentPresentations = (int) ($counts->count_urgent_presentations ?? 0);
        $countDone = (int) ($counts->count_done ?? 0);
        $countHistoryQuestion = (int) ($counts->count_history_question ?? 0);
        $countHistoryNewTask = (int) ($counts->count_history_new_task ?? 0);
        $countHistoryPresentation = (int) ($counts->count_history_presentation ?? 0);

        $divisions = \App\Models\Division::orderBy('name')->get();

        // 2. Fetch items for the active tab with optional search & division filter
        $query = HandRaise::with($baseRelations);

        if ($this->activeTab === 'question') {
            $query->where('is_raised', true)
                ->where('status', '!=', 'done')
                ->where(function ($q) {
                    $q->where('type', 'question')->orWhereNull('type');
                })
                ->orderBy('created_at', 'desc');
        } elseif ($this->activeTab === 'new_task') {
            $query->where('is_raised', true)
                ->where('status', '!=', 'done')
                ->where('type', 'new_task')
                ->orderBy('created_at', 'desc');
        } elseif ($this->activeTab === 'presentation') {
            $query->where('is_raised', true)
                ->where('status', '!=', 'done')
                ->where('type', 'presentation')
                ->orderByRaw("CASE WHEN status = 'pending' THEN 0 WHEN presentation_date = CURDATE() THEN 1 ELSE 2 END")
                ->orderBy('presentation_date', 'asc')
                ->orderBy('created_at', 'desc');
        } else { // history
            $query->where(function ($q) {
                $q->where('status', 'done')
                  ->orWhere(function ($sq) {
                      $sq->where('is_raised', false)->whereNotNull('resolved_at');
                  });
            });

            // Filter per 3 sub-tab history
            if ($this->historyTab === 'question') {
                $query->where(function ($q) {
                    $q->where('type', 'question')->orWhereNull('type');
                });
            } elseif ($this->historyTab === 'new_task') {
                $query->where('type', 'new_task');
            } elseif ($this->historyTab === 'presentation') {
                $query->where('type', 'presentation');
            }

            // Filter per divisi khusus di history
            if (!empty($this->selectedDivision)) {
                $query->whereHas('user.intern', function ($iq) {
                    $iq->where('division_id', $this->selectedDivision);
                });
            }

            // Filter per nama/peserta khusus di history
            if (!empty($this->selectedUserId)) {
                $query->where('user_id', $this->selectedUserId);
            } elseif (!empty(trim($this->historySearchName))) {
                $nameTerm = '%' . trim($this->historySearchName) . '%';
                $query->whereHas('user', function ($uq) use ($nameTerm) {
                    $uq->where('username', 'like', $nameTerm)
                       ->orWhereHas('profile', fn($pq) => $pq->where('full_name', 'like', $nameTerm));
                });
            }

            // Urutan list yang diperbaiki: selalu menempatkan yang paling baru diselesaikan di paling atas
            $query->orderByRaw('COALESCE(resolved_at, updated_at, created_at) DESC')
                  ->orderBy('id', 'desc');
        }

        // Suggestions untuk filter peserta di tab history
        $suggestedUsers = collect();
        if ($this->activeTab === 'history' && !empty(trim($this->historySearchName))) {
            $searchTerm = '%' . trim($this->historySearchName) . '%';
            $suggestedUsers = \App\Models\User::whereHas('intern')
                ->where(function ($q) use ($searchTerm) {
                    $q->where('username', 'like', $searchTerm)
                      ->orWhereHas('profile', fn($pq) => $pq->where('full_name', 'like', $searchTerm))
                      ->orWhereHas('intern.school', fn($sq) => $sq->where('name', 'like', $searchTerm));
                })
                ->with(['profile', 'intern.school', 'intern.division'])
                ->limit(8)
                ->get();
        }

        // Apply search filter if query string provided
        if (!empty(trim($this->search))) {
            $searchTerm = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('notes', 'like', $searchTerm)
                  ->orWhere('reason', 'like', $searchTerm)
                  ->orWhere('admin_response', 'like', $searchTerm)
                  ->orWhere('performance_notes', 'like', $searchTerm)
                  ->orWhereHas('user', function ($uq) use ($searchTerm) {
                      $uq->where('username', 'like', $searchTerm)
                         ->orWhereHas('profile', function ($pq) use ($searchTerm) {
                             $pq->where('full_name', 'like', $searchTerm)
                                ->orWhere('phone', 'like', $searchTerm);
                         })
                         ->orWhereHas('intern.school', function ($sq) use ($searchTerm) {
                             $sq->where('name', 'like', $searchTerm);
                         })
                         ->orWhereHas('intern.division', function ($dq) use ($searchTerm) {
                             $dq->where('name', 'like', $searchTerm);
                         });
                  });
            });
        }

        if ($this->activeTab === 'history') {
            $items = $query->paginate(5);
        } else {
            $items = $query->get();
        }

        return view('livewire.admin.raise-hand-manager', [
            'countQuestions' => $countQuestions,
            'countNewTasks' => $countNewTasks,
            'countPresentations' => $countPresentations,
            'countUrgentPresentations' => $countUrgentPresentations,
            'countDone' => $countDone,
            'countHistoryQuestion' => $countHistoryQuestion,
            'countHistoryNewTask' => $countHistoryNewTask,
            'countHistoryPresentation' => $countHistoryPresentation,
            'divisions' => $divisions,
            'historyTab' => $this->historyTab,
            'selectedDivision' => $this->selectedDivision,
            'suggestedUsers' => $suggestedUsers,
            'items' => $items,
            'activeTab' => $this->activeTab,
            'pollInterval' => \App\Models\PopupSetting::getInterval('raise_hand_manager', 5),
        ]);
    }
}
