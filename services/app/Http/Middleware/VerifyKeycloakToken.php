<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyKeycloakToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get token from request
        $token = $request->bearerToken();

        // Check if token exists, otherwise return 401
        if (!$token) {
            return response()->json([
                'status' => false,
                'message' => 'Error: You are not logged in or token is missing!'
            ], 401);
        }

        $tokenParts = explode('.', $token);
        
        if (count($tokenParts) === 3) {
            $payload = json_decode(base64_decode($tokenParts[1]));
            $request->merge([
                'user_email' => $payload->email ?? null,
                'user_roles' => $payload->realm_access->roles ?? []
            ]);
        }

        return $next($request);
    }
}

// RS256 is a digital signature: combination of RSA and SHA-256
// RSA - Asymmetric encryption:
//      Mechanism: Use Keycloak's private key to sign the token. Use public key to verify the signature.
// SHA-256 - Hash function:
//      Mechanism: Before signing with RSA, the token data is hashed into a shorter string.