@extends('layouts.master')

@section('title', 'Bảng lương - PayShield')

@push('styles')
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
@endpush

@section('content')
<div class="max-w-7xl mx-auto p-6">
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Quản Lý Lương</h2>
            <p class="text-sm text-gray-500">Xem và kiểm soát chi phí nhân sự theo kỳ</p>
        </div>
        
        <div class="flex gap-3 bg-white p-2 rounded-lg shadow-sm border border-gray-200">
            <select id="filter_month" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5">
                @for($i = 1; $i <= 12; $i++)
                    <option value="{{ $i }}">Tháng {{ $i }}</option>
                @endfor
            </select>

            <select id="filter_year" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5">
                <option value="2025">2025</option>
                <option value="2026" selected>2026</option>
            </select>

            <button onclick="window.load_payroll()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition">
                <i class="fas fa-filter mr-2"></i> Lọc
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-blue-600 text-white p-5 rounded-xl shadow-lg">
            <p class="text-blue-100 text-sm">Tổng chi (Tháng <span class="label_month">...</span>)</p>
            <h3 id="sum_total" class="text-3xl font-bold mt-1">...</h3>
        </div>
        <div class="bg-emerald-500 text-white p-5 rounded-xl shadow-lg">
            <p class="text-emerald-100 text-sm">Đã thanh toán</p>
            <h3 id="sum_paid" class="text-3xl font-bold mt-1">...</h3>
        </div>
        <div class="bg-rose-500 text-white p-5 rounded-xl shadow-lg">
            <p class="text-rose-100 text-sm">Chờ thanh toán</p>
            <h3 id="sum_pending" class="text-3xl font-bold mt-1">...</h3>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nhân viên</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Lương cứng</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-green-600 uppercase tracking-wider">Thưởng</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-red-600 uppercase tracking-wider">Phạt</th>
                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">Thực nhận</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Trạng thái</th>
                </tr>
            </thead>
            <tbody id="payroll_list" class="bg-white divide-y divide-gray-200">
                 <tr><td colspan="6" class="text-center py-4">Đang tải dữ liệu...</td></tr>
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const format_money = (amount) => {
        return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(amount);
    };

    window.load_payroll = function() {
        const m = document.getElementById('filter_month').value;
        const y = document.getElementById('filter_year').value;

        document.querySelector('.label_month').innerText = `${m}/${y}`;
        document.getElementById('payroll_list').innerHTML = '<tr><td colspan="6" class="text-center py-10 text-gray-500">Đang tải dữ liệu...</td></tr>';

        fetch(`/api/payrolls?month=${m}&year=${y}`)
            .then(response => response.json())
            .then(res => {
                document.getElementById('sum_total').innerText = format_money(res.summary.total_cost);
                document.getElementById('sum_paid').innerText = format_money(res.summary.total_paid);
                document.getElementById('sum_pending').innerText = format_money(res.summary.total_pending);

                const table_body = document.getElementById('payroll_list');
                table_body.innerHTML = '';
                
                if(res.list.length === 0) {
                    table_body.innerHTML = `<tr><td colspan="6" class="text-center py-10 text-gray-400">Không có dữ liệu lương cho Tháng ${m}/${y}</td></tr>`;
                    return;
                }

                res.list.forEach(pay => {
                    let status_badge = pay.status == 'paid' 
                        ? `<span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Đã chuyển</span>`
                        : `<span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Chờ duyệt</span>`;

                    const row = `
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">${pay.name}</div>
                            <div class="text-xs text-gray-500">${pay.period}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-gray-600">${format_money(pay.base)}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-green-600 font-medium">+${format_money(pay.bonus)}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-red-600 font-medium">-${format_money(pay.fine)}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right font-bold text-blue-600 text-lg">${format_money(pay.total)}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">${status_badge}</td>
                    </tr>`;
                    table_body.innerHTML += row;
                });
            })
            .catch(err => console.error("Error:", err));
        }

    document.addEventListener('DOMContentLoaded', function() {
        const today = new Date();
        document.getElementById('filter_month').value = today.getMonth() + 1;
        document.getElementById('filter_year').value = today.getFullYear();
        window.load_payroll();
    });
</script>
@endpush