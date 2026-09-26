<?php

namespace App\Http\Controllers;

use App\Models\DetailProjects;
use App\Models\Division;
use App\Models\Projects;
use App\Services\UserService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        if ($userData && !$userData->relationLoaded('profile')) {
            $userData->loadMissing('profile');
        }

        $search = trim($request->input('search', ''));
        $divisionFilter = $request->input('division_id', null);
        $hasFilter = !empty($search) || (!empty($divisionFilter) && $divisionFilter !== 'all');

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
                        $uq->where('username', 'like', "%{$search}%")
                            ->orWhereHas('profile', function ($pq) use ($search) {
                                $pq->where('full_name', 'like', "%{$search}%");
                            });
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

        // Ringkasan Statistik Global (gunakan total paginator jika tanpa filter untuk menghindari duplikasi query)
        $totalCompletedProjects = $hasFilter
            ? Projects::where('status', 'done')->count()
            : $projects->total();

        $totalInternsInvolved = DetailProjects::join('projects', 'projects.id', '=', 'detail_projects.project_id')
            ->where('projects.status', 'done')
            ->distinct('detail_projects.intern_id')
            ->count('detail_projects.intern_id');

        // Daftar divisi dan hitung jumlah project selesai per divisi dalam 1 agregasi query tunggal (mencegah N+1)
        $divisionCounts = DB::table('projects')
            ->join('detail_projects', 'detail_projects.project_id', '=', 'projects.id')
            ->join('interns', 'interns.id', '=', 'detail_projects.intern_id')
            ->where('projects.status', 'done')
            ->groupBy('interns.division_id')
            ->select('interns.division_id', DB::raw('COUNT(DISTINCT projects.id) as total'))
            ->pluck('total', 'division_id');

        $allDivisions = Division::all()->map(function ($div) use ($divisionCounts) {
            $div->completed_count = (int) ($divisionCounts->get($div->id) ?? 0);
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
