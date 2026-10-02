<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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
        'category',
        'title',
        'message',
        // [MODIFIKASI] 'image' dihapus karena sekarang kita menggunakan relasi untuk banyak gambar
        'broadcast_type',
        'scheduled_at',
        'requires_report',
        'report_question',
        'created_by',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'requires_report' => 'boolean',
    ];

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

    /**
     * Target per shift (Many-to-Many).
     */
    public function shifts()
    {
        return $this->belongsToMany(Shift::class, 'broadcast_shift', 'broadcast_id', 'shift_id');
    }

    /**
     * Target per kantor/brand (Many-to-Many).
     */
    public function offices()
    {
        return $this->belongsToMany(Office::class, 'broadcast_office', 'broadcast_id', 'office_id');
    }

    /**
     * Laporan yang dikirim pemagang untuk broadcast ini.
     */
    public function reports()
    {
        return $this->hasMany(BroadcastReport::class);
    }

    /**
     * Broadcast tanpa jadwal dianggap langsung terkirim;
     * yang terjadwal terkirim saat waktunya tiba (tanpa cron).
     */
    public function isDue(): bool
    {
        return is_null($this->scheduled_at) || $this->scheduled_at->lessThanOrEqualTo(now());
    }

    /**
     * Scope khusus untuk Pengumuman Banner/Dashboard biasa
     */
    public function scopeAnnouncements(Builder $query): Builder
    {
        return $query->where('category', 'announcement');
    }

    /**
     * Scope khusus untuk Broadcast Pesan/Pertanyaan Terjadwal
     */
    public function scopeScheduledBroadcasts(Builder $query): Builder
    {
        return $query->where('category', 'scheduled_broadcast');
    }
}
