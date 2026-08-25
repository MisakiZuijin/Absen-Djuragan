<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\HandRaise;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class HandRaiseController extends Controller
{
    /**
     * Method untuk mendapatkan jumlah notifikasi untuk admin
     */
    public function getCount()
    {
        try {
            $count = HandRaise::where('is_raised', true)->count();
            
            return response()->json([
                'count' => $count,
                'type' => 'raise_hand',
                'timestamp' => now()->toISOString(),
                'status' => 'success'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'count' => 0,
                'type' => 'raise_hand',
                'timestamp' => now()->toISOString(),
                'status' => 'error',
                'message' => 'Failed to fetch count'
            ], 500);
        }
    }

    /**
     * Tampilkan halaman index raise hand
     */
    public function index()
    {
        try {
            $handRaises = $this->fetchHandRaises();
            return view('admin.raise-hand', compact('handRaises'));
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Gagal memuat data: ' . $e->getMessage());
        }
    }

    /**
     * Get table data untuk AJAX refresh
     */
    public function getTableData()
    {
        try {
            $handRaises = $this->fetchHandRaises();
            return view('admin.partials.raise-hand-table-body', compact('handRaises'))->render();
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Fetch data raise hand dengan relasi
     */
    private function fetchHandRaises()
    {
        return HandRaise::with([
                'user.profile',
                'user.intern.school',
                'project'
            ])
            ->where('is_raised', true)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Konfirmasi dan selesaikan raise hand request
     */
    public function confirmAction(Request $request, $id)
    {
        try {
            $handRaise = HandRaise::findOrFail($id);
            $userName = $handRaise->user->profile->full_name ?? $handRaise->user->name ?? 'User';

            $handRaise->update([
                'is_raised' => false,
                'resolved_at' => now(),
                'resolved_by' => auth()->id() // Tambahkan siapa yang menyelesaikan
            ]);

            // Log activity untuk tracking
            Log::info("Raise hand confirmed by " . auth()->user()->name . " for user: " . $userName);

            return redirect()->route('admin.raiseHand.index')
                             ->with('success', "Permintaan raise hand dari {$userName} berhasil dikonfirmasi dan telah diselesaikan.");

        } catch (Exception $e) {
            Log::error('Error confirming raise hand: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal mengkonfirmasi: ' . $e->getMessage());
        }
    }

    public function getEnhancedCount(): JsonResponse
{
    try {
        $count = HandRaise::where('is_raised', true)->count();

        // Get recent raise hands for additional context
        $recentRaiseHands = HandRaise::with(['user.profile', 'user.intern.school'])
            ->where('is_raised', true)
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get()
            ->map(function ($raiseHand) {
                return [
                    'id' => $raiseHand->id,
                    'user_name' => $raiseHand->user->profile->full_name ?? $raiseHand->user->name,
                    'school' => $raiseHand->user->intern->school->name ?? 'Unknown',
                    'created_at' => $raiseHand->created_at->diffForHumans(),
                    'reason' => $raiseHand->reason ?? 'Tidak ada keterangan'
                ];
            });

        return response()->json([
            'success' => true,
            'count' => $count,
            'recent_requests' => $recentRaiseHands,
            'timestamp' => now()->toISOString(),
            'message' => $count > 0 ? "Ada {$count} permintaan raise hand" : 'Tidak ada permintaan'
        ]);

    } catch (Exception $e) {
        Log::error('Error getting enhanced raise hand count: ' . $e->getMessage());

        return response()->json([
            'success' => false,
            'count' => 0,
            'recent_requests' => [],
            'error' => $e->getMessage(),
            'timestamp' => now()->toISOString()
        ], 500);
    }
}

/**
 * Lightweight polling method for fallback
 */
public function pollNotifications(): JsonResponse
{
    try {
        $count = HandRaise::where('is_raised', true)->count();

        return response()->json([
            'success' => true,
            'count' => $count,
            'timestamp' => now()->toISOString(),
            'polling' => true
        ]);

    } catch (Exception $e) {
        return response()->json([
            'success' => false,
            'count' => 0,
            'error' => $e->getMessage(),
            'timestamp' => now()->toISOString()
        ], 500);
    }
}

 public function latest()
    {
        $latest = HandRaise::latest()->first();
        return response()->json($latest);
    }

/**
 * Quick resolve method for instant feedback
 */
public function quickResolve(Request $request, $id): JsonResponse
{
    try {
        $handRaise = HandRaise::findOrFail($id);
        $userName = $handRaise->user->profile->full_name ?? $handRaise->user->name ?? 'User';

        $handRaise->update([
            'is_raised' => false,
            'resolved_at' => now(),
            'resolved_by' => auth()->id()
        ]);

        // Log activity
        Log::info("Quick resolve raise hand by " . auth()->user()->name . " for user: " . $userName);

        // Get updated count
        $newCount = HandRaise::where('is_raised', true)->count();

        return response()->json([
            'success' => true,
            'message' => "Permintaan dari {$userName} berhasil diselesaikan",
            'new_count' => $newCount,
            'resolved_user' => $userName
        ]);

    } catch (Exception $e) {
        Log::error('Error quick resolving raise hand: ' . $e->getMessage());

        return response()->json([
            'success' => false,
            'error' => 'Gagal menyelesaikan permintaan: ' . $e->getMessage()
        ], 500);
    }
}

    public function stream()
    {
        // Validate user access
        if (!auth()->check() || auth()->user()->role_id !== 1) {
            abort(403);
        }

        return response()->stream(function () {
            // Set proper SSE headers
            echo "data: " . json_encode(['status' => 'connected', 'time' => now()]) . "\n\n";
            
            if (ob_get_level()) {
                ob_end_flush();
            }
            flush();

            $lastCount = 0;
            $iterations = 0;

            while ($iterations < 60) { // Maximum 2 minutes (60 * 2 seconds)
                if (connection_aborted()) {
                    break;
                }

                try {
                    $currentCount = HandRaise::where('is_raised', true)->count();

                    if ($currentCount !== $lastCount) {
                        echo "data: " . json_encode([
                            'count' => $currentCount,
                            'type' => 'raise_hand',
                            'timestamp' => now()->toISOString()
                        ]) . "\n\n";
                        
                        $lastCount = $currentCount;
                        
                        if (ob_get_level()) {
                            ob_end_flush();
                        }
                        flush();
                    }

                } catch (\Exception $e) {
                    \Log::error('SSE Stream error: ' . $e->getMessage());
                    echo "data: " . json_encode(['error' => 'Connection error']) . "\n\n";
                    if (ob_get_level()) {
                        ob_end_flush();
                    }
                    flush();
                    break;
                }

                sleep(2);
                $iterations++;
            }
        }, 200, [
            'Content-Type' => 'text/plain',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no'
        ]);
    }

    public function getNotificationData(): JsonResponse
    {
        try {
            $handRaises = HandRaise::with(['user.profile', 'user.intern.school'])
                ->where('is_raised', true)
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($raise) {
                    return [
                        'id' => $raise->id,
                        'user_name' => $raise->user->profile->full_name ?? $raise->user->name,
                        'school' => $raise->user->intern->school->name ?? 'Unknown',
                        'time_ago' => $raise->created_at->diffForHumans(),
                        'reason' => $raise->reason ?? 'Membutuhkan bantuan'
                    ];
                });

            return response()->json([
                'success' => true,
                'count' => HandRaise::where('is_raised', true)->count(),
                'recent_requests' => $handRaises,
                'timestamp' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'count' => 0,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function toggle()
    {
        $user = auth()->user();
        
        $handRaise = HandRaise::firstOrCreate(
            ['user_id' => $user->id],
            [
                'is_raised' => false,
                'project_id' => null, // sesuaikan dengan kebutuhan
            ]
        );
        
        // Toggle status
        $handRaise->is_raised = !$handRaise->is_raised;
        $handRaise->save();
        
        $status = $handRaise->is_raised ? 'diangkat' : 'diturunkan';
        
        return redirect()->back()->with('success', "Tangan berhasil {$status}!");
    }
}
