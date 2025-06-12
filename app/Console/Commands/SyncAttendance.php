<?php

namespace App\Console\Commands;

use App\Services\ZKTecoService;
use Illuminate\Console\Command;

class SyncAttendance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync attendance data from ZKTeco device';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting attendance sync...');

        $service = new ZKTecoService();
        $success = $service->syncAttendance();

        if ($success) {
            $this->info('Attendance sync completed successfully!');
        } else {
            $this->error('Failed to sync attendance data.');
        }
    }
}
