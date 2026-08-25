<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Broadcast extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'message',
        // [MODIFIKASI] 'image' dihapus karena sekarang kita menggunakan relasi untuk banyak gambar
        'broadcast_type',
    ];

    /**
     * The attributes that should be cast.
     * Ini akan membuat Laravel secara otomatis meng-eager load relasi ini
     * saat model Broadcast di-serialize ke JSON. Sangat berguna untuk JavaScript.
     *
     * @var array
     */
    // [MODIFIKASI] Menambahkan 'images' agar otomatis di-load bersama divisions dan users
    protected $with = ['divisions', 'users', 'images'];

    /**
     * Relasi ke model Division (Many-to-Many).
     */
    public function divisions()
    {
        return $this->belongsToMany(Division::class, 'broadcast_division', 'broadcast_id', 'division_id');
    }

    /**
     * Relasi ke model User (Many-to-Many).
     */
    public function users()
    {
        // Pastikan untuk juga memuat relasi profile user agar namanya bisa ditampilkan
        return $this->belongsToMany(User::class, 'broadcast_user', 'broadcast_id', 'user_id')->with('profile');
    }

    /**
     * [TAMBAHAN]
     * Mendefinisikan relasi "hasMany" ke model BroadcastImage.
     * Ini adalah bagian terpenting untuk memperbaiki error Anda.
     * Satu pengumuman bisa memiliki banyak gambar.
     */
    public function images()
    {
        return $this->hasMany(BroadcastImage::class);
    }
}