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
        // Phân quyền xem dashboard quản lý
        $role = $request->get('user_roles');

        if (!in_array('manager', $role)) {
            return response()->json(['message' => 'Lỗi 403: Tính làm hacker hay gì!'], 403);
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
                ['title' => 'Thông báo 1', 'date' => '01/01/2026', 'content' => 'Nội dung 1'],
                ['title' => 'Thông báo 2', 'date' => '02/01/2026', 'content' => 'Nội dung 2'],
                ['title' => 'Thông báo 3', 'date' => '03/01/2026', 'content' => 'Nội dung 3'],
                ['title' => 'Thông báo 4', 'date' => '04/01/2026', 'content' => 'Nội dung 4']
            ]
        ]);
    }
}