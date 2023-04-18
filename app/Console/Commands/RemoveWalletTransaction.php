<?php

namespace App\Console\Commands;

use App\Models\TransferRecord;
use App\Models\WalletTransaction;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Command\Command as CommandAlias;

class RemoveWalletTransaction extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wallets:remove-transaction';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove wallet transaction that no longer exist on the blockchain.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->warn('This will remove the transaction and its corresponding transfer record for users.');

        if ($hash = $this->ask('Enter the transaction hash')) {
            $this->deleteTransaction($hash);
        }

        return CommandAlias::SUCCESS;
    }

    /**
     * Delete transaction
     *
     * @param  string  $hash
     * @return void
     */
    protected function deleteTransaction(string $hash): void
    {
        DB::transaction(function () use ($hash) {
            $transaction = WalletTransaction::has('transferRecords')
                ->whereDoesntHave('transferRecords', function (Builder $query) {
                    $query->confirmed()->orWhere('confirmations', '>', 0);
                })->whereConfirmations(0)->whereHash($hash)->firstOrFail();

            $transaction->transferRecords->each(function (TransferRecord $transferRecord) {
                $this->deleteTransferRecord($transferRecord);
            });

            if ($transaction->delete()) {
                $this->info("Removed transaction: $hash");
            }
        });
    }

    /**
     * Delete transfer record
     *
     * @param  TransferRecord  $transferRecord
     * @return void
     */
    protected function deleteTransferRecord(TransferRecord $transferRecord): void
    {
        $transferRecord->walletAccount->acquireLockOrThrow(function () use ($transferRecord) {
            if ($transferRecord->confirmed || $transferRecord->confirmations > 0) {
                throw new Exception('You cannot delete a confirmed transaction.');
            } elseif ($transferRecord->delete()) {
                $this->info("Removed transfer record: $transferRecord->id");
            }
        });
    }
}
