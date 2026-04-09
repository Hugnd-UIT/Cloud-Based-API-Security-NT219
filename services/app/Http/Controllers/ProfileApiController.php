<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;

class ProfileApiController extends Controller
{
    public function getProfile(Request $request)
    {
        // Get email from token to prevent BOLA attack
        $email = $request->get('user_email');

        if (!$email) {
            return response()->json(['message' => 'Error 403: Unauthorized access!'], 403);
        }

        $employee = Employee::where('EMAIL', $email)->first();

        if (!$employee) {
            return response()->json(['message' => 'Employee data not found'], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $employee
        ]);
    }
}