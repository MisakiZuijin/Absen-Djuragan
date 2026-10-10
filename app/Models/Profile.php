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
        "gender",
        "religion",
    ];

    public function isMale(): bool
    {
        $gender = strtolower(trim($this->gender ?? ''));
        return in_array($gender, ['l', 'laki-laki', 'male', 'pria', 'laki'], true);
    }

    public function isFemale(): bool
    {
        $gender = strtolower(trim($this->gender ?? ''));
        return in_array($gender, ['p', 'perempuan', 'female', 'wanita'], true);
    }

    public function isMuslim(): bool
    {
        $religion = strtolower(trim($this->religion ?? ''));
        return $religion === 'islam';
    }

    public function isMuslimMale(): bool
    {
        if (!$this->isMale()) {
            return false;
        }

        // Jika data agama belum diisi (legacy data), anggap memenuhi syarat istirahat
        // Jika sudah diisi, hanya pemagang beragama Islam yang mendapat aturan khusus Jumat
        $religion = strtolower(trim($this->religion ?? ''));
        return empty($religion) || $religion === 'islam';
    }


}
