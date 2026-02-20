<?php

namespace App\Jobs;

use App\Models\Batch;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendExpiryAlert implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Batch $batch
    ) {}

    public function handle(NotificationService $notificationService): void
    {
        $notificationService->sendExpiryAlert($this->batch);
    }
}
