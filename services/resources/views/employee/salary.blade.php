@extends('layouts.master')

@section('title', 'Lương của tôi - PayShield')

@push('styles')
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
@endpush

@section('content')
<div class="max-w-7xl mx-auto p-6">
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Lương Của Tôi</h2>
            <p class="text-sm text-gray-500">Xin chào <span id="emp_name" class="font-bold text-blue-600">...</span>! Dưới đây là chi tiết thu nhập của bạn.</p>
        </div>
        
        <div class="flex gap-3 bg-white p-2 rounded-lg shadow-sm border border-gray-200">
            <div class="flex items-center px-2 text-gray-500">
                <i class="fas fa-calendar-alt mr-2"></i> Lọc theo năm:
            </div>
            <select id="filter_year" onchange="window.render_salary()" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5 min-w-[120px]">
                </select>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white p-6 rounded-xl shadow-lg flex justify-between items-center relative overflow-hidden">
            <div class="z-10">
                <p class="text-blue-100 text-sm font-medium mb-1 uppercase tracking-wider">Thực nhận kỳ gần nhất</p>
                <h3 id="latest_salary" class="text-3xl font-bold">0 ₫</h3>
            </div>
            <div class="text-6xl opacity-20 absolute right-4 bottom-[-10px] z-0"><i class="fas fa-wallet"></i></div>
        </div>
        <div class="bg-gradient-to-r from-emerald-500 to-emerald-600 text-white p-6 rounded-xl shadow-lg flex justify-between items-center relative overflow-hidden">
            <div class="z-10">
                <p class="text-emerald-100 text-sm font-medium mb-1 uppercase tracking-wider">Tổng thu nhập năm <span class="label_year font-bold">...</span></p>
                <h3 id="year_total" class="text-3xl font-bold">0 ₫</h3>
            </div>
            <div class="text-6xl opacity-20 absolute right-4 bottom-[-10px] z-0"><i class="fas fa-chart-pie"></i></div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Kỳ Lương</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Ngày Công</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Lương Căn Bản</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-green-600 uppercase tracking-wider">Thưởng</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-red-600 uppercase tracking-wider">Phạt</th>
                        <th class="px-6 py-4 text-right text-sm font-bold text-gray-800 uppercase tracking-wider">Thực nhận</th>
                    </tr>
                </thead>
                <tbody id="my_salary_list" class="bg-white divide-y divide-gray-200">
                     <tr><td colspan="6" class="text-center py-10 text-gray-500 font-medium">Đang tải dữ liệu...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let payrollData = []; 

    const format_money = (amount) => {
        return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount || 0);
    };

    const initYears = () => {
        const yearSelect = document.getElementById('filter_year');
        const years = [...new Set(payrollData.map(p => p.NAM))].sort((a, b) => b - a);
        
        if (years.length === 0) {
            yearSelect.innerHTML = `<option value="${new Date().getFullYear()}">${new Date().getFullYear()}</option>`;
            return;
        }

        yearSelect.innerHTML = years.map(y => `<option value="${y}">${y}</option>`).join('');
    };

    window.render_salary = function() {
        const selectedYear = parseInt(document.getElementById('filter_year').value) || new Date().getFullYear();
        document.querySelectorAll('.label_year').forEach(el => el.innerText = selectedYear);

        const filteredPayrolls = payrollData.filter(p => parseInt(p.NAM) === selectedYear);
        const table_body = document.getElementById('my_salary_list');
        const yearTotal = filteredPayrolls.reduce((sum, p) => sum + parseFloat(p.TIENLUONGTL || 0), 0);
        document.getElementById('year_total').innerText = format_money(yearTotal);
        
        if(payrollData.length > 0) {
            document.getElementById('latest_salary').innerText = format_money(payrollData[0].TIENLUONGTL);
        } else {
            document.getElementById('latest_salary').innerText = '0 ₫';
        }

        table_body.innerHTML = '';
        if(filteredPayrolls.length === 0) {
            table_body.innerHTML = `<tr><td colspan="6" class="text-center py-10 text-gray-500 font-medium"><i class="fas fa-box-open text-gray-300 text-3xl block mb-2"></i>Chưa có dữ liệu lương cho năm ${selectedYear}</td></tr>`;
            return;
        }

        filteredPayrolls.forEach(pay => {
            const row = `
            <tr class="hover:bg-blue-50 transition border-b border-gray-100 group">
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        <div class="bg-blue-100 text-blue-600 p-2 rounded-lg mr-3 group-hover:bg-blue-200 transition">
                            <i class="far fa-calendar-alt"></i>
                        </div>
                        <div>
                            <div class="text-sm font-bold text-gray-900">Tháng ${pay.THANG}/${pay.NAM}</div>
                            <div class="text-xs text-gray-500">Kỳ lương định kỳ</div>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center">
                    <span class="px-3 py-1 bg-gray-100 text-gray-700 font-medium rounded-full text-sm">
                        ${pay.SONGAYCONG} ngày
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right font-medium text-gray-600">
                    ${format_money(pay.TIENLUONGCB)}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right font-semibold text-green-600">
                    +${format_money(pay.TIENTHUONG)}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right font-semibold text-red-500">
                    -${format_money(pay.TIENPHAT)}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right font-bold text-blue-600 text-lg">
                    ${format_money(pay.TIENLUONGTL)}
                </td>
            </tr>`;
            table_body.innerHTML += row;
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        fetch('/api/salary')
            .then(response => response.json())
            .then(res => {
                if(res.status) {
                    document.getElementById('emp_name').innerText = res.data.employee.HOTEN;
                    payrollData = res.data.payrolls;
                    initYears();         
                    render_salary();     
                } else {
                    alert(res.message);
                }
            })
            .catch(err => console.error("Lỗi:", err));
        });
</script>
@endpush