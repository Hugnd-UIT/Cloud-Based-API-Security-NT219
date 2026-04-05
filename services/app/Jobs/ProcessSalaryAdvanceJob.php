<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessSalaryAdvanceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $webhookData;

    public function __construct($webhookData)
    {
        $this->webhookData = $webhookData;
    }
    public function handle(): void
    {
        $nhanVien = $this->webhookData['nhan_vien'] ?? 'Unknown';
        $soTien = $this->webhookData['tien_ung'] ?? 0;

        Log::info("[WORKER] Bắt đầu xử lý ứng lương cho: {$nhanVien}");

        sleep(3); 
        
        Log::info("[WORKER] Xử lý xong! Đã giải ngân {$soTien} VNĐ cho {$nhanVien}.");
    }
}