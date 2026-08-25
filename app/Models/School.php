<?php

namespace App\Models;

use App\Models\Scopes\SchoolScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ScopedBy([SchoolScope::class])]
class School extends Model {
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        "name",
        "address",
        "educational_level_id"
    ];

    public function interns()
    {
        return $this->hasMany(Intern::class);
    }
}
