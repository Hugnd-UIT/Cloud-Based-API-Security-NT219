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
        $employee = $this->webhookData['nhan_vien'] ?? 'Unknown';
        $amount = $this->webhookData['tien_ung'] ?? 0;

        Log::info("[INFO] Starting salary advance processing for: {$employee}");

        sleep(3); 
        
        Log::info("[INFO] Completed! Disbursed {$amount} VND to {$employee}.");
    }
}