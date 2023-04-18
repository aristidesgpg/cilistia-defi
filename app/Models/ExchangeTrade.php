<?php

namespace App\Models;

use Akaunting\Money\Currency;
use Akaunting\Money\Money;
use App\Casts\CoinFormatterCast;
use App\Casts\MoneyCast;
use App\Events\ExchangeTradeSaved;
use App\Exceptions\TransferException;
use App\Helpers\CoinFormatter;
use App\Models\Support\CurrencyAttribute;
use App\Models\Support\Lock;
use App\Models\Support\WalletAttribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

class ExchangeTrade extends Model implements CurrencyAttribute, WalletAttribute
{
    use HasFactory, Lock;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['status', 'completed_at'];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'wallet_value' => CoinFormatterCast::class,
        'payment_value' => MoneyCast::class,
        'fee_value' => CoinFormatterCast::class,
        'completed_at' => 'datetime',
        'dollar_price' => 'float',
    ];

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = ['walletAccount', 'paymentAccount', 'trader'];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'wallet_value_price',
        'formatted_wallet_value_price',
        'payment_currency',
        'formatted_payment_value',
        'payment_symbol',
        'coin',
    ];

    /**
     * The event map for the model.
     *
     * @var array
     */
    protected $dispatchesEvents = [
        'saved' => ExchangeTradeSaved::class,
    ];

    /**
     * Perform any actions required after the model boots.
     *
     * @return void
     */
    protected static function booted(): void
    {
        static::updating(function (self $record) {
            if ($record->isDirty('status')) {
                $attribute = match ($record->status) {
                    'completed' => 'completed_at',
                    default => null
                };

                if (is_string($attribute)) {
                    $record->$attribute = Date::now();
                }
            }
        });

        static::saving(CoinFormatterCast::assert('wallet_value', 'fee_value'));
        static::saving(MoneyCast::assert('payment_value'));
    }

    /**
     * Payment Value Object
     *
     * @return Money
     */
    public function getPaymentValueObject(): Money
    {
        return $this->payment_value;
    }

    /**
     * Get formatted payment value
     *
     * @return string
     */
    protected function getFormattedPaymentValueAttribute(): string
    {
        return $this->getPaymentValueObject()->format();
    }

    /**
     * Related payment account
     *
     * @return BelongsTo
     */
    public function paymentAccount(): BelongsTo
    {
        return $this->belongsTo(PaymentAccount::class, 'payment_account_id', 'id');
    }

    /**
     * Get fee value object
     *
     * @return CoinFormatter
     */
    public function getFeeValueObject(): CoinFormatter
    {
        return $this->fee_value;
    }

    /**
     * Get wallet value coin object
     *
     * @return CoinFormatter
     */
    public function getWalletValueObject(): CoinFormatter
    {
        return $this->wallet_value;
    }

    /**
     * Price of wallet value in payment currency
     *
     * @return float|string
     */
    protected function getWalletValuePriceAttribute(): float|string
    {
        return $this->getWalletValueObject()->getPrice($this->paymentAccount->currency, $this->dollar_price);
    }

    /**
     * @return string
     */
    protected function getFormattedWalletValuePriceAttribute(): string
    {
        return $this->getWalletValueObject()->getFormattedPrice($this->paymentAccount->currency, $this->dollar_price);
    }

    /**
     * Related wallet account
     *
     * @return BelongsTo
     */
    public function walletAccount(): BelongsTo
    {
        return $this->belongsTo(WalletAccount::class, 'wallet_account_id', 'id');
    }

    /**
     * Get coin attribute
     *
     * @return Coin
     */
    protected function getCoinAttribute(): Coin
    {
        return $this->walletAccount->wallet->coin;
    }

    /**
     * Get payment currency
     *
     * @return string
     */
    protected function getPaymentCurrencyAttribute(): string
    {
        return $this->paymentAccount->currency;
    }

    /**
     * Get payment symbol
     *
     * @return string
     */
    protected function getPaymentSymbolAttribute(): string
    {
        return $this->paymentAccount->symbol;
    }

    /**
     * Trader object
     *
     * @return BelongsTo
     */
    public function trader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'trader_id', 'id');
    }

    /**
     * Complete pending buy
     *
     * @return ExchangeTrade
     */
    public function completePendingBuy(): ExchangeTrade
    {
        return $this->paymentAccount->acquireLockOrThrow(function () {
            return $this->acquireLockOrThrow(function (self $record) {
                if ($record->type !== 'buy' || $record->status !== 'pending') {
                    throw new TransferException('Trade is not pending.');
                }

                $traderAccount = $record->walletAccount->parseTarget($record->trader);

                return $traderAccount->acquireLockOrThrow(function (WalletAccount $traderWalletAccount) use ($record) {
                    return DB::transaction(function () use ($record, $traderWalletAccount) {
                        $amount = $record->getWalletValueObject();

                        if ($traderWalletAccount->getAvailableObject()->lessThan($amount)) {
                            throw new TransferException(trans('wallet.insufficient_trader_available'));
                        }

                        $traderDescription = $record->getTransferDescription($traderWalletAccount->user);
                        $targetDescription = $record->getTransferDescription($record->walletAccount->user);

                        $record->walletAccount->credit($amount, $targetDescription, $record->dollar_price);
                        $traderWalletAccount->debit($amount, $traderDescription, $record->dollar_price);

                        $paymentValue = $record->getPaymentValueObject();

                        $record->paymentAccount->debit($paymentValue, $targetDescription);
                        $traderPaymentAccount = $record->trader->getPaymentAccountByCurrency($record->paymentAccount->currency);
                        $traderPaymentAccount->credit($paymentValue, $traderDescription);

                        FeatureLimit::walletExchange()->setUsage($paymentValue, $record->paymentAccount->user);
                        Earning::saveCoin($record->getFeeValueObject(), $traderDescription, $record->trader);

                        return tap($record)->update(['status' => 'completed']);
                    });
                });
            });
        });
    }

    /**
     * Canceled pending
     *
     * @return ExchangeTrade
     */
    public function cancelPending(): ExchangeTrade
    {
        return $this->acquireLockOrThrow(function (self $record) {
            if ($record->status !== 'pending') {
                throw new TransferException('Exchange trade is not pending.');
            }

            return tap($record)->update(['status' => 'canceled']);
        });
    }

    /**
     * Get transfer description
     *
     * @param  User  $recipient
     * @return string
     */
    public function getTransferDescription(User $recipient): string
    {
        if ($this->type === 'sell') {
            $description = match (true) {
                $recipient->is($this->trader) => 'exchange.buy_description',
                $recipient->is($this->walletAccount->user) => 'exchange.sell_description',
                default => throw new \InvalidArgumentException('Unrecognized user.')
            };
        } else {
            $description = match (true) {
                $recipient->is($this->trader) => 'exchange.sell_description',
                $recipient->is($this->walletAccount->user) => 'exchange.buy_description',
                default => throw new \InvalidArgumentException('Unrecognized user.')
            };
        }

        return trans($description, [
            'currency' => $this->paymentAccount->currency,
            'coin' => $this->coin->name,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function getCurrency(): Currency
    {
        return currency($this->paymentAccount->currency);
    }

    /**
     * {@inheritDoc}
     */
    public function getWallet(): Wallet
    {
        return $this->walletAccount->wallet;
    }
}
