<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Firebase\JWT\JWT;
use Firebase\JWT\JWK;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class VerifyKeycloakToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'status' => false,
                'message' => 'Error 401: App - Missing Token!'
            ], 401);
        }

        try {
            $jwks = Cache::remember('keycloak_jwks', 3600, function () {
                $response = Http::keycloak()->get('/realms/payshield-realm/protocol/openid-connect/certs');
                return $response->json();
            });

            $decoded = JWT::decode($token, JWK::parseKeySet($jwks));

            $request->merge([
                'user_email' => $decoded->email ?? null,
                'user_roles' => $decoded->realm_access->roles ?? []
            ]);

        } catch (\Firebase\JWT\ExpiredException $e) {
            return response()->json(['message' => 'Error 401: Token has expired!'], 401);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Lỗi xác minh: ' . $e->getMessage()], 403);
        }

        return $next($request);
    }
}

// RS256 is a digital signature: combination of RSA and SHA-256
// RSA - Asymmetric encryption:
//      Mechanism: Use Keycloak's private key to sign the token. Use public key to verify the signature.
// SHA-256 - Hash function:
//      Mechanism: Before signing with RSA, the token data is hashed into a shorter string.