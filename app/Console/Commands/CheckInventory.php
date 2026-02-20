<?php

namespace App\Console\Commands;

use App\Jobs\CheckInventoryAlerts;
use Illuminate\Console\Command;

class CheckInventory extends Command
{
    protected $signature = 'inventory:check-alerts';
    protected $description = 'Check inventory for low stock and expiring batches';

    public function handle()
    {
        $this->info('Checking inventory alerts...');
        
        CheckInventoryAlerts::dispatch();
        
        $this->info('Inventory alerts have been queued for processing.');
        
        return Command::SUCCESS;
    }
}
