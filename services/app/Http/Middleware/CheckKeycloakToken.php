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
            // Lấy public key từ .env
            $public_key = env('KEYCLOAK_PUBLIC_KEY');
            
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