<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckKeycloakToken
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

        try {
            $public_key = <<<EOD
                -----BEGIN PUBLIC KEY-----
                MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAnyo6T5aZ8aRphvp2SltkoWg2Zwdt//hEEDspsE6TEe8b1sPSTwfw5wu80TvqoKGkQuLKR3FStTHc9iCgDQTMXBetMei9IqJWOngyi3neq9xQhrrdOiLShW1v3JshzrIKlNhjZMprwBwNiW4LqGxW3g32UMULc7ASe10+QLeH+oq4ke6psHWlA3RDDIgbokewBONg3niqCmpC6Uu9P4mHkdRSKbZmY2K7buqdTwA90FArifveG2FX4EfDqqFf1wj7bav1+Ar5KQyPa63SUSKzDlbqjLeQYd6qg/38qfmwE8NkTPGJXloPbm/EpCXwwAzp9UH1pTblIxxCcp3m8JQSYwIDAQAB
                -----END PUBLIC KEY-----
                EOD;
            
            // Check if public key is configured
            if (!$public_key) {
                throw new Exception("Public key is not configured");
            }

            // Decode token
            $decoded_token = JWT::decode($token, new Key($public_key, 'RS256'));
            
            $request->merge([
                // Attach email to request
                'user_email' => $decoded_token->email ?? null,
                // Attach roles to request
                'user_roles' => $decoded_token->realm_access->roles ?? []
            ]);
            
            // Continue request to Controller
            return $next($request);

        } catch (\Firebase\JWT\ExpiredException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Token has expired! Please log in again.'
            ], 401);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid or tampered token!'
            ], 401);
        }
    }
}

// RS256 is a digital signature: combination of RSA and SHA-256
// RSA - Asymmetric encryption:
//      Mechanism: Use Keycloak's private key to sign the token. Use public key to verify the signature.
// SHA-256 - Hash function:
//      Mechanism: Before signing with RSA, the token data is hashed into a shorter string.