<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use PhpParser\Node\Stmt\Return_;

class Profile extends Model {
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        "NIP",
        "full_name",
        "address",
        "phone",
        "date_of_birth",
        "birth_place",
        "user_id",
        "gender"
    ];


}
