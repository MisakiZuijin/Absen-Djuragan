<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DetailProjects extends Model {
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        "project_id",
        "intern_id",
    ];

    protected $table = 'detail_projects';

    public function project()
    {
        return $this->belongsTo(Projects::class, 'project_id');
    }

    public function user(): HasOne {
        return $this->hasOne(Intern::class, "id",  "intern_id");
    }

    public function intern() {
        return $this->belongsTo(\App\Models\Intern::class, 'intern_id');
    }
}
