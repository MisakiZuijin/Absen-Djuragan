<?php

namespace App\Models;

use App\Models\NameProjects;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Projects extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        "name_project_id",
        "team",
        "description",
        "status"
    ];

    // In Projects.php model
    public function detailProjects()
    {
        return $this->hasMany(DetailProjects::class, 'project_id');
    }

    public function members()
    {
        return $this->hasMany(DetailProjects::class, 'project_id', 'id');
    }

    public function nameProject()
    {
        return $this->belongsTo(NameProjects::class, 'name_project_id');
    }
}
