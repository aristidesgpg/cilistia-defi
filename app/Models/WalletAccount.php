<?php

namespace App\Models;

use Akaunting\Money\Money;
use App\Events\WalletAccountSaved;
use App\Exceptions\LockException;
use App\Exceptions\TransferException;
use App\Helpers\CoinFormatter;
use App\Models\Support\Lock;
use Brick\Math\BigDecimal;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use UnexpectedValueException;

class WalletAccount extends Model
{
    use HasFactory, Lock;

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = ['wallet', 'user'];

    /**
     * The event map for the model.
     *
     * @var array
     */
    protected $dispatchesEvents = [
        'saved' => WalletAccountSaved::class,
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'price',
        'formatted_price',
        'coin',
        'min_transferable_price',
        'formatted_min_transferable_price',
        'max_transferable_price',
        'formatted_max_transferable_price',
        'balance_on_trade_price',
        'formatted_balance_on_trade_price',
        'balance_price',
        'formatted_balance_price',
        'available_price',
        'formatted_available_price',
        'total_received_price',
        'formatted_total_received_price',
        'total_sent_price',
        'formatted_total_sent_price',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];

    /**
     * Get min price margin
     *
     * @return Attribute
     */
    protected function minPriceMargin(): Attribute
    {
        return Attribute::get(function () {
            $multiplier = (100 - min(settings()->get('price_margin'), 99)) / 100;

            return $this->getPriceAsMoney()->multiply($multiplier);
        });
    }

    /**
     * Get max price margin
     *
     * @return Attribute
     */
    protected function maxPriceMargin(): Attribute
    {
        return Attribute::get(function () {
            $multiplier = (100 + min(settings()->get('price_margin'), 99)) / 100;

            return $this->getPriceAsMoney()->multiply($multiplier);
        });
    }

    /**
     * Get minimum transferable
     *
     * @return Attribute
     */
    protected function minTransferable(): Attribute
    {
        return Attribute::get(function () {
            $minTransferable = $this->wallet->coin->adapter->getMinimumTransferable();

            return $this->wallet->castCoin($minTransferable);
        });
    }

    /**
     * @return float|string
     */
    protected function getMinTransferablePriceAttribute(): float|string
    {
        return $this->min_transferable->getPrice($this->user->currency);
    }

    /**
     * @return string
     */
    protected function getFormattedMinTransferablePriceAttribute(): string
    {
        return $this->min_transferable->getFormattedPrice($this->user->currency);
    }

    /**
     * Get max transferable
     *
     * @return Attribute
     */
    protected function maxTransferable(): Attribute
    {
        return Attribute::get(function () {
            $maxTransferable = $this->wallet->coin->adapter->getMaximumTransferable();

            return $this->wallet->castCoin($maxTransferable);
        });
    }

    /**
     * @return float|string
     */
    protected function getMaxTransferablePriceAttribute(): float|string
    {
        return $this->max_transferable->getPrice($this->user->currency);
    }

    /**
     * @return string
     */
    protected function getFormattedMaxTransferablePriceAttribute(): string
    {
        return $this->max_transferable->getFormattedPrice($this->user->currency);
    }

    /**
     * @return CoinFormatter
     */
    public function getAvailableObject(): CoinFormatter
    {
        return $this->available;
    }

    /**
     * Get available balance
     *
     * @return Attribute
     */
    protected function available(): Attribute
    {
        return Attribute::get(function (): CoinFormatter {
            return $this->getBalanceObject()->subtract($this->getBalanceOnTradeObject());
        });
    }

    /**
     * @return float|string
     */
    protected function getAvailablePriceAttribute(): float|string
    {
        return $this->getAvailableObject()->getPrice($this->user->currency);
    }

    /**
     * @return string
     */
    protected function getFormattedAvailablePriceAttribute(): string
    {
        return $this->getAvailableObject()->getFormattedPrice($this->user->currency);
    }

    /**
     * @return CoinFormatter
     */
    public function getBalanceOnTradeObject(): CoinFormatter
    {
        return $this->balance_on_trade;
    }

    /**
     * Get balance locked on trade
     *
     * @return Attribute
     */
    protected function balanceOnTrade(): Attribute
    {
        return Attribute::get(function (): CoinFormatter {
            $exchangeTrade = $this->exchangeTrades()
                ->whereType('sell')->whereStatus('pending')
                ->sum('wallet_value');

            $peerTrade = $this->sellPeerTrades()
                ->inProgress()->sum('total_value');

            $balanceOnTrade = (string) BigDecimal::of($exchangeTrade)->plus($peerTrade);

            return $this->wallet->castCoin($balanceOnTrade);
        });
    }

    /**
     * @return float|string
     */
    protected function getBalanceOnTradePriceAttribute(): float|string
    {
        return $this->getBalanceOnTradeObject()->getPrice($this->user->currency);
    }

    /**
     * @return string
     */
    protected function getFormattedBalanceOnTradePriceAttribute(): string
    {
        return $this->getBalanceOnTradeObject()->getFormattedPrice($this->user->currency);
    }

    /**
     * @return CoinFormatter
     */
    public function getBalanceObject(): CoinFormatter
    {
        return $this->balance;
    }

    /**
     * Get account balance
     *
     * @return Attribute
     */
    protected function balance(): Attribute
    {
        return Attribute::get(function (): CoinFormatter {
            return $this->getTotalReceivedObject()->subtract($this->getTotalSentObject());
        });
    }

    /**
     * @return float|string
     */
    protected function getBalancePriceAttribute(): float|string
    {
        return $this->getBalanceObject()->getPrice($this->user->currency);
    }

    /**
     * @return string
     */
    protected function getFormattedBalancePriceAttribute(): string
    {
        return $this->getBalanceObject()->getFormattedPrice($this->user->currency);
    }

    /**
     * @return CoinFormatter
     */
    public function getTotalReceivedObject(): CoinFormatter
    {
        return $this->total_received;
    }

    /**
     * Total received
     *
     * @return Attribute
     */
    protected function totalReceived(): Attribute
    {
        return Attribute::get(function (): CoinFormatter {
            $totalReceived = $this->transferRecords()
                ->whereColumn('confirmations', '>=', 'required_confirmations')
                ->where('type', 'receive')->sum('value');

            return $this->wallet->castCoin($totalReceived);
        });
    }

    /**
     * @return float|string
     */
    protected function getTotalReceivedPriceAttribute(): float|string
    {
        return $this->getTotalReceivedObject()->getPrice($this->user->currency);
    }

    /**
     * @return string
     */
    protected function getFormattedTotalReceivedPriceAttribute(): string
    {
        return $this->getTotalReceivedObject()->getFormattedPrice($this->user->currency);
    }

    /**
     * @return CoinFormatter
     */
    public function getTotalSentObject(): CoinFormatter
    {
        return $this->total_sent;
    }

    /**
     * Get total sent
     *
     * @return Attribute
     */
    protected function totalSent(): Attribute
    {
        return Attribute::get(function (): CoinFormatter {
            $totalSent = $this->transferRecords()->where('type', 'send')->sum('value');

            return $this->wallet->castCoin($totalSent);
        });
    }

    /**
     * @return float|string
     */
    protected function getTotalSentPriceAttribute(): float|string
    {
        return $this->getTotalSentObject()->getPrice($this->user->currency);
    }

    /**
     * @return string
     */
    protected function getFormattedTotalSentPriceAttribute(): string
    {
        return $this->getTotalSentObject()->getFormattedPrice($this->user->currency);
    }

    /**
     * Related Exchange Trades
     *
     * @return HasMany
     */
    public function exchangeTrades(): HasMany
    {
        return $this->hasMany(ExchangeTrade::class, 'wallet_account_id', 'id');
    }

    /**
     * Calculate transaction fee
     *
     * @param  CoinFormatter  $amount
     * @return CoinFormatter
     */
    public function getTransactionFee(CoinFormatter $amount): CoinFormatter
    {
        return $this->wallet->estimateTransactionFee($amount->getAmount(), $this->total_unspent_address);
    }

    /**
     * Get withdrawal fee
     *
     * @param  CoinFormatter  $amount
     * @return CoinFormatter
     */
    public function getWithdrawalFee(CoinFormatter $amount): CoinFormatter
    {
        return $this->wallet->getWithdrawalFee($amount);
    }

    /**
     * Get buy exchange fee
     *
     * @param  CoinFormatter  $amount
     * @return CoinFormatter
     */
    public function getBuyExchangeFee(CoinFormatter $amount): CoinFormatter
    {
        return $this->wallet->getExchangeFee($amount, 'buy');
    }

    /**
     * Get sell exchange fee
     *
     * @param  CoinFormatter  $amount
     * @return CoinFormatter
     */
    public function getSellExchangeFee(CoinFormatter $amount): CoinFormatter
    {
        return $this->wallet->getExchangeFee($amount, 'sell');
    }

    /**
     * Get total unspent address
     *
     * @return Attribute
     */
    protected function totalUnspentAddress(): Attribute
    {
        return Attribute::get(function (): int {
            $query = $this->transferRecords()
                ->whereNotNull('wallet_transaction_id')
                ->where('type', 'receive');

            $lastSentQuery = $this->transferRecords()
                ->whereNotNull('wallet_transaction_id')
                ->where('type', 'send')->latest();

            if ($lastSent = $lastSentQuery->first()) {
                $query->where('created_at', '>=', $lastSent->created_at);
            }

            return $query->count() ?: 1;
        })->shouldCache();
    }

    /**
     * Parse amount as money
     *
     * @param $amount
     * @return Money
     */
    public function parseMoney($amount): Money
    {
        return $this->wallet->parseMoney($amount, $this->user->currency);
    }

    /**
     * Get price as Money object
     *
     * @return Money
     */
    public function getPriceAsMoney(): Money
    {
        return $this->wallet->getUnitObject()->getPriceAsMoney($this->user->currency);
    }

    /**
     * Get coin price
     *
     * @return float|string
     */
    protected function getPriceAttribute(): float|string
    {
        return $this->wallet->getUnitObject()->getPrice($this->user->currency);
    }

    /**
     * Get formatted coin price
     *
     * @return string
     */
    protected function getFormattedPriceAttribute(): string
    {
        return $this->wallet->getUnitObject()->getFormattedPrice($this->user->currency);
    }

    /**
     * Get coin name
     *
     * @return string
     */
    protected function getCoinAttribute(): string
    {
        return $this->wallet->coin->name;
    }

    /**
     * Validate amount
     *
     * @param  CoinFormatter  $amount
     * @return CoinFormatter
     */
    protected function validateAmount(CoinFormatter $amount): CoinFormatter
    {
        return tap($amount, function (CoinFormatter $amount) {
            if ($amount->getCoin()->isNot($this->wallet->coin)) {
                throw new UnexpectedValueException('Unexpected coin.');
            }
        });
    }

    /**
     * Create wallet address
     *
     * @return WalletAddress
     */
    public function createAddress(): WalletAddress
    {
        return $this->wallet->createAddress($this);
    }

    /**
     * Associate address with this account and save
     *
     * @param  WalletAddress  $address
     * @return bool
     */
    public function saveAddress(WalletAddress $address): bool
    {
        if ($this->wallet->isNot($address->wallet)) {
            throw new InvalidArgumentException('Address does not belong to the same wallet.');
        }

        if ($address->exists) {
            return $address->exists;
        }

        $address->walletAccount()->associate($this);

        return $address->save();
    }

    /**
     * Credit wallet account
     *
     * @param  CoinFormatter  $amount
     * @param  string  $description
     * @param  float|null  $dollarPrice
     * @return TransferRecord
     */
    public function credit(CoinFormatter $amount, string $description, float $dollarPrice = null): TransferRecord
    {
        $value = $this->validateAmount($amount);

        return $this->transferRecords()->create([
            'type' => 'receive',
            'description' => $description,
            'dollar_price' => $dollarPrice ?: $value->getDollarPrice(),
            'value' => $value,
        ]);
    }

    /**
     * Debit wallet account
     *
     * @param  CoinFormatter  $amount
     * @param  string  $description
     * @param  float|null  $dollarPrice
     * @return TransferRecord
     */
    public function debit(CoinFormatter $amount, string $description, float $dollarPrice = null): TransferRecord
    {
        $value = $this->validateAmount($amount);

        return $this->transferRecords()->create([
            'type' => 'send',
            'description' => $description,
            'dollar_price' => $dollarPrice ?: $value->getDollarPrice(),
            'value' => $value,
        ]);
    }

    /**
     * Parse amount as CoinFormatter object
     *
     * @param $amount
     * @return CoinFormatter
     */
    public function parseCoin($amount): CoinFormatter
    {
        if (is_numeric($amount)) {
            return $this->wallet->parseCoin($amount);
        }

        if ($amount instanceof CoinFormatter) {
            return $amount;
        }

        throw new InvalidArgumentException('Invalid amount.');
    }

    /**
     * Parse target as either a user a wallet account or external address
     *
     * @param  User|WalletAccount|string  $target
     * @return WalletAccount|string
     *
     * @throws TransferException
     */
    public function parseTarget(User|WalletAccount|string $target): string|WalletAccount
    {
        if ($target instanceof self) {
            if ($target->is($this)) {
                throw new TransferException(trans('wallet.cannot_send_to_same_account'));
            } elseif ($target->wallet->isNot($this->wallet)) {
                throw new TransferException(trans('wallet.different_account_parent_wallet'));
            }

            return $target;
        } elseif (is_string($target)) {
            if (!filter_var($target, FILTER_VALIDATE_EMAIL)) {
                $query = $this->wallet->addresses()->where('address', $target);

                if ($address = $query->first()) {
                    if ($address->walletAccount->is($this)) {
                        throw new TransferException(trans('wallet.cannot_send_to_same_account'));
                    } elseif ($address->walletAccount->wallet->isNot($this->wallet)) {
                        throw new TransferException(trans('wallet.different_account_parent_wallet'));
                    }

                    return $address->walletAccount;
                } else {
                    return $target;
                }
            } else {
                if (!$user = User::whereEmail($target)->first()) {
                    throw new TransferException(trans('auth.user_does_not_exist'));
                } elseif ($user->is($this->user)) {
                    throw new TransferException(trans('wallet.cannot_send_to_same_user'));
                }

                return $this->wallet->getAccount($user);
            }
        } elseif ($target instanceof User) {
            if ($target->is($this->user)) {
                throw new TransferException(trans('wallet.cannot_send_to_same_user'));
            }

            return $this->wallet->getAccount($target);
        }

        throw new InvalidArgumentException('Invalid target provided.');
    }

    /**
     * Handle internal and external transfer
     *
     * @param $value
     * @param  User|WalletAccount|string  $to
     * @return TransferRecord
     *
     * @throws LockException
     */
    public function send($value, User|WalletAccount|string $to): TransferRecord
    {
        return $this->acquireLockOrThrow(function () use ($value, $to) {
            $account = $this->fresh();
            $amount = $account->parseCoin($value);
            $target = $account->parseTarget($to);

            $coin = $account->wallet->coin;

            if ($amount->isNegativeOrZero()) {
                throw new InvalidArgumentException(trans('wallet.invalid_amount'));
            }

            if ($target instanceof self) {
                return DB::transaction(function () use ($account, $coin, $target, $amount) {
                    if ($account->getAvailableObject()->lessThan($amount)) {
                        throw new TransferException(trans('wallet.insufficient_available'));
                    }

                    $target->transferRecords()->create([
                        'value' => $amount,
                        'type' => 'receive',
                        'description' => $target->getIncomingDescription($account),
                        'dollar_price' => $coin->getDollarPrice(),
                    ]);

                    return $account->transferRecords()->create([
                        'value' => $amount,
                        'type' => 'send',
                        'description' => $account->getOutgoingDescription($target),
                        'dollar_price' => $coin->getDollarPrice(),
                    ]);
                });
            } elseif (is_string($target)) {
                $operatorAccount = $account->wallet->operatorAccount();
                $withdrawalFee = $account->getWithdrawalFee($amount);
                $transactionFee = $account->getTransactionFee($amount);

                if ($account->isNot($operatorAccount)) {
                    $deductible = $amount->add($transactionFee)->add($withdrawalFee);
                } else {
                    $deductible = $amount->add($transactionFee);
                }

                if ($account->getAvailableObject()->lessThan($deductible)) {
                    throw new TransferException(trans('wallet.insufficient_available'));
                }

                $transferRecord = $account->transferRecords()->create([
                    'value' => $deductible,
                    'type' => 'send',
                    'required_confirmations' => 1,
                    'external' => true,
                    'description' => $account->getOutgoingDescription($target),
                    'dollar_price' => $coin->getDollarPrice(),
                ]);

                $resource = $account->wallet->send($target, $amount->getAmount());

                $transaction = $account->wallet->transactions()->create([
                    'hash' => $resource->getHash(),
                    'value' => $resource->getValue(),
                    'type' => $resource->getType(),
                    'confirmations' => $resource->getConfirmations(),
                    'date' => $resource->getDate(),
                    'resource' => $resource,
                ]);

                $transferRecord->walletTransaction()->associate($transaction);

                if ($operatorAccount?->isNot($account) && $withdrawalFee->isPositive()) {
                    $description = $transferRecord->getFeeDescription();
                    $earningTransaction = $operatorAccount->credit($withdrawalFee, $description);
                    Earning::saveWalletTransaction($earningTransaction);
                }

                return tap($transferRecord)->save();
            }

            throw new InvalidArgumentException('Invalid target provided.');
        });
    }

    /**
     * Get incoming description
     *
     * @param  WalletAccount|string  $sender
     * @return string
     */
    public function getIncomingDescription(WalletAccount|string $sender): string
    {
        if ($sender instanceof static) {
            return trans('wallet.internal_incoming_description', ['name' => $sender->user->name]);
        } else {
            return trans('wallet.external_incoming_description', ['address' => $sender]);
        }
    }

    /**
     * Get outgoing description
     *
     * @param  WalletAccount|string  $target
     * @return string
     */
    public function getOutgoingDescription(WalletAccount|string $target): string
    {
        if ($target instanceof static) {
            return trans('wallet.internal_outgoing_description', ['name' => $target->user->name]);
        } else {
            return trans('wallet.external_outgoing_description', ['address' => $target]);
        }
    }

    /**
     * Related "sell" PeerTrade
     *
     * @return HasMany
     */
    public function sellPeerTrades(): HasMany
    {
        return $this->hasMany(PeerTrade::class, 'seller_wallet_account_id', 'id');
    }

    /**
     * Related "buy" PeerTrade
     *
     * @return HasMany
     */
    public function buyPeerTrades(): HasMany
    {
        return $this->hasMany(PeerTrade::class, 'buyer_wallet_account_id', 'id');
    }

    /**
     * @return BelongsTo
     */
    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'wallet_id', 'id');
    }

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Standard wallet addresses for receiving
     *
     * @return HasMany
     */
    public function standardAddresses(): HasMany
    {
        return $this->addresses()->doesntHave('commerceTransactions');
    }

    /**
     * Wallet addresses with association to commerce
     *
     * @return HasMany
     */
    public function commerceAddresses(): HasMany
    {
        return $this->addresses()->has('commerceTransactions');
    }

    /**
     * Get completed commerce address
     *
     * @return HasMany
     */
    public function completedCommerceAddresses(): HasMany
    {
        return $this->addresses()->whereHas('commerceTransactions', fn ($query) => $query->isCompleted());
    }

    /**
     * Get pending commerce address
     *
     * @return HasMany
     */
    public function pendingCommerceAddresses(): HasMany
    {
        return $this->addresses()->whereHas('commerceTransactions', fn ($query) => $query->isPending());
    }

    /**
     * Get canceled commerce address
     *
     * @return HasMany
     */
    public function canceledCommerceAddresses(): HasMany
    {
        return $this->addresses()->whereHas('commerceTransactions', fn ($query) => $query->isCanceled());
    }

    /**
     * Check commerce address limit
     *
     * @return bool
     */
    public function hasMaximumPendingCommerceAddress(): bool
    {
        return $this->pendingCommerceAddresses()->count() >= settings()->commerce->get('pending_transactions');
    }

    /**
     * @return HasMany
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(WalletAddress::class, 'wallet_account_id', 'id')->latest();
    }

    /**
     * @return HasMany
     */
    public function transferRecords(): HasMany
    {
        return $this->hasMany(TransferRecord::class, 'wallet_account_id', 'id');
    }

    /**
     * @param $address
     * @return bool
     */
    public function hasAddress($address): bool
    {
        return $this->addresses()->where('address', $address)->exists();
    }
}
