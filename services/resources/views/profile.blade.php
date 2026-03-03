@extends('layouts.master')

@section('title', 'Hồ sơ nhân sự | PayShield Pro')

@section('content')
    <div class="flex justify-end mb-6">
        <a id="link_salary" href="#" class="group flex items-center gap-2 bg-slate-900 text-white px-5 py-2.5 rounded-full text-sm font-bold hover:bg-indigo-600 transition-all duration-300 shadow-md">
            <span>Tra cứu thu nhập</span>
            <i class="fa-solid fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
        </a>
    </div>

    <div class="relative mb-10">
        <div class="h-48 w-full bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 rounded-[2rem] shadow-2xl opacity-90"></div>
        
        <div class="absolute -bottom-8 left-8 flex flex-col md:flex-row items-end gap-6 px-4">
            <div class="h-32 w-32 bg-white rounded-[2.5rem] p-2 shadow-xl relative group">
                <div id="emp_avatar" class="h-full w-full bg-slate-100 rounded-[2rem] flex items-center justify-center text-4xl font-black text-indigo-600 overflow-hidden">
                    ?
                </div>
                <div class="absolute bottom-1 right-1 h-8 w-8 bg-emerald-500 border-4 border-white rounded-full flex items-center justify-center" title="Đang làm việc">
                    <i class="fa-solid fa-check text-[10px] text-white"></i>
                </div>
            </div>
            <div class="mb-2">
                <h1 id="emp_name" class="text-3xl font-extrabold text-slate-900 leading-none mb-2">Đang tải...</h1>
                <div class="flex flex-wrap gap-2 items-center text-slate-500 font-medium">
                    <span id="emp_role_badge" class="bg-indigo-50 text-indigo-700 px-3 py-1 rounded-full text-xs font-bold">...</span>
                    <span class="text-slate-300">•</span>
                    <span class="text-sm">Mã nhân viên: <b id="emp_code" class="text-slate-700">...</b></span>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mt-16">
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100">
                <h3 class="text-sm font-bold uppercase tracking-[0.2em] text-slate-400 mb-6">Liên hệ nhanh</h3>
                <div class="space-y-5">
                    <div class="flex items-center gap-4 group">
                        <div class="h-10 w-10 bg-slate-50 rounded-2xl flex items-center justify-center text-slate-400 group-hover:bg-indigo-50 group-hover:text-indigo-600 transition-colors">
                            <i class="fa-solid fa-phone"></i>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase">Số điện thoại</p>
                            <p id="emp_phone" class="font-bold text-slate-700">...</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 group">
                        <div class="h-10 w-10 bg-slate-50 rounded-2xl flex items-center justify-center text-slate-400 group-hover:bg-indigo-50 group-hover:text-indigo-600 transition-colors">
                            <i class="fa-solid fa-envelope"></i>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase">Email nội bộ</p>
                            <p id="emp_email" class="font-bold text-slate-700">...</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-emerald-50 p-6 rounded-[2rem] border border-emerald-100 relative overflow-hidden group">
                <i class="fa-solid fa-shield-halved absolute -right-4 -bottom-4 text-8xl text-emerald-100/50 group-hover:rotate-12 transition-transform"></i>
                <h3 class="text-emerald-700 font-bold mb-1">Trạng thái hồ sơ</h3>
                <p class="text-emerald-600 text-sm font-medium mb-4">Hồ sơ đã được xác minh trên hệ thống PayShield.</p>
                <div class="inline-flex items-center gap-2 bg-white px-3 py-1 rounded-full text-[10px] font-extrabold text-emerald-600 shadow-sm">
                    <span class="h-2 w-2 bg-emerald-500 rounded-full animate-pulse"></span>
                    <span id="emp_status_text">...</span>
                </div>
            </div>
        </div>

        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white p-8 rounded-[2rem] shadow-sm border border-slate-100 relative overflow-hidden">
                <div class="flex justify-between items-center mb-10">
                    <h3 class="text-xl font-extrabold text-slate-800">Thông tin chi tiết</h3>
                    <div class="h-px flex-grow mx-6 bg-slate-100"></div>
                    <i class="fa-solid fa-user-gear text-slate-200 text-2xl"></i>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-y-10 gap-x-12">
                    <div class="relative">
                        <p class="text-[10px] font-black text-slate-300 uppercase tracking-widest mb-2">Họ và tên</p>
                        <p id="detail_name" class="text-lg font-bold text-slate-700">...</p>
                    </div>
                    <div class="relative">
                        <p class="text-[10px] font-black text-slate-300 uppercase tracking-widest mb-2">Ngày sinh</p>
                        <p id="detail_dob" class="text-lg font-bold text-slate-700">...</p>
                    </div>
                    <div class="relative">
                        <p class="text-[10px] font-black text-slate-300 uppercase tracking-widest mb-2">Số định danh (CCCD)</p>
                        <p id="detail_cccd" class="text-lg font-bold text-slate-700 tracking-wider">...</p>
                    </div>
                    <div class="relative">
                        <p class="text-[10px] font-black text-slate-300 uppercase tracking-widest mb-2">Giới tính</p>
                        <p id="detail_gender" class="text-lg font-bold text-slate-700 flex items-center gap-2">...</p>
                    </div>
                    <div class="relative">
                        <p class="text-[10px] font-black text-slate-300 uppercase tracking-widest mb-2">Ngày vào làm</p>
                        <p id="detail_join_date" class="text-lg font-bold text-slate-700">...</p>
                    </div>
                    <div class="relative">
                        <p class="text-[10px] font-black text-slate-300 uppercase tracking-widest mb-2">Vị trí công tác</p>
                        <p id="detail_role" class="text-lg font-bold text-indigo-600">...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if(linkSalary) linkSalary.href = '/employee/salary';
        fetch('/api/profile')
            .then(response => response.json())
            .then(res => {
                if(res.status) {
                    const emp = res.data;
                    document.getElementById('emp_avatar').innerText = emp.HOTEN.charAt(0);
                    document.getElementById('emp_name').innerText = emp.HOTEN;
                    document.getElementById('detail_name').innerText = emp.HOTEN;
                    document.getElementById('emp_role_badge').innerText = emp.CHUCVU;
                    document.getElementById('detail_role').innerText = emp.CHUCVU;
                    document.getElementById('emp_code').innerText = emp.MANV;
                    document.getElementById('emp_phone').innerText = emp.SDT || 'Chưa cập nhật';
                    document.getElementById('emp_email').innerText = emp.MANV.toLowerCase() + '@payshield.com';
                    document.getElementById('detail_cccd').innerText = emp.CCCD;
                    
                    document.getElementById('emp_status_text').innerText = emp.TRANGTHAI == 1 ? 'Đang làm việc' : 'Đã nghỉ việc';

                    const genderIcon = emp.GIOITINH == 'Nam' ? '<i class="fa-solid fa-mars text-blue-500"></i>' : '<i class="fa-solid fa-venus text-rose-500"></i>';
                    document.getElementById('detail_gender').innerHTML = genderIcon + ' ' + emp.GIOITINH;

                    const formatDate = (dateStr) => {
                        if(!dateStr) return '...';
                        const [year, month, day] = dateStr.split('-');
                        return `${day}/${month}/${year}`;
                    };
                    document.getElementById('detail_dob').innerText = formatDate(emp.NGAYSINH);
                    document.getElementById('detail_join_date').innerText = formatDate(emp.NGAYVAOLAM);
                } else {
                    alert(res.message);
                }
            })
            .catch(err => console.error("Lỗi:", err));
        });
</script>
@endpush