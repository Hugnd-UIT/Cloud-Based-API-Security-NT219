<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function handle(Request $request)
    {
        $data = $request->all();

        Log::info('Đã nhận Webhook an toàn: ', $data);

        return response()->json([
            'status' => 'success',
            'message' => 'PayShield đã nhận dữ liệu!'
        ]);
    }
}