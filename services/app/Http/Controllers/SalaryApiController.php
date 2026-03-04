<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;

class SalaryApiController extends Controller
{
    public function getSalary(Request $request)
    {
        // Lấy email từ token để chống BOLA Attack
        $email = $request->get('user_email');

        if (!$email) {
            return response()->json(['message' => 'Lỗi 403: Tính làm hacker hay gì!'], 403);
        }

        $employee = Employee::with(['salaries' => function($query) {
            $query->orderBy('NAM', 'desc')->orderBy('THANG', 'desc');
        }])->where('EMAIL', $email)->first();

        if (!$employee) {
            return response()->json(['message' => 'Không tìm thấy dữ liệu nhân viên'], 404);
        }

        return response()->json([
            'status' => true,
            'data' => [
                'employee' => $employee,
                'payrolls' => $employee->salaries
            ]
        ]);
    }
}