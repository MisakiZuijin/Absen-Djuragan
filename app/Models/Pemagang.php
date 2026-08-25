<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Pemagang extends Model
{
    use HasFactory;

    protected $table = 'interns';

    /**
     * === TAMBAHKAN BARIS INI ===
     * Memberi tahu Eloquent untuk TIDAK mengelola kolom created_at dan updated_at.
     * @var bool
     */
    public $timestamps = false;
    // =============================

    // Kita perlu mendefinisikan kolom yang boleh diisi oleh Seeder
    protected $fillable = [
        'user_id',
        'school_id',
        'division_id',
        'start_date',
        'end_date',
    ];

    public function outsiders(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'pemagang_user', 'pemagang_id', 'user_id');
    }
}