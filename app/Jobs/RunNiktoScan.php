<?php

namespace App\Jobs;

use App\Models\ScanResult;
use App\Services\NiktoScanService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class RunNiktoScan implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600;
    public int $tries   = 2;

    public function __construct(public ScanResult $scan) {}

    public function handle(NiktoScanService $service): void
    {
        $this->scan->update(['status' => 'berjalan', 'started_at' => now()]);
        try {
            $result = $service->scan($this->scan->target_url);
            $this->scan->update([
                'status'      => 'selesai',
                'hasil_json'  => $result,
                'finished_at' => now(),
            ]);
        } catch (Throwable $e) {
            $this->scan->update([
                'status'        => 'gagal',
                'error_message' => $e->getMessage(),
                'finished_at'   => now(),
            ]);
        }
    }
}
