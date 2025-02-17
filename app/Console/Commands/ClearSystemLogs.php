<?php

namespace App\Console\Commands;

use App\Models\SystemLog;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;

class ClearSystemLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system-logs:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear seen system logs';

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
    public function handle(): int
    {
        SystemLog::seen()->delete();

        return CommandAlias::SUCCESS;
    }
}
