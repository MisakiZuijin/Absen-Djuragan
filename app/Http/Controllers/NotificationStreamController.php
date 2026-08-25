<?php

namespace App\Http\Controllers;

use App\Models\HandRaise;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class NotificationStreamController extends Controller
{
    /**
     * Stream notifications using Server-Sent Events
     */
    public function stream(Request $request): StreamedResponse
    {
        return new StreamedResponse(function () {
            // Set headers for SSE
            header('Content-Type: text/event-stream');
            header('Cache-Control: no-cache');
            header('Connection: keep-alive');
            header('X-Accel-Buffering: no'); // Nginx specific

            // Initial connection message
            echo "event: connected\n";
            echo "data: " . json_encode(['message' => 'Connected to notification stream']) . "\n\n";

            $lastCheck = time();
            $previousCount = HandRaise::where('is_raised', true)->count();

            while (true) {
                // Check if client disconnected
                if (connection_aborted()) {
                    break;
                }


                $currentTime = time();

                // Check for updates every 2 seconds
                if ($currentTime - $lastCheck >= 2) {
                    $currentCount = HandRaise::where('is_raised', true)->count();

                    // Send update if count changed
                    if ($currentCount !== $previousCount) {
                        $eventData = [
                            'type' => 'raise_hand',
                            'count' => $currentCount,
                            'success' => true,
                            'timestamp' => now()->toISOString(),
                            'previous_count' => $previousCount
                        ];

                        echo "event: raise-hand-update\n";
                        echo "data: " . json_encode($eventData) . "\n\n";

                        $previousCount = $currentCount;

                        Log::info("SSE: Raise hand count updated to {$currentCount}");
                    }

                    // Send heartbeat to keep connection alive
                    echo "event: heartbeat\n";
                    echo "data: " . json_encode(['timestamp' => $currentTime]) . "\n\n";

                    $lastCheck = $currentTime;
                }

                // Flush output
                if (ob_get_level()) {
                    ob_flush();
                }
                flush();

                // Small sleep to prevent high CPU usage
                usleep(500000); // 0.5 seconds
            }
        });
    }

    /**
     * Enhanced count method with additional metadata
     */
    public function getEnhancedCount(): \Illuminate\Http\JsonResponse
    {
        try {
            $count = HandRaise::where('is_raised', true)->count();

            // Get recent raise hands for additional context
            $recentRaiseHands = HandRaise::with(['user.profile'])
                ->where('is_raised', true) 
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($raiseHand) {
                    return [
                        'id' => $raiseHand->id,
                        'user_name' => $raiseHand->user->profile->full_name ?? $raiseHand->user->name,
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

        } catch (\Exception $e) {
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
}
