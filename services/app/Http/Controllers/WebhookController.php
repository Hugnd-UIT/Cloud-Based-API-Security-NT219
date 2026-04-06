<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Jobs\ProcessSalaryAdvanceJob;

class WebhookController extends Controller
{
    public function handle(Request $request)
    {
        $data = $request->all();
        $eventId = $data['event_id'] ?? null;

        if (!$eventId) {
            return response()->json(['error' => 'Missing event_id!'], 400);
        }

        if (Cache::has('webhook_processed_' . $eventId)) {
            Log::warning("[Security] Replay Attack Detected: {$eventId}. Blocked!");
            return response()->json([
                'status' => 'success',
                'message' => 'This transaction has already been processed.'
            ]);
        }

        Cache::put('webhook_processed_' . $eventId, true, 86400);
        Log::info("[INFO] Webhook {$eventId} received, pushing to queue...");
        ProcessSalaryAdvanceJob::dispatch($data);

        return response()->json([
            'status' => 'success',
            'message' => 'PayShield has queued the salary advance request for processing!'
        ]);
    }
}