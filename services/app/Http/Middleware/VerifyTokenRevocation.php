<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\HttpFoundation\Response;

class VerifyTokenRevocation
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $header = $request->header('Authorization');
        if (!$header) return $next($request);

        $token = str_replace('Bearer ', '', $header);
        $payload = json_decode(base64_decode(explode('.', $token)[1]), true);
        $jti = $payload['jti'] ?? null;

        if (!$jti) return $next($request);

        $current = trim(explode(',', $request->header('X-Forwarded-For'))[0]);
        if (!$current) $current = $request->ip();

        $redisKey = "token_origin_ip:$jti";
        $stored = Redis::get($redisKey);

        if (!$stored) {
            Redis::setex($redisKey, 300, $current);
        } elseif ($stored !== $current) {
            Log::warning("Security Alert: Token $jti used from unauthorized IP. Current: $current, Original: $stored");
            return response()->json([
                'message' => 'Security Alert: This token is locked to another IP address.'
            ], 403);
        }

        return $next($request);
    }
}
