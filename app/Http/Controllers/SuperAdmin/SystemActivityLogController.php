<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SystemActivityLog;
use App\Helper\ActivityLogger;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SystemActivityLogController extends Controller
{
    /**
     * Tampilkan antarmuka audit log aktivitas sistem dengan berbagai filter.
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User|null $user */
        $user = \Illuminate\Support\Facades\Auth::user();
        if ($user instanceof \App\Models\User && !$user->relationLoaded('profile')) {
            $user->loadMissing('profile');
        }

        $query = SystemActivityLog::query()->latest('created_at');
        $query = $this->applyFilters($query, $request);

        $perPage = (int) $request->input('per_page', 50);
        if ($perPage <= 0 || $perPage > 200) {
            $perPage = 50;
        }

        $logs = $query->paginate($perPage)->appends($request->all());

        // Statistik ringkas aktivitas dalam 1 query agregasi tunggal
        $today = Carbon::today();
        $todayStats = SystemActivityLog::whereDate('created_at', $today)
            ->selectRaw("
                COUNT(*) as total_logs,
                SUM(CASE WHEN action = 'LOGIN' THEN 1 ELSE 0 END) as total_logins,
                SUM(CASE WHEN user_role IN ('Super Admin', 'Admin', 'Asisten Admin') THEN 1 ELSE 0 END) as total_admin_actions,
                SUM(CASE WHEN user_role = 'Magang' THEN 1 ELSE 0 END) as total_intern_actions
            ")
            ->first();

        $totalLogsToday = (int) ($todayStats->total_logs ?? 0);
        $totalLoginsToday = (int) ($todayStats->total_logins ?? 0);
        $totalAdminActionsToday = (int) ($todayStats->total_admin_actions ?? 0);
        $totalInternActionsToday = (int) ($todayStats->total_intern_actions ?? 0);

        // Daftar modul lengkap (Master + Dynamic dari DB)
        $standardModules = [
            'Auth',
            'Presensi',
            'Presensi Offline',
            'Izin',
            'Logbook',
            'Raise Hand',
            'Broadcast',
            'Master Data',
            'User Management',
            'System Log',
        ];
        $dbModules = \Illuminate\Support\Facades\Cache::remember('system_log_distinct_modules', 3600, function () {
            return SystemActivityLog::select('module')->distinct()->pluck('module')->filter()->toArray();
        });
        $modules = array_values(array_unique(array_merge($standardModules, $dbModules)));

        // Daftar jenis aksi lengkap (Master + Dynamic dari DB)
        $standardActions = [
            'LOGIN',
            'LOGOUT',
            'LOGIN_FAILED',
            'CREATE',
            'UPDATE',
            'DELETE',
            'APPROVE',
            'REJECT',
            'RESOLVE',
            'PENALTY',
            'EXPORT',
        ];
        $dbActions = \Illuminate\Support\Facades\Cache::remember('system_log_distinct_actions', 3600, function () {
            return SystemActivityLog::select('action')->distinct()->pluck('action')->filter()->toArray();
        });
        $actions = array_values(array_unique(array_merge($standardActions, $dbActions)));

        $roles = ['Super Admin', 'Admin', 'Asisten Admin', 'Outsider', 'Magang'];

        return view('super_admin.activity_logs.index', compact(
            'logs',
            'totalLogsToday',
            'totalLoginsToday',
            'totalAdminActionsToday',
            'totalInternActionsToday',
            'modules',
            'actions',
            'roles',
            'perPage'
        ));
    }

    /**
     * Export log aktivitas ke file CSV berdasarkan prioritas:
     * 1. Checklist baris terpilih (ids)
     * 2. Pencarian & Filter aktif
     * 3. Fallback (seluruh / 5000 log terbaru)
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $query = SystemActivityLog::query()->latest('created_at');
        $isSelective = false;
        $exportedInfo = '';

        // Prioritas 1: Jika ada daftar ID terpilih dari checklist
        if ($request->filled('ids')) {
            $rawIds = $request->input('ids');
            $ids = is_array($rawIds) ? $rawIds : explode(',', (string) $rawIds);
            $ids = array_filter(array_map('trim', $ids));

            if (!empty($ids)) {
                $query->whereIn('id', $ids);
                $isSelective = true;
                $exportedInfo = count($ids) . ' baris checklist terpilih';
            }
        }

        // Prioritas 2: Jika tidak ada checklist, gunakan filter pencarian aktif
        if (!$isSelective) {
            $query = $this->applyFilters($query, $request);
            $exportedInfo = 'berdasarkan kriteria filter/pencarian';
        }

        $filename = 'audit_logs_' . ($isSelective ? 'terpilih_' : '') . date('Ymd_His') . '.csv';

        ActivityLogger::log('EXPORT', 'System Log', "Super Admin mengekspor berkas CSV log aktivitas ({$exportedInfo}).");

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control' => 'no-store, no-cache',
        ];

        return response()->stream(function () use ($query) {
            $handle = fopen('php://output', 'w');
            // Add UTF-8 BOM for proper Excel rendering
            fputs($handle, "\xEF\xBB\xBF");

            // Header kolom CSV
            fputcsv($handle, ['ID', 'Waktu (WIB)', 'Nama User', 'Peran (Role)', 'Aksi', 'Modul', 'Deskripsi Aktivitas', 'Alamat IP', 'Perangkat / Browser']);

            $query->chunk(500, function ($records) use ($handle) {
                foreach ($records as $log) {
                    fputcsv($handle, [
                        $log->id,
                        $log->created_at ? Carbon::parse($log->created_at)->format('Y-m-d H:i:s') : '-',
                        $log->user_name ?? 'Sistem',
                        $log->user_role ?? '-',
                        $log->action,
                        $log->module,
                        $log->description,
                        $log->ip_address ?? '-',
                        $log->user_agent ?? '-',
                    ]);
                }
            });

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Terapkan seluruh filter pencarian pada query SystemActivityLog.
     */
    private function applyFilters($query, Request $request)
    {
        // 1. Filter: Pencarian umum multi-kata (deskripsi, nama, role, modul, aksi, IP, perangkat, tanggal)
        if ($request->filled('search')) {
            $rawSearch = trim($request->input('search'));
            $tokens = array_filter(preg_split('/\s+/', $rawSearch));

            if (!empty($tokens)) {
                $query->where(function ($masterQuery) use ($tokens, $rawSearch) {
                    // Full phrase match
                    $masterQuery->where(function ($q) use ($rawSearch) {
                        $q->where('description', 'like', "%{$rawSearch}%")
                            ->orWhere('user_name', 'like', "%{$rawSearch}%")
                            ->orWhere('ip_address', 'like', "%{$rawSearch}%")
                            ->orWhere('action', 'like', "%{$rawSearch}%")
                            ->orWhere('module', 'like', "%{$rawSearch}%")
                            ->orWhere('user_role', 'like', "%{$rawSearch}%")
                            ->orWhere('user_agent', 'like', "%{$rawSearch}%")
                            ->orWhere('created_at', 'like', "%{$rawSearch}%");
                    });

                    // OR Every token must match at least one column
                    $masterQuery->orWhere(function ($allTokensQuery) use ($tokens) {
                        foreach ($tokens as $token) {
                            $allTokensQuery->where(function ($q) use ($token) {
                                $q->where('description', 'like', "%{$token}%")
                                    ->orWhere('user_name', 'like', "%{$token}%")
                                    ->orWhere('ip_address', 'like', "%{$token}%")
                                    ->orWhere('action', 'like', "%{$token}%")
                                    ->orWhere('module', 'like', "%{$token}%")
                                    ->orWhere('user_role', 'like', "%{$token}%")
                                    ->orWhere('user_agent', 'like', "%{$token}%")
                                    ->orWhere('created_at', 'like', "%{$token}%");
                            });
                        }
                    });
                });
            }
        }

        // 2. Filter: Modul
        if ($request->filled('module')) {
            $query->where('module', trim($request->input('module')));
        }

        // 3. Filter: Jenis Aksi
        if ($request->filled('action')) {
            $action = trim($request->input('action'));
            $query->where(function ($q) use ($action) {
                $q->where('action', $action)
                    ->orWhere('action', 'like', "%{$action}%");
            });
        }

        // 4. Filter: Role User
        if ($request->filled('role')) {
            $query->where('user_role', trim($request->input('role')));
        }

        // 5. Filter: Rentang Tanggal / Tanggal Spesifik
        if ($request->filled('date_start') && $request->filled('date_end')) {
            $startDate = Carbon::parse($request->input('date_start'))->startOfDay();
            $endDate = Carbon::parse($request->input('date_end'))->endOfDay();
            $query->whereBetween('created_at', [$startDate, $endDate]);
        } elseif ($request->filled('date_start')) {
            $query->whereDate('created_at', $request->input('date_start'));
        } elseif ($request->filled('date_end')) {
            $query->whereDate('created_at', '<=', $request->input('date_end'));
        }

        return $query;
    }

    /**
     * Hapus log lama berdasarkan umur hari (misal > 30, 60, atau 90 hari).
     */
    public function clearOldLogs(Request $request)
    {
        $validated = $request->validate([
            'days' => 'required|integer|in:30,60,90,180,365',
        ]);

        $days = (int) $validated['days'];
        $cutoffDate = Carbon::now()->subDays($days);

        $deletedCount = SystemActivityLog::where('created_at', '<', $cutoffDate)->delete();

        ActivityLogger::log(
            'DELETE',
            'System Log',
            "Super Admin membersihkan {$deletedCount} catatan log aktivitas yang berusia lebih dari {$days} hari."
        );

        return redirect()->route('super-admin.activity-logs.index')
            ->with('success', "Berhasil menghapus {$deletedCount} log aktivitas lama (> {$days} hari).");
    }
}
