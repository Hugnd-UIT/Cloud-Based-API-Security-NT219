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
        // Lấy token từ request
        $token = $request->bearerToken();

        // Kiểm tra token nếu không có thì quăng lỗi 401
        if (!$token) {
            return response()->json([
                'status' => false,
                'message' => 'Lỗi: Bạn chưa đăng nhập hoặc thiếu Token!'
            ], 401);
        }

        try {
            $public_key = "-----BEGIN PUBLIC KEY-----\n" .
                "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAnyo6T5aZ8aRphvp2Sltk\n" .
                "oWg2Zwdt//hEEDspsE6TEe8b1sPSTwfw5wu80TvqoKGkQuLKR3FStTHc9iCgDQTM\n" .
                "XBetMei9IqJWOngyi3neq9xQhrrdOiLShW1v3JshzrIKlNhjZMprwBwNiW4LqGxW\n" .
                "3g32UMULc7ASe10+QLeH+oq4ke6psHWlA3RDDIgbokewBONg3niqCmpC6Uu9P4mH\n" .
                "kdRSKbZmY2K7buqdTwA90FArifveG2FX4EfDqqFf1wj7bav1+Ar5KQyPa63SUSKz\n" .
                "DlbqjLeQYd6qg/38qfmwE8NkTPGJXloPbm/EpCXwwAzp9UH1pTblIxxCcp3m8JQS\n" .
                "YwIDAQAB\n" .
                "-----END PUBLIC KEY-----";
            
            // Kiểm tra public key nếu không có thì quăng lỗi 401
            if (!$public_key) {
                throw new Exception("Chưa cấu hình public key");
            }

            // Giải mã token
            $decoded_token = JWT::decode($token, new Key($public_key, 'RS256'));
            
            $request->merge([
                // Gán email vào request
                'user_email' => $decoded_token->email ?? null,
                // Gán role vào request
                'user_roles' => $decoded_token->realm_access->roles ?? []
            ]);
            
            // Thả request tiếp tục vào Controller
            return $next($request);

        } catch (\Firebase\JWT\ExpiredException $e) {
            return response()->json(['status' => false, 'message' => 'Token đã hết hạn! Vui lòng đăng nhập lại.'], 401);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => 'Token không hợp lệ hoặc bị giả mạo!'], 401);
        }
    }
}

// RS256 là ký số: kết hợp giữa RSA và SHA-256
// RSA - Mã hóa bất đối xứng:
//      Cơ chế: Dùng private key của keycloak để ký lên token. Dùng public key để kiểm tra chữ ký trên token.
// SHA-256 - Hàm băm:
//      Cơ chế: Trước khi dùng RSA để ký, toàn bộ dữ liệu của token sẽ được băm ra thành một chuỗi ngắn.