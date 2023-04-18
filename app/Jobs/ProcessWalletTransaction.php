<?php

namespace App\Jobs;

use App\CoinAdapters\Resources\Transaction;
use App\Helpers\CoinFormatter;
use App\Models\Earning;
use App\Models\TransferRecord;
use App\Models\Wallet;
use App\Models\WalletAccount;
use App\Models\WalletAddress;
use App\Models\WalletTransaction;
use Brick\Math\BigDecimal;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;
use UnexpectedValueException;

class ProcessWalletTransaction implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Transaction resource
     *
     * @var Transaction
     */
    protected Transaction $resource;

    /**
     * Related wallet
     *
     * @var Wallet
     */
    protected Wallet $wallet;

    /**
     * Wallet Transaction object
     *
     * @var WalletTransaction
     */
    protected WalletTransaction $transaction;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public int $tries = 1;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public int $timeout = 120;

    /**
     * Create a new job instance.
     *
     * @param  Transaction  $resource
     * @param  Wallet  $wallet
     */
    public function __construct(Transaction $resource, Wallet $wallet)
    {
        $this->resource = $resource;
        $this->wallet = $wallet;
    }

    /**
     * Execute the job.
     *
     * @return void
     *
     * @throws Throwable
     */
    public function handle(): void
    {
        match ($this->resource->getType()) {
            'receive' => $this->handleIncomingTransaction(),
            'send' => $this->handleOutgoingTransaction()
        };
    }

    /**
     * Handle outgoing transaction
     *
     * @return void
     *
     * @throws Exception
     */
    protected function handleOutgoingTransaction(): void
    {
        $query = $this->wallet
            ->transactions()->where('hash', $this->resource->getHash())
            ->where('type', $this->resource->getType());

        if ($transaction = $query->first()) {
            $transaction->transferRecords->each(function (TransferRecord $record) {
                $record->update(['confirmations' => $this->resource->getConfirmations()]);
            });

            $transaction->update([
                'confirmations' => $this->resource->getConfirmations(),
                'resource' => $this->resource,
            ]);
        }
    }

    /**
     * Handle Incoming transaction
     *
     * @throws Exception
     * @throws Throwable
     */
    protected function handleIncomingTransaction()
    {
        $output = $this->resource->getReceiver();

        if (is_array($output)) {
            collect($output)->each(function ($output) {
                $query = $this->wallet->addresses()->where('address', $output['address']);

                if ($address = $query->first()) {
                    $this->saveTransferRecord($address, (string) $output['value']);
                }
            });
        }

        if (is_string($output)) {
            $query = $this->wallet->addresses()->where('address', $output);

            if ($address = $query->first()) {
                $this->saveTransferRecord($address);
            }
        }
    }

    /**
     * Save transfer record
     *
     * @param  WalletAddress  $address
     * @param  string|null  $value
     * @return void
     *
     * @throws Throwable
     */
    protected function saveTransferRecord(WalletAddress $address, string $value = null): void
    {
        $transferRecord = $address->walletAccount->acquireLockOrThrow(function (WalletAccount $account) use ($address, $value) {
            $transaction = $this->getTransaction();
            $valueReceived = $address->wallet->castCoin($this->abs($value ?: $transaction->value));

            $transferRecord = $address->transferRecords()->updateOrCreate([
                'wallet_account_id' => $address->wallet_account_id,
                'wallet_transaction_id' => $transaction->id,
            ], [
                'type' => $transaction->type,
                'value' => $valueReceived,
                'dollar_price' => $this->wallet->coin->getDollarPrice(),
                'description' => $account->getIncomingDescription($address->address),
                'required_confirmations' => $this->wallet->min_conf,
                'confirmations' => $transaction->confirmations,
                'external' => true,
            ]);

            if ($transferRecord->wasRecentlyCreated) {
                DB::transaction(function () use ($address, $valueReceived) {
                    $this->chargeCommerceFee($address, $valueReceived);
                });
            }

            return $transferRecord;
        });

        if (!$transferRecord instanceof TransferRecord) {
            throw new UnexpectedValueException();
        }

        if ($transferRecord->wasRecentlyCreated && $transferRecord->walletAddress) {
            rescue(fn () => $transferRecord->walletAddress->consolidate(true));
        }
    }

    /**
     * Charge commerce fee
     * PS: This needs to be called only once and when the transfer record was created
     * PS: This needs to be called under walletAccount lock to avoid balance conflicts.
     *
     * @param  WalletAddress  $address
     * @param  CoinFormatter  $valueReceived
     * @return void
     */
    protected function chargeCommerceFee(WalletAddress $address, CoinFormatter $valueReceived): void
    {
        if ($commerceTransaction = $address->commerceTransactions()->first()) {
            $commerceFee = $address->wallet->getCommerceFee($valueReceived);
            $operatorAccount = $commerceTransaction->getOperatorWalletAccount();

            if ($operatorAccount?->isNot($address->walletAccount) && $commerceFee->isPositive()) {
                $commerceDescription = $commerceTransaction->getFeeDescription();
                $revenueTransaction = $operatorAccount->credit($commerceFee, $commerceDescription);
                $address->walletAccount->debit($commerceFee, $commerceDescription);
                Earning::saveWalletTransaction($revenueTransaction);
            }
        }
    }

    /**
     * Save wallet transaction
     *
     * @return WalletTransaction
     *
     * @throws Exception
     */
    protected function getTransaction(): WalletTransaction
    {
        if (!isset($this->transaction)) {
            $this->transaction = $this->wallet->transactions()
                ->updateOrCreate([
                    'hash' => $this->resource->getHash(),
                    'type' => $this->resource->getType(),
                ], [
                    'date' => $this->resource->getDate(),
                    'confirmations' => $this->resource->getConfirmations(),
                    'value' => $this->resource->getValue(),
                    'resource' => $this->resource,
                ]);
        }

        return $this->transaction;
    }

    /**
     * Get absolute value
     *
     * @param $value
     * @return string
     */
    protected function abs($value): string
    {
        return (string) BigDecimal::of($value)->abs();
    }

    /**
     * The unique ID of the job.
     *
     * @return string
     */
    public function uniqueId(): string
    {
        return $this->resource->lockKey();
    }

    /**
     * Get the cache driver for the unique job lock.
     *
     * @return Repository
     */
    public function uniqueVia(): Repository
    {
        return Cache::driver('redis');
    }
}
