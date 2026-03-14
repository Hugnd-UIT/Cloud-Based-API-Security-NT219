<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;

class RosterApiController extends Controller
{
    public function getRoster(Request $request)
    {
        // Phân quyền xem danh sách nhân viên
        $role = $request->get('user_roles');

        if (!in_array('manager', $role)) {
            return response()->json(['message' => 'Lỗi 403: Tính làm hacker hay gì!'], 403);
        }

        return response()->json(Employee::orderBy('created_at', 'desc')->get());
    }

    public function getEmployee(Request $request, $id)
    {
        // Phân quyền xem chi tiết nhân viên
        $role = $request->get('user_roles');

        if (!in_array('manager', $role)) {
            return response()->json(['message' => 'Lỗi 403: Tính làm hacker hay gì!'], 403);
        }

        return response()->json(Employee::find($id));
    }

    public function storeEmployee(Request $request)
    {
        // Phân quyền thêm nhân viên
        $role = $request->get('user_roles');

        if (!in_array('manager', $role)) {
            return response()->json(['message' => 'Lỗi 403: Tính làm hacker hay gì!'], 403);
        }

        try {
            $input_data = $request->all();
            if(!isset($input_data['TRANGTHAI'])) $input_data['TRANGTHAI'] = 1; 
            
            Employee::create($input_data);
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function updateEmployee(Request $request, $id)
    {
        // Phân quyền sửa nhân viên
        $role = $request->get('user_roles');

        if (!in_array('manager', $role)) {
            return response()->json(['message' => 'Lỗi 403: Tính làm hacker hay gì!'], 403);
        }
        try {
            $employee = Employee::find($id);
            if($employee) {
                $employee->update($request->all());
                return response()->json(['success' => true]);
            }
            return response()->json(['success' => false, 'message' => 'Not found']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function destroyEmployee(Request $request, $id)
    {
        // Phân quyền xóa nhân viên
        $role = $request->get('user_roles');

        if (!in_array('manager', $role)) {
            return response()->json(['message' => 'Lỗi 403: Tính làm hacker hay gì!'], 403);
        }

        $employee = Employee::find($id);
        if ($employee) {
            \App\Models\Payroll::where('MANV', $id)->delete();
            $employee->delete();
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false]);
    }
}