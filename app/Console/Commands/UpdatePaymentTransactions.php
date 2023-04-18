<?php

namespace App\Console\Commands;

use App\Jobs\ProcessPaymentTransaction;
use App\Models\PaymentTransaction;
use Illuminate\Console\Command;
use Illuminate\Support\LazyCollection;
use Symfony\Component\Console\Command\Command as CommandAlias;

class UpdatePaymentTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payment-transactions:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch pending payment transaction for processing';

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
        $this->models()->each(function (PaymentTransaction $transaction) {
            ProcessPaymentTransaction::dispatch($transaction);
            $this->info("Dispatched: {$transaction->id}");
        });

        return CommandAlias::SUCCESS;
    }

    /**
     * @return LazyCollection
     */
    protected function models(): LazyCollection
    {
        return PaymentTransaction::pending()->lazyById();
    }
}
