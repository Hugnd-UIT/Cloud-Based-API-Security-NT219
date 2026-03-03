<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
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

        $current_time = Carbon::now();
        $current_month = $current_time->month;
        $current_year = $current_time->year;

        $total_employees = Employee::where('TRANGTHAI', 1)->count();

        $total_salary = Payroll::where('THANG', $current_month)->where('NAM', $current_year)->sum('TIENLUONGTL');

        $total_bonus = Payroll::where('THANG', $current_month)->where('NAM', $current_year)->sum('TIENTHUONG');

        $total_fine = Payroll::where('THANG', $current_month)->where('NAM', $current_year)->sum('TIENPHAT');
    
        $chart_data = Payroll::selectRaw('CONCAT(THANG, "/", NAM) as label, SUM(TIENLUONGTL) as value')
                                ->groupBy('NAM', 'THANG')
                                ->orderBy('NAM', 'asc') 
                                ->orderBy('THANG', 'asc')
                                ->limit(6)
                                ->get();

        return response()->json([
            'status' => true,
            'message' => 'Success',
            'data' => [
                'card_stats' => [
                    'total_employees' => $total_employees,
                    'total_salary' => $total_salary,
                    'total_bonus' => $total_bonus,
                    'total_fine' => $total_fine
                ],
                'chart_stats' => $chart_data
            ]
        ]);
    }

    public function getEmployeeDashboard(Request $request) {
        return response()->json([
            'status' => true,
            'data' => [
                'notifications' => [
                    [
                        'title' => 'Quy định mới về bảo mật mật khẩu',
                        'date' => '10/02/2026',
                        'content' => 'Yêu cầu toàn bộ nhân viên đổi mật khẩu định kỳ 3 tháng/lần. Mật khẩu phải bao gồm chữ hoa, chữ thường và ký tự đặc biệt.'
                    ],
                    [
                        'title' => 'Quy định mới về bảo mật mật khẩu',
                        'date' => '10/02/2026',
                        'content' => 'Yêu cầu toàn bộ nhân viên đổi mật khẩu định kỳ 3 tháng/lần. Mật khẩu phải bao gồm chữ hoa, chữ thường và ký tự đặc biệt.'
                    ],
                    [
                        'title' => 'Quy định mới về bảo mật mật khẩu',
                        'date' => '10/02/2026',
                        'content' => 'Yêu cầu toàn bộ nhân viên đổi mật khẩu định kỳ 3 tháng/lần. Mật khẩu phải bao gồm chữ hoa, chữ thường và ký tự đặc biệt.'
                    ],
                    [
                        'title' => 'Quy định mới về bảo mật mật khẩu',
                        'date' => '10/02/2026',
                        'content' => 'Yêu cầu toàn bộ nhân viên đổi mật khẩu định kỳ 3 tháng/lần. Mật khẩu phải bao gồm chữ hoa, chữ thường và ký tự đặc biệt.'
                    ],
                    [
                        'title' => 'Quy định mới về bảo mật mật khẩu',
                        'date' => '10/02/2026',
                        'content' => 'Yêu cầu toàn bộ nhân viên đổi mật khẩu định kỳ 3 tháng/lần. Mật khẩu phải bao gồm chữ hoa, chữ thường và ký tự đặc biệt.'
                    ]
                ]
            ]
        ]);
    }
}