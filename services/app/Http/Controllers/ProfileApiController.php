<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;

class ProfileApiController extends Controller
{

    public function getProfile(Request $request)
    {
        // Lấy email từ token để chống BOLA Attack
        $email = $request->get('user_email');

        if (!$email) {
            return response()->json(['message' => 'Lỗi 403: Tính làm hacker hay gì!'], 403);
        }

        $employee = Employee::where('EMAIL', $email)->first();

        if (!$employee) {
            return response()->json(['message' => 'Không tìm thấy dữ liệu nhân viên'], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $employee
        ]);
    }
}