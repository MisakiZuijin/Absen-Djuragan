<?php

namespace App\Repositories\Implementation;

use App\Models\Intern;
use App\Repositories\Interface\InternRepository;
use Illuminate\Support\Facades\DB;

class InternRepositoryIMPL implements InternRepository
{
    protected Intern $model;

    public function __construct(Intern $internModel)
    {
        $this->model = $internModel;
    }

    public function store(array $data)
    {
        $this->model->create($data);
    }

    public function update(int $id, array $data)
    {
        $intern = $this->model->find($id);
        $intern->update($data);
        return $intern;
    }

    public function getById(int $id)
    {
        return $this->model->with(['user.profile'])->find($id);
    }

    public function getBySchoolId(int $schoolId)
    {
        return $this->model->where("school_id", $schoolId)->get();
    }

    public function getByProfileId(int $profile_id)
    {
        return $this->model->where("profile_id", $profile_id)->first();
    }

    public function getAll()
    {
        return $this->model->with(['user.profile', 'brand'])->get();
    }

    public function getAllWithPaggination(int $pagination, int $currentPage)
    {
        $skip = $pagination * ($currentPage - 1);
        $query = $this->model->skip($skip)->take($pagination)->get();
        return $query;
    }

    public function count()
    {
        return $this->model->count();
    }

    public function countByInternRole()
    {
        return $this->model
            ->join('users', 'users.id', '=', 'interns.user_id')
            ->where("users.role_id", '=', 3)
            ->groupBy('users.role_id')
            ->select('users.role_id', DB::raw('COUNT(users.id) as count'))
            ->get();
    }

    public function countBySchool(int $schoolId)
    {
        return $this->model->where("school_id", $schoolId)->count();
    }

    public function countByDivision(int $divisionId)
    {
        return $this->model->where("division_id", $divisionId)->count();
    }

    public function getWithoutDivision()
    {
        return $this->model->with(['user.profile', 'brand'])->where("division_id", null)->get();
    }

    public function getByDivisionId(int $id)
    {
        return $this->model->with(['user.profile', 'brand'])->where("division_id", $id)->get();
    }

    public function getMultiByNamePagination(string $name, int $pagnt, int $currentPage)
    {
        $currentPage = max(1, $currentPage);
        $skip = $pagnt * ($currentPage - 1);

        $query = $this->model
            ->select('interns.*')
            ->join("users", "interns.user_id", "users.id")
            ->join("profiles", "profiles.user_id", "users.id")
            ->where('profiles.full_name', 'LIKE', "%$name%")
            ->skip($skip)
            ->take($pagnt)
            ->get();

        return $query;
    }
}
