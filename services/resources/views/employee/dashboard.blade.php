@extends('layouts.master')

@section('title', 'Trang chủ - PayShield')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    
    <div class="bg-gradient-to-r from-blue-700 via-blue-600 to-indigo-700 rounded-2xl p-8 text-white shadow-lg relative overflow-hidden flex flex-col md:flex-row justify-between items-center gap-6">
        <div class="relative z-10 w-full">
            <p class="text-blue-100 font-medium mb-1 tracking-wider uppercase text-sm">Tổng quan không gian làm việc</p>
            <h2 class="text-3xl font-bold mb-2">Xin chào, {{ $employee->HOTEN ?? 'Nhân viên' }}! 👋</h2>
            <p class="text-blue-100 text-base">Chúc bạn một ngày làm việc tràn đầy năng lượng và đạt hiệu suất cao nhất.</p>
        </div>
        
        <div class="relative z-10 bg-white/10 backdrop-blur-sm border border-white/20 rounded-xl p-4 min-w-[200px] text-center shrink-0 hidden md:block">
            <p class="text-blue-100 text-sm font-medium">Hôm nay</p>
            <p class="text-2xl font-bold">{{ \Carbon\Carbon::now()->format('d/m/Y') }}</p>
            <p class="text-sm text-blue-100 mt-1">{{ \Carbon\Carbon::now()->locale('vi')->translatedFormat('l') }}</p>
        </div>

        <i class="fas fa-layer-group absolute right-0 bottom-[-20px] text-8xl text-white opacity-5 transform -rotate-12"></i>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center gap-4">
            <div class="bg-indigo-50 text-indigo-600 w-12 h-12 rounded-full flex items-center justify-center text-xl shrink-0">
                <i class="fas fa-briefcase"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Vị trí công tác</p>
                <h3 class="text-lg font-bold text-gray-800">{{ $employee->CHUCVU ?? 'Chưa cập nhật' }}</h3>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center gap-4">
            <div class="bg-emerald-50 text-emerald-600 w-12 h-12 rounded-full flex items-center justify-center text-xl shrink-0">
                <i class="fas fa-user-check"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Trạng thái</p>
                <div class="flex items-center gap-2 mt-0.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <h3 class="text-lg font-bold text-gray-800">Đang làm việc</h3>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center gap-4">
            <div class="bg-orange-50 text-orange-600 w-12 h-12 rounded-full flex items-center justify-center text-xl shrink-0">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Ngày vào làm</p>
                <h3 class="text-lg font-bold text-gray-800">
                    {{ isset($employee->NGAYVAOLAM) ? \Carbon\Carbon::parse($employee->NGAYVAOLAM)->format('d/m/Y') : '--' }}
                </h3>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="bg-amber-100 text-amber-600 w-8 h-8 rounded-lg flex items-center justify-center">
                    <i class="fas fa-bell"></i>
                </div>
                <h3 class="font-bold text-gray-800 text-lg">Bảng tin & Thông báo</h3>
            </div>
            <span class="bg-blue-50 text-blue-600 text-xs font-bold px-3 py-1.5 rounded-full border border-blue-100">Live Updates</span>
        </div>
        
        <div class="p-6">
            <div id="notification_list" class="space-y-4">
                <div class="flex justify-center items-center py-10 text-gray-400">
                    <i class="fas fa-circle-notch fa-spin text-2xl mr-3"></i>
                    <span class="font-medium">Đang đồng bộ dữ liệu...</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        fetch('/api/dashboard/employee-data')
            .then(response => response.json())
            .then(res => {
                const listContainer = document.getElementById('notification_list');
                listContainer.innerHTML = ''; 

                if(res.status && res.data && res.data.notifications.length > 0) {
                    const notices = res.data.notifications;
                    
                    notices.forEach((notice, index) => {
                        const colors = ['border-blue-500', 'border-emerald-500', 'border-indigo-500', 'border-amber-500'];
                        const borderColor = colors[index % colors.length];

                        const html = `
                            <div class="border-l-4 ${borderColor} bg-white rounded-r-xl p-5 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] border border-gray-100 hover:shadow-md transition duration-300">
                                <div class="flex flex-col sm:flex-row justify-between sm:items-center mb-2 gap-2">
                                    <h4 class="font-bold text-gray-800 text-base flex items-center gap-2">
                                        ${notice.title}
                                    </h4>
                                    <span class="text-xs font-semibold text-gray-500 bg-gray-50 px-2.5 py-1 rounded-md border border-gray-200 whitespace-nowrap">
                                        <i class="far fa-clock mr-1"></i> ${notice.date}
                                    </span>
                                </div>
                                <p class="text-sm text-gray-600 leading-relaxed">${notice.content}</p>
                            </div>
                        `;
                        listContainer.innerHTML += html;
                    });
                } else {
                    listContainer.innerHTML = `
                        <div class="text-center py-12 bg-gray-50 rounded-lg border border-dashed border-gray-200">
                            <i class="far fa-folder-open text-4xl text-gray-300 mb-3 block"></i>
                            <p class="text-gray-500 font-medium">Hiện không có thông báo mới nào.</p>
                        </div>`;
                }
            })
            .catch(err => {
                console.error('Lỗi tải thông báo:', err);
                document.getElementById('notification_list').innerHTML = `
                    <div class="text-center py-8 text-red-500 bg-red-50 rounded-lg border border-red-100">
                        <i class="fas fa-exclamation-circle text-2xl mb-2"></i>
                        <p class="font-medium">Có lỗi xảy ra khi kết nối máy chủ. Vui lòng thử lại sau.</p>
                    </div>`;
            });
        });
</script>
@endpush