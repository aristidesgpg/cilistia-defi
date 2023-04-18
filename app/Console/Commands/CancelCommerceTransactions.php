<?php

namespace App\Console\Commands;

use App\Models\CommerceTransaction;
use Illuminate\Console\Command;
use Illuminate\Support\LazyCollection;
use Symfony\Component\Console\Command\Command as CommandAlias;

class CancelCommerceTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'commerce-transactions:cancel';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cancel pending commerce transactions';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->models()->each(function (CommerceTransaction $transaction) {
            $this->process($transaction);
        });

        return CommandAlias::SUCCESS;
    }

    /**
     * Cancel commerce transaction
     *
     * @param  CommerceTransaction  $transaction
     * @return void
     */
    protected function process(CommerceTransaction $transaction): void
    {
        $transaction->acquireLock(function (CommerceTransaction $transaction) {
            return $transaction->cancel();
        });
    }

    /**
     * @return LazyCollection
     */
    protected function models(): LazyCollection
    {
        return CommerceTransaction::isPendingOverdue()->lazyById();
    }
}
