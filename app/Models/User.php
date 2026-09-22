<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // protected $table = 'users'; // Uncomment this if you have a custom table name

    protected $fillable = [
        'username',
        'email',
        'password',
        'role_id',
        'os',
        'browser',
        'device',
        'is_reset_token',
        'is_active',
        'is_confirm',
        'is_gps_support',
        'is_gps_activate'
    ];

    protected $hidden = [
        'password'
    ];

    public function setPasswordAttribute(string $value)
    {
        if (!empty($value)) {
            $this->attributes['password'] = Hash::make($value);
        }
    }

    public function profile()
    {
        return $this->hasOne(Profile::class, 'user_id');
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, "role_id", "id");
    }

    public function intern(): HasOne
    {
        return $this->hasOne(Intern::class, "user_id", "id");
    }

    /**
     * Mendefinisikan relasi many-to-many ke model Pemagang.
     * Seorang User (misal: outsider/pembimbing) bisa terhubung dengan banyak Pemagang.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function pemagangs(): BelongsToMany
    {
        return $this->belongsToMany(Pemagang::class, 'pemagang_user');
    }

    public function outsider()
    {
        return $this->hasOne(Outsider::class);
    }

    public function whatsappNumbers()
    {
        return $this->hasMany(WhatsappNumber::class, 'intern_id');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'user_id');
    }

    public function prayers()
    {
        return $this->hasMany(Prayer::class, 'user_id');
    }

    public function handRaise()
    {
        return $this->hasOne(HandRaise::class, 'user_id');
    }

    public function scopeIntern(EloquentBuilder $query)
    {
        return $query->where('role_id', 3);
    }

    public function logActivities()
    {
        return $this->hasManyThrough(
            LogActivity::class,
            DetailSchedule::class,
            'intern_id', // Foreign key on detail_schedules table
            'id', // Foreign key on log_activities table
            'id', // Local key on users table
            'log_activity_id' // Local key on detail_schedules table
        );
    }

    public function getDurationFromCreation()
    {
        $created_at = Carbon::parse($this->created_at);
        $now = Carbon::now();
        $diff = $now->diff($created_at);

        $hours = $diff->h;
        $minutes = $diff->i;

        $duration = '';
        if ($hours > 0) {
            $duration .= $hours . ' jam ';
        }
        if ($minutes > 0) {
            $duration .= $minutes . ' menit';
        }

        return trim($duration) ?: '0 menit';
    }

    /**
     * Cek apakah pemagang memiliki tugas atau project aktif.
     *
     * @return bool
     */
    public function hasActiveTasks(): bool
    {
        return $this->getActiveTasksCount() > 0;
    }

    /**
     * Hitung total tugas & project aktif pemagang yang belum selesai.
     *
     * @return int
     */
    public function getActiveTasksCount(): int
    {
        $internId = $this->intern?->id;
        $activeProjectsCount = 0;

        if ($internId) {
            $activeProjectsCount = DetailProjects::where('intern_id', $internId)
                ->whereHas('project', function ($q) {
                    $q->where('status', '!=', 'done');
                })
                ->count();
        }

        $activeMentorTasksCount = HandRaise::where('user_id', $this->id)
            ->where('type', 'new_task')
            ->where('status', '!=', 'done')
            ->where(function ($q) {
                $q->whereNull('project_id')
                    ->orWhereHas('project', function ($pq) {
                        $pq->where('status', '!=', 'done');
                    });
            })
            ->where(function ($q) {
                $q->where('status', 'in_progress')
                    ->orWhereNotNull('admin_response');
            })
            ->count();

        return $activeProjectsCount + $activeMentorTasksCount;
    }
}
