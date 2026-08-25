<?php

namespace App\Repositories\Implementation;

use App\Models\Intern;
use App\Repositories\Interface\InternRepository;

use Illuminate\Support\Facades\DB;


class InternRepositoryIMPL implements InternRepository {
    protected $model;

    public function __construct(Intern $internModel) {
        $this->model = $internModel;
    }

    public function store($data) {
        $this->model->create($data);
    }

    public function update($id, $data) {

        $intern = $this->model->find($id);

        $intern->update($data);

        return $intern;
    }

    public function getById($id) {
        return $this->model->find($id);
    }

    public function getBySchoolId($schoolId) {
        return $this->model->where("school_id", $schoolId)->get();
    }

    public function getByProfileId($profile_id) {
        return $this->model->where("profile_id", $profile_id)->first();
    }

    public function getAll() {
        return $this->model->get();
    }

    public function getAllWithPaggination($pagination, $currentPage) {
        $skip = $pagination * ($currentPage - 1);
        $query = $this->model->skip($skip)->take($pagination)->get();
        return $query;
    }

    public function count() {
        return $this->model->count();
    }

    public function countByInternRole() {
        return $this->model
            ->join('users', 'users.id', '=', 'interns.user_id')
            ->where("users.role_id", '=', 3)
            ->groupBy('users.role_id')
            ->select('users.role_id', DB::raw('COUNT(users.id) as count'))
            ->get();
    }



    public function countBySchool($schoolId) {
        return $this->model->where("school_id", $schoolId)->count();
    }

    public function countByDivision($divisionId) {
        return $this->model->where("division_id", $divisionId)->count();
    }

    public function getWithoutDivision() {
        return $this->model->where("division_id", null)->get();
    }

    public function getByDivisionId($id) {
        return $this->model->where("division_id", $id)->get();
    }

    public function getMultiByNamePagination($name, $pagnt, $currentPage) {
        $currentPage = max(1, $currentPage);
        $skip = $pagnt * ($currentPage - 1);

        $query = $this->model
            ->join("users", "interns.user_id", "users.id")
            ->join("profiles", "profiles.user_id", "users.id")
            ->where('profiles.full_name', 'LIKE', "%$name%")
            ->skip($skip)
            ->take($pagnt)
            ->get();

        return $query;
    }
}
