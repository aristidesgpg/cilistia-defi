<?php

namespace App\Console\Commands;

use App\CoinAdapters\Exceptions\AdapterException;
use App\CoinAdapters\Resources\Transaction;
use App\Jobs\ProcessWalletTransaction;
use App\Models\TransferRecord;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\LazyCollection;
use Symfony\Component\Console\Command\Command as CommandAlias;

class UpdateTransferRecords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transfer-records:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update transfer records confirmations';

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
     *
     * @throws Exception
     */
    public function handle(): int
    {
        $this->models()->each(fn (TransferRecord $record) => rescue(fn () => $this->process($record)));

        return CommandAlias::SUCCESS;
    }

    /**
     * @return LazyCollection
     */
    protected function models(): LazyCollection
    {
        return TransferRecord::has('walletTransaction')->unconfirmed()->lazyById();
    }

    /**
     * Process TransferRecord
     *
     * @param  TransferRecord  $record
     * @return void
     *
     * @throws AdapterException
     */
    protected function process(TransferRecord $record): void
    {
        $wallet = $record->walletAccount->wallet;
        $hash = $record->walletTransaction->hash;

        try {
            $resource = $wallet->getTransactionResource($hash);
        } catch (AdapterException $exception) {
            throw $exception->setContext(['hash' => $hash]);
        }

        if ($resource instanceof Transaction) {
            ProcessWalletTransaction::dispatch($resource, $wallet);
        }
    }
}
