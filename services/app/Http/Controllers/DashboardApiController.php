<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Payroll;
use Carbon\Carbon;

class DashboardApiController extends Controller
{
    public function getManagerDashboard(Request $request) {
        // Authorization check for manager dashboard
        $role = $request->get('user_roles');

        if (!in_array('manager', $role)) {
            return response()->json(['message' => 'Error 403: Unauthorized access!'], 403);
        }

        $month = Carbon::now()->month;
        $year = Carbon::now()->year;

        $stats = Payroll::where('THANG', $month)->where('NAM', $year)
            ->selectRaw('COALESCE(SUM(TIENLUONGTL), 0) as salary, COALESCE(SUM(TIENTHUONG), 0) as bonus, COALESCE(SUM(TIENPHAT), 0) as fine')
            ->first();

        return response()->json([
            'status' => true,
            'data' => [
                'total_employees' => Employee::where('TRANGTHAI', 1)->count(),
                'total_salary'    => (float) $stats->salary,
                'total_bonus'     => (float) $stats->bonus,
                'total_fine'      => (float) $stats->fine
            ]
        ]);
    }

    public function getEmployeeDashboard(Request $request) {
        return response()->json([
            'status' => true,
            'data' => [
                ['title' => 'Notification 1', 'date' => '01/01/2026', 'content' => 'Content 1'],
                ['title' => 'Notification 2', 'date' => '02/01/2026', 'content' => 'Content 2'],
                ['title' => 'Notification 3', 'date' => '03/01/2026', 'content' => 'Content 3'],
                ['title' => 'Notification 4', 'date' => '04/01/2026', 'content' => 'Content 4']
            ]
        ]);
    }
}