<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class VerifyTokenRevocation
{
    private const TIME = 60; 

    public function handle(Request $request, Closure $next)
    {
        $header = $request->header('Authorization');
        if (!$header) return response()->json(['message' => 'Missing Token'], 401);

        $token = str_replace('Bearer ', '', $header);
        
        $timestamp = $request->header('X-Timestamp');
        $nonce = $request->header('X-Nonce');
        $signature = $request->header('X-Signature');

        if (!$timestamp || !$nonce || !$signature) {
            return response()->json(['message' => 'Missing Security Headers (Timestamp/Nonce/Signature).'], 400);
        }

        $now = time();
        if (abs($now - $timestamp) > self::TIME) {
            Log::warning("Replay Attack Alert: Timestamp expired. Request TS: $timestamp, Server TS: $now");
            return response()->json(['message' => 'Request expired.'], 403);
        }

        $redis = "replay_nonce:$nonce";
        if (Redis::exists($redis)) {
            Log::warning("Replay Attack Alert: Nonce $nonce already used!");
            return response()->json(['message' => 'Replay attack detected! Nonce already used.'], 403);
        }

        $method = $request->method();
        $uri = '/' . $request->path();
        $payload = "$method|$uri|$timestamp|$nonce|$token";
        $secret = env('CLIENT_SECRET');

        $expect = hash_hmac('sha256', $payload, $secret);

        if (!hash_equals($expect, $signature)) {
            Log::warning("Tampering Alert: Invalid Signature!");
            return response()->json(['message' => 'Invalid Signature.'], 403);
        }

        Redis::setex($redis, self::TIME, "used");

        return $next($request);
    }
}