<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HandRaise extends Model
{
    protected $fillable = ['user_id', 'is_raised', 'project_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function project()
    {
        return $this->belongsTo(Projects::class, 'project_id');
    }
}
