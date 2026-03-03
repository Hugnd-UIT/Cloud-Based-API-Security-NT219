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

        $employee = Employee::whereHas('user', function($query) use ($email) {
            $query->where('EMAIL', $email);
        })->with(['salaries' => function($query) {
            $query->orderBy('NAM', 'desc')->orderBy('THANG', 'desc');
        }])->first();

        if (!$employee) {
            return response()->json([
                'status' => false,
                'message' => 'Không tìm thấy dữ liệu lương của nhân viên này!'
            ], 404);
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