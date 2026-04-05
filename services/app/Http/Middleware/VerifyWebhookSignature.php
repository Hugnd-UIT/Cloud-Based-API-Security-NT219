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
            return response()->json([
                'error' => 'Chữ ký sai, cấm cửa!'
            ], 403);
        }

        return $next($request);
    }
}