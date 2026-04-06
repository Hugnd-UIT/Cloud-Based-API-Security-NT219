<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;

class SalaryApiController extends Controller
{
    public function getSalary(Request $request)
    {
        // Get email from token to prevent BOLA attack
        $email = $request->get('user_email');

        if (!$email) {
            return response()->json(['message' => 'Error 403: Unauthorized access!'], 403);
        }

        $employee = Employee::with(['salaries' => function($query) {
            $query->orderBy('NAM', 'desc')->orderBy('THANG', 'desc');
        }])->where('EMAIL', $email)->first();

        if (!$employee) {
            return response()->json(['message' => 'Employee data not found'], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $employee->salaries
        ]);
    }
}