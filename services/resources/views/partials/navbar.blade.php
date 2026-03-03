@php
    $isManager = request()->is('manager/*');
    $manv = request()->route('manv');
    $user = \App\Models\Employee::where('MANV', $manv)->first();
    $userName = $user ? $user->HOTEN : ($isManager ? 'Admin' : 'Nhân viên');
@endphp

<nav class="bg-white border-b border-gray-200 px-4 sm:px-6 py-3 flex justify-between items-center shadow-sm sticky top-0 z-50">
    <div class="flex items-center gap-8">
        <a href="/" class="flex items-center gap-2 hover:opacity-80 transition group">
            <div class="bg-blue-600 text-white px-2.5 py-1.5 rounded-lg font-bold text-lg leading-none shadow-sm group-hover:bg-blue-700 transition">
                PS
            </div>
            <span class="font-bold text-xl text-gray-800 tracking-tight hidden sm:block">PayShield</span>
        </a>
        
        <div class="hidden md:flex items-center gap-6">
            @if($isManager)
                <a href="/manager/dashboard" class="flex items-center gap-2 py-2 {{ request()->is('manager/*/dashboard') ? 'text-blue-600 font-bold border-b-2 border-blue-600' : 'text-gray-500 hover:text-blue-600 font-medium transition border-b-2 border-transparent hover:border-blue-600' }}">
                    <i class="fas fa-chart-pie"></i> Tổng quan
                </a>
                <a href="/manager/roster" class="flex items-center gap-2 py-2 {{ request()->is('manager/*/roster') ? 'text-blue-600 font-bold border-b-2 border-blue-600' : 'text-gray-500 hover:text-blue-600 font-medium transition border-b-2 border-transparent hover:border-blue-600' }}">
                    <i class="fas fa-users"></i> Nhân sự
                </a>
                <a href="/manager/payroll" class="flex items-center gap-2 py-2 {{ request()->is('manager/*/payroll') ? 'text-blue-600 font-bold border-b-2 border-blue-600' : 'text-gray-500 hover:text-blue-600 font-medium transition border-b-2 border-transparent hover:border-blue-600' }}">
                    <i class="fas fa-file-invoice-dollar"></i> Quản lý Lương
                </a>
                <a href="/manager/profile" class="flex items-center gap-2 py-2 {{ request()->is('manager/*/profile') ? 'text-blue-600 font-bold border-b-2 border-blue-600' : 'text-gray-500 hover:text-blue-600 font-medium transition border-b-2 border-transparent hover:border-blue-600' }}">
                    <i class="fas fa-user-tie"></i> Hồ sơ
                </a>
            @else
                <a href="/employee/dashboard" class="flex items-center gap-2 py-2 {{ request()->is('employee/*/dashboard') ? 'text-blue-600 font-bold border-b-2 border-blue-600' : 'text-gray-500 hover:text-blue-600 font-medium transition border-b-2 border-transparent hover:border-blue-600' }}">
                    <i class="fas fa-home"></i> Trang chủ
                </a>
                <a href="/employee/salary" class="flex items-center gap-2 py-2 {{ request()->is('employee/*/salary') ? 'text-blue-600 font-bold border-b-2 border-blue-600' : 'text-gray-500 hover:text-blue-600 font-medium transition border-b-2 border-transparent hover:border-blue-600' }}">
                    <i class="fas fa-wallet"></i> Bảng lương
                </a>
                <a href="/employee/profile" class="flex items-center gap-2 py-2 {{ request()->is('employee/*/profile') ? 'text-blue-600 font-bold border-b-2 border-blue-600' : 'text-gray-500 hover:text-blue-600 font-medium transition border-b-2 border-transparent hover:border-blue-600' }}">
                    <i class="fas fa-id-card"></i> Hồ sơ của tôi
                </a>
            @endif
        </div>
    </div>

    <div class="flex items-center gap-4">
        <div class="hidden sm:flex items-center gap-3 border-l border-gray-200 pl-4">
            <div class="text-right">
                <div class="text-sm font-bold text-gray-800">{{ $userName }}</div>
                <div class="text-xs text-gray-500 font-medium">{{ $isManager ? 'Quản Lý Hệ Thống' : 'Nhân Viên' }}</div>
            </div>
            <img src="https://ui-avatars.com/api/?name={{ urlencode($userName) }}&background=eff6ff&color=2563eb&bold=true" alt="Avatar" class="w-9 h-9 rounded-full border border-gray-200 shadow-sm cursor-pointer hover:ring-2 hover:ring-blue-400 transition">
        </div>
        
        <button class="md:hidden text-gray-500 hover:text-blue-600 focus:outline-none p-2" onclick="alert('Tính năng mở menu trên Mobile sẽ được code JS sau nhé!')">
            <i class="fas fa-bars text-xl"></i>
        </button>
    </div>
</nav>