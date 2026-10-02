<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class ChangeTimeRegistration extends Model
{
    use HasFactory;

    protected $table = 'change_time_registrations';

    protected $fillable = [
        'intern_id',
        'requested_date',
        'shift_id',
        'office_id',
        'target_schedule_ids',
        'estimated_minutes',
        'reason',
        'status',
        'admin_notes',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'requested_date' => 'date',
        'approved_at' => 'datetime',
        'estimated_minutes' => 'integer',
        'target_schedule_ids' => 'array',
    ];

    /**
     * Relasi ke Pemagang (Intern).
     */
    public function intern(): BelongsTo
    {
        return $this->belongsTo(Intern::class);
    }

    /**
     * Relasi ke Shift yang diajukan / disetujui.
     */
    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    /**
     * Relasi ke Kantor penempatan yang diajukan / disetujui.
     */
    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    /**
     * Relasi ke Admin yang menyetujui / menolak.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Relasi ke Catatan / Pesan Diskusi (Admin & Pemagang).
     */
    public function notes(): HasMany
    {
        return $this->hasMany(ChangeTimeNote::class, 'registration_id')->orderBy('created_at', 'asc');
    }

    /**
     * Cek apakah ada pesan dari admin yang belum dibaca pemagang.
     */
    public function hasUnreadAdminNotes(): bool
    {
        return $this->notes()->where('is_from_admin', true)->where('is_read', false)->exists();
    }

    /**
     * Jumlah pesan dari admin yang belum dibaca pemagang.
     */
    public function unreadAdminNotesCount(): int
    {
        return $this->notes()->where('is_from_admin', true)->where('is_read', false)->count();
    }

    /**
     * Jumlah pesan dari pemagang yang belum dibaca admin.
     */
    public function unreadInternNotesCount(): int
    {
        return $this->notes()->where('is_from_admin', false)->where('is_read', false)->count();
    }

    /**
     * Scope untuk pendaftaran aktif (pending atau approved yang belum lewat).
     */
    public function scopeActiveRegistration(Builder $query, int $internId): Builder
    {
        return $query->where('intern_id', $internId)
            ->whereIn('status', ['pending', 'approved'])
            ->where(function ($q) {
                $q->whereNull('requested_date')
                  ->orWhereDate('requested_date', '>=', Carbon::today('Asia/Jakarta'));
            });
    }

    /**
     * Scope untuk pendaftaran yang disetujui pada tanggal tertentu.
     */
    public function scopeApprovedForDate(Builder $query, int $internId, string $date): Builder
    {
        return $query->where('intern_id', $internId)
            ->where('status', 'approved')
            ->whereDate('requested_date', $date);
    }

    /**
     * Accessor label status bahasa Indonesia.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Menunggu Persetujuan',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            'completed' => 'Selesai Dilaksanakan',
            'cancelled' => 'Dibatalkan',
            default => ucfirst($this->status),
        };
    }

    /**
     * Accessor badge style class.
     */
    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'bg-amber-100 text-amber-800 border-amber-200',
            'approved' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            'rejected' => 'bg-rose-100 text-rose-800 border-rose-200',
            'completed' => 'bg-blue-100 text-blue-800 border-blue-200',
            'cancelled' => 'bg-slate-100 text-slate-700 border-slate-200',
            default => 'bg-gray-100 text-gray-800 border-gray-200',
        };
    }
}
