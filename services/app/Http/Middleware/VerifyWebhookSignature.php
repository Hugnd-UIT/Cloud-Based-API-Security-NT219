<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifyWebhookSignature
{
    public function handle(Request $request, Closure $next)
    {
        $secret = config('services.payshield.WEBHOOK_SECRET');
        $signature = $request->header('X-Signature');

        $expectedSignature = hash_hmac('sha256', $request->getContent(), $secret);

        if (!hash_equals($expectedSignature, (string) $signature)) {
            // TẠM THỜI THÊM 3 DÒNG DEBUG NÀY VÀO ĐỂ BẮT LỖI
            return response()->json([
                'error' => 'Chữ ký sai, cấm cửa!',
                'debug_mat_khau_cua_laravel_dang_dung' => $secret,
                'debug_chu_ky_cua_postman' => $signature,
                'debug_chu_ky_cua_laravel' => $expectedSignature
            ], 403);
        }

        return $next($request);
    }
}