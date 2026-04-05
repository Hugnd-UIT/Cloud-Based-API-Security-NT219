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
            return response()->json(['error' => 'Thiếu mã event_id!'], 400);
        }

        if (Cache::has('webhook_processed_' . $eventId)) {
            Log::warning("[BẢO VỆ] Phát hiện gói tin nhai lại (Replay): {$eventId}. Đã chặn!");
            return response()->json([
                'status' => 'success',
                'message' => 'Đã tiếp nhận giao dịch này trước đó.'
            ]);
        }

        Cache::put('webhook_processed_' . $eventId, true, 86400);
        Log::info("[LỄ TÂN] Đã nhận Webhook {$eventId}, đang đẩy vào hàng đợi...");
        ProcessSalaryAdvanceJob::dispatch($data);

        return response()->json([
            'status' => 'success',
            'message' => 'PayShield đã đưa lệnh ứng lương vào hàng đợi xử lý!'
        ]);
    }
}