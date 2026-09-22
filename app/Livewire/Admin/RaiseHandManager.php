<?php

namespace App\Livewire\Admin;

use App\Models\HandRaise;
use Livewire\Component;
use Livewire\Attributes\Url;

class RaiseHandManager extends Component
{
    #[Url(as: 'tab')]
    public string $activeTab = 'question';

    #[Url(as: 'h_tab')]
    public string $historyTab = 'question'; // 'question', 'new_task', 'presentation'

    #[Url(as: 'div')]
    public string $selectedDivision = '';

    public string $search = '';

    public function mount(): void
    {
        $tab = request('tab');
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
    }

    public function switchTab(string $tab): void
    {
        if (in_array($tab, ['question', 'new_task', 'presentation', 'history'])) {
            $this->activeTab = $tab;
        }
    }

    public function switchHistoryTab(string $subTab): void
    {
        if (in_array($subTab, ['question', 'new_task', 'presentation'])) {
            $this->historyTab = $subTab;
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
            'resolver.profile'
        ];

        // 1. Live Counters across all categories
        $countQuestions = HandRaise::where('is_raised', true)
            ->where('status', '!=', 'done')
            ->where(function ($q) {
                $q->where('type', 'question')->orWhereNull('type');
            })
            ->count();

        $countNewTasks = HandRaise::where('is_raised', true)
            ->where('status', '!=', 'done')
            ->where('type', 'new_task')
            ->count();

        $countPresentations = HandRaise::where('is_raised', true)
            ->where('status', '!=', 'done')
            ->where('type', 'presentation')
            ->count();

        $countUrgentPresentations = HandRaise::where('is_raised', true)
            ->where('status', '!=', 'done')
            ->where('type', 'presentation')
            ->where(function ($q) {
                $q->where('status', 'urgent')
                  ->orWhereDate('presentation_date', today());
            })
            ->count();

        // Base history count & sub-tab history counts
        $baseHistory = HandRaise::where(function ($q) {
            $q->where('status', 'done')
              ->orWhere(function ($sq) {
                  $sq->where('is_raised', false)->whereNotNull('resolved_at');
              });
        });

        $countDone = (clone $baseHistory)->count();

        $countHistoryQuestion = (clone $baseHistory)->where(function ($q) {
            $q->where('type', 'question')->orWhereNull('type');
        })->count();

        $countHistoryNewTask = (clone $baseHistory)->where('type', 'new_task')->count();

        $countHistoryPresentation = (clone $baseHistory)->where('type', 'presentation')->count();

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
                ->orderByRaw("CASE WHEN presentation_date = CURDATE() THEN 0 ELSE 1 END")
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

            // Urutan list yang diperbaiki: selalu menempatkan yang paling baru diselesaikan di paling atas
            $query->orderByRaw('COALESCE(resolved_at, updated_at, created_at) DESC')
                  ->orderBy('id', 'desc');
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

        $items = $query->get();

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
            'items' => $items,
            'activeTab' => $this->activeTab,
        ]);
    }
}
