<?php

namespace App\Console\Commands;

use App\Models\WalletAddress;
use Illuminate\Console\Command;
use Illuminate\Support\LazyCollection;
use Symfony\Component\Console\Command\Command as CommandAlias;
use Throwable;

class ConsolidateWalletAddresses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wallet-addresses:consolidate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retry all failed consolidation.';

    /**
     * Execute the console command.
     *
     * @return int
     *
     * @throws Throwable
     */
    public function handle(): int
    {
        $this->models()->each(fn (WalletAddress $address) => rescue(fn () => $address->consolidate(true)));

        return CommandAlias::SUCCESS;
    }

    /**
     * @return LazyCollection
     */
    protected function models(): LazyCollection
    {
        return WalletAddress::unconsolidated()->lazyById();
    }
}
