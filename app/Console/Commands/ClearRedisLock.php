<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\Console\Command\Command as CommandAlias;

class ClearRedisLock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lock:clear-redis';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear redis lock database';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        if (!$this->laravel->isDownForMaintenance()) {
            $this->error('This requires maintenance mode!');

            return CommandAlias::FAILURE;
        }

        $this->warn('It is typically unsafe to clear lock database, this is going to release all acquired locks and could lead to race condition.');

        if ($this->confirm('Do you still wish to proceed?')) {
            Redis::connection(config('cache.stores.redis.lock_connection') ?: 'lock')->flushdb();

            $this->info('Redis lock cleared successfully.');
        }

        return CommandAlias::SUCCESS;
    }
}
