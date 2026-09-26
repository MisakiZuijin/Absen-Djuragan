<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class PermitLog extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'permit_logs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'attendance_id',
        'type',
        'description',
        'authorized_by',
        'start_time',
        'end_time',
        'duration_in_minutes',
        'agreed_duration_minutes',
        'is_mandatory_replace',
        'approval_status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'agreed_duration_minutes' => 'integer',
        'is_mandatory_replace' => 'boolean',
    ];

    /**
     * Get the attendance record that this permit log belongs to.
     */
    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }

    /**
     * Menutup izin keluar yang sudah melewati waktu kesepakatan.
     * end_time diisi dengan jam mulai + agreed_duration_minutes agar
     * riwayat menampilkan jam selesai sesuai kesepakatan, bukan jam tutup.
     */
    public static function closeExpiredLeavePermits(): void
    {
        static::where('type', 'leave')
            ->whereNull('end_time')
            ->whereNotNull('agreed_duration_minutes')
            ->whereRaw('TIMESTAMPADD(MINUTE, agreed_duration_minutes, start_time) <= NOW()')
            ->update([
                'end_time'            => DB::raw('TIMESTAMPADD(MINUTE, agreed_duration_minutes, start_time)'),
                'duration_in_minutes' => DB::raw('agreed_duration_minutes'),
                'updated_at'          => now(),
            ]);
    }
}