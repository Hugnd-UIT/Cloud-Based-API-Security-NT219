@extends('layouts.master')

@section('title', 'Nhân sự - PayShield')

@push('styles')
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
@endpush

@section('content')
<div class="max-w-7xl mx-auto p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Danh sách nhân viên</h2>
        <button onclick="window.open_modal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium shadow-sm flex items-center gap-2 transition">
            <i class="fas fa-plus"></i> Thêm mới
        </button>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mã NV</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Họ Tên</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SĐT</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Chức Vụ</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Thao tác</th>
                </tr>
            </thead>
            <tbody id="roster_list" class="bg-white divide-y divide-gray-200"></tbody>
        </table>
    </div>
</div>

<div id="employee_modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-lg shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modal_title">Thông tin nhân viên</h3>
            <form id="employee_form" onsubmit="window.save_employee(event)">
                <input type="hidden" id="employee_id"> 
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Mã NV</label>
                        <input type="text" id="MANV" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Họ Tên</label>
                        <input type="text" id="HOTEN" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">SĐT</label>
                        <input type="text" id="SDT" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Chức Vụ</label>
                        <input type="text" id="CHUCVU" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Ngày Sinh</label>
                        <input type="date" id="NGAYSINH" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Giới Tính</label>
                        <select id="GIOITINH" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                            <option value="Nam">Nam</option>
                            <option value="Nữ">Nữ</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">CCCD</label>
                        <input type="text" id="CCCD" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Ngày Vào Làm</label>
                        <input type="date" id="NGAYVAOLAM" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" required>
                    </div>
                </div>

                <div class="items-center px-4 py-3 mt-4 text-right">
                    <button type="button" onclick="window.close_modal()" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg mr-2">Hủy</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Lưu</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    window.load_roster = function() {
        fetch('/api/employees')
            .then(r => r.json())
            .then(data => {
                const table_body = document.getElementById('roster_list');
                table_body.innerHTML = ''; 
                if(data.length === 0) { 
                    table_body.innerHTML = '<tr><td colspan="5" class="text-center py-4">Chưa có dữ liệu</td></tr>'; 
                    return; 
                }

                data.forEach(emp => {
                    const row = `
                    <tr class="hover:bg-gray-50 transition border-b">
                        <td class="px-6 py-4 font-bold text-gray-700">${emp.MANV}</td>
                        <td class="px-6 py-4 font-medium">${emp.HOTEN}</td>
                        <td class="px-6 py-4 text-gray-500">${emp.SDT}</td>
                        <td class="px-6 py-4 text-blue-600">${emp.CHUCVU}</td>
                        <td class="px-6 py-4 text-right space-x-2">
                            <button onclick="window.edit_employee(${emp.id})" class="text-blue-600 hover:text-blue-800 font-medium"><i class="fas fa-edit"></i> Sửa</button>
                            <button onclick="window.delete_employee(${emp.id})" class="text-red-600 hover:text-red-800 font-medium"><i class="fas fa-trash"></i> Xóa</button>
                        </td>
                    </tr>`;
                    table_body.innerHTML += row;
                });
            })
            .catch(err => console.error("Error:", err));
    }

    window.open_modal = function(id = null) {
        document.getElementById('employee_modal').classList.remove('hidden');
        document.getElementById('employee_id').value = id || '';
        document.getElementById('modal_title').innerText = id ? 'Sửa Nhân Viên' : 'Thêm Nhân Viên Mới';
        if(!id) document.getElementById('employee_form').reset();
    }

    window.close_modal = function() {
        document.getElementById('employee_modal').classList.add('hidden');
    }

    window.edit_employee = function(id) {
        fetch(`/api/employees/${id}`)
            .then(r => r.json())
            .then(emp => {
                window.open_modal(id);
                document.getElementById('MANV').value = emp.MANV || '';
                document.getElementById('HOTEN').value = emp.HOTEN || '';
                document.getElementById('SDT').value = emp.SDT || '';
                document.getElementById('CHUCVU').value = emp.CHUCVU || '';
                document.getElementById('NGAYSINH').value = emp.NGAYSINH || '';
                document.getElementById('GIOITINH').value = emp.GIOITINH || 'Nam';
                document.getElementById('CCCD').value = emp.CCCD || '';
                document.getElementById('NGAYVAOLAM').value = emp.NGAYVAOLAM || '';
            })
            .catch(err => alert("Error: " + err));
        }

    window.save_employee = function(e) {
        e.preventDefault();
        const id = document.getElementById('employee_id').value;
        const url = id ? `/api/employees/${id}` : '/api/employees';
        const method = id ? 'PUT' : 'POST';

        const data = {
            MANV: document.getElementById('MANV').value,
            HOTEN: document.getElementById('HOTEN').value,
            SDT: document.getElementById('SDT').value,
            CHUCVU: document.getElementById('CHUCVU').value,
            NGAYSINH: document.getElementById('NGAYSINH').value,
            GIOITINH: document.getElementById('GIOITINH').value,
            CCCD: document.getElementById('CCCD').value,
            NGAYVAOLAM: document.getElementById('NGAYVAOLAM').value,
        };

        fetch(url, {
            method: method,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(r => r.json())
        .then(res => {
            if(res.success) {
                window.close_modal();
                window.load_roster();
                alert('Thành công!');
            } else {
                alert('Lỗi: ' + res.message);
            }
        })
        .catch(err => console.error(err));
    }

    window.delete_employee = function(id) {
        if(confirm('Bạn có chắc muốn xóa?')) {
            fetch(`/api/employees/${id}`, { method: 'DELETE' })
            .then(r => r.json())
            .then(res => {
                if(res.success) window.load_roster();
                else alert("Lỗi xóa!");
            });
        }
    }

    document.addEventListener('DOMContentLoaded', window.load_roster);
</script>
@endpush