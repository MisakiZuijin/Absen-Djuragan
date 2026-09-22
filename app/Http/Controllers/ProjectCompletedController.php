<?php

namespace App\Http\Controllers;

use App\Models\DetailProjects;
use App\Models\Division;
use App\Models\Projects;
use App\Services\UserService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ProjectCompletedController extends Controller
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Tampilkan halaman rekap portofolio seluruh project yang telah selesai (status 'done')
     */
    public function index(Request $request): View
    {
        $userData = $this->userService->getUserLoggedData();

        $search = trim($request->input('search', ''));
        $divisionFilter = $request->input('division_id', null);

        // Query dasar seluruh project yang sudah selesai
        $query = Projects::where('status', 'done')
            ->with([
                'nameProject',
                'detailProjects.intern.user.profile',
                'detailProjects.intern.division',
                'detailProjects.intern.school',
                'detailProjects.intern.account',
            ])
            ->latest('id');

        // Filter pencarian teks
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('team', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('nameProject', function ($nq) use ($search) {
                        $nq->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('detailProjects.intern.user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('detailProjects.intern.school', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Filter divisi
        if (!empty($divisionFilter) && $divisionFilter !== 'all') {
            $query->whereHas('detailProjects.intern', function ($dq) use ($divisionFilter) {
                $dq->where('division_id', $divisionFilter);
            });
        }

        $projects = $query->paginate(9)->withQueryString();

        // Ringkasan Statistik Global
        $totalCompletedProjects = Projects::where('status', 'done')->count();
        $totalInternsInvolved = DetailProjects::whereHas('project', function ($q) {
            $q->where('status', 'done');
        })->distinct('intern_id')->count('intern_id');

        // Daftar divisi dan hitung jumlah project selesai per divisi
        $allDivisions = Division::all()->map(function ($div) {
            $div->completed_count = Projects::where('status', 'done')
                ->whereHas('detailProjects.intern', function ($q) use ($div) {
                    $q->where('division_id', $div->id);
                })->count();
            return $div;
        });

        $data = [
            'user' => $userData,
            'projects' => $projects,
            'divisions' => $allDivisions,
            'selectedDivision' => $divisionFilter,
            'searchKeyword' => $search,
            'totalCompletedProjects' => $totalCompletedProjects,
            'totalInternsInvolved' => $totalInternsInvolved,
        ];

        return view('admin.project-completed', $data);
    }
}
