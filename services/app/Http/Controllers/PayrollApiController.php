<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payroll;
use Carbon\Carbon;

class PayrollApiController extends Controller
{
    public function getPayroll(Request $request)
    {
        // Phân quyền xem bảng lương
        $role = $request->get('user_roles');

        if (!in_array('manager', $role)) {
            return response()->json(['message' => 'Lỗi 403: Tính làm hacker hay gì!'], 403);
        }

        $month = $request->query('month', Carbon::now()->month);
        $year  = $request->query('year', Carbon::now()->year);

        $payroll_list = Payroll::with('employee')
                    ->where('THANG', $month)
                    ->where('NAM', $year)
                    ->get();

        $list = $payroll_list->map(function ($p) {
            $status = $p->TIENLUONGTL > 0 ? 'paid' : 'pending';
            return [
                'name'   => $p->employee ? $p->employee->HOTEN : ($p->MANV ?? 'Unknown'),
                'base'   => (float) $p->TIENLUONGCB, 
                'bonus'  => (float) $p->TIENTHUONG,
                'fine'   => (float) $p->TIENPHAT,
                'total'  => (float) $p->TIENLUONGTL,
                'status' => $status,
                'period' => "Tháng $p->THANG/$p->NAM"
            ];
        });

        $summary_data = [
            'total_cost'    => $list->sum('total'),
            'total_paid'    => $list->where('status', 'paid')->sum('total'),
            'total_pending' => $list->where('status', 'pending')->sum('total'),
        ];

        return response()->json([
            'status' => true,
            'data' => $list
        ]);
    }
}