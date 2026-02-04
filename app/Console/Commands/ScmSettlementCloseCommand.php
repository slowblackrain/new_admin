<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ScmSettlementCloseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scm:settlement-close {year} {month}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Close SCM Settlement data for a specific month (Update status to complete)';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $year = $this->argument('year');
        $month = str_pad($this->argument('month'), 2, '0', STR_PAD_LEFT);
        $targetDate = "{$year}-{$month}";

        $this->info("Starting SCM Settlement Close for: {$targetDate}");

        // 1. Find target records
        $query = DB::table('fm_account_provider_ats')
            ->where('acc_date', $targetDate)
            ->where('acc_status', '!=', 'complete');

        $count = $query->count();

        if ($count === 0) {
            $this->warn("No records found to close for {$targetDate}.");
            return 0;
        }

        if ($this->confirm("Found {$count} records to close. Do you wish to proceed?", true)) {
            // 2. Update status
            $updated = $query->update([
                'acc_status' => 'complete',
                // 'acc_date' is already targetDate, no need to update unless we want 'closed_at' timestamp
            ]);

            $this->info("Successfully closed {$updated} records.");
            return 0;
        } else {
            $this->info("Operation cancelled.");
            return 1;
        }
    }
}
