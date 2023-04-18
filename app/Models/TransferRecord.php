<?php

namespace App\Models;

use App\Casts\CoinFormatterCast;
use App\Events\TransferRecordSaved;
use App\Helpers\CoinFormatter;
use App\Models\Support\Lock;
use App\Models\Support\ValidateCoinFormatter;
use App\Models\Support\WalletAttribute;
use App\Notifications\WalletCredit;
use App\Notifications\WalletDebit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use UnexpectedValueException;

class TransferRecord extends Model implements WalletAttribute
{
    use HasFactory, Lock, ValidateCoinFormatter;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id', 'created_at', 'updated_at'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'value' => CoinFormatterCast::class,
        'balance' => CoinFormatterCast::class,
        'dollar_price' => 'float',
        'external' => 'boolean',
    ];

    /**
     * The event map for the model.
     *
     * @var array
     */
    protected $dispatchesEvents = [
        'saved' => TransferRecordSaved::class,
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'value_price',
        'formatted_value_price',
        'balance_price',
        'formatted_balance_price',
        'hash',
        'confirmed',
        'coin',
    ];

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = ['walletAccount'];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'balance' => 0,
    ];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted(): void
    {
        static::saved(function (self $record) {
            $user = $record->walletAccount->user;

            if ($record->isDirty('confirmations') && $record->confirmed) {
                match ($record->type) {
                    'receive' => $user->notify(new WalletCredit($record)),
                    'send' => $user->notify(new WalletDebit($record))
                };
            }
        });

        static::updating(function (self $record) {
            if ($record->getOriginal('confirmations') > $record->confirmations) {
                throw new UnexpectedValueException('You cannot decrease confirmation.');
            }

            if ($record->isDirty('required_confirmations', 'type', 'address', 'external', 'wallet_account_id')) {
                throw new UnexpectedValueException('You cannot change property.');
            }
        });

        static::saved(function (self $record) {
            if ($record->confirmed && $record->getBalanceObject()->isZero()) {
                $balance = $record->walletAccount->fresh()->getBalanceObject();

                $record->updateQuietly(['balance' => $balance]);
            }
        });
    }

    /**
     * @return bool
     */
    protected function getConfirmedAttribute(): bool
    {
        return $this->confirmations >= $this->required_confirmations;
    }

    /**
     * @return Coin
     */
    protected function getCoinAttribute(): Coin
    {
        return $this->walletAccount->wallet->coin;
    }

    /**
     * @return CoinFormatter
     */
    public function getValueObject(): CoinFormatter
    {
        return $this->value;
    }

    /**
     * @return float|string
     */
    protected function getValuePriceAttribute(): float|string
    {
        return $this->getValueObject()->getPrice($this->walletAccount->user->currency, $this->dollar_price);
    }

    /**
     * @return string
     */
    protected function getFormattedValuePriceAttribute(): string
    {
        return $this->getValueObject()->getFormattedPrice($this->walletAccount->user->currency, $this->dollar_price);
    }

    /**
     * Get balance object
     *
     * @return CoinFormatter
     */
    public function getBalanceObject(): CoinFormatter
    {
        return $this->balance;
    }

    /**
     * @return float|string
     */
    protected function getBalancePriceAttribute(): float|string
    {
        return $this->getBalanceObject()->getPrice($this->walletAccount->user->currency, $this->dollar_price);
    }

    /**
     * @return string
     */
    protected function getFormattedBalancePriceAttribute(): string
    {
        return $this->getBalanceObject()->getFormattedPrice($this->walletAccount->user->currency, $this->dollar_price);
    }

    /**
     * Receiving address
     *
     * @return BelongsTo
     */
    public function walletAddress(): BelongsTo
    {
        $relation = $this->belongsTo(WalletAddress::class, 'address', 'address');

        if ($this->wallet_account_id) {
            return $relation->where('wallet_account_id', $this->wallet_account_id);
        } else {
            return $relation;
        }
    }

    /**
     * Wallet transaction if it is external
     *
     * @return BelongsTo
     */
    public function walletTransaction(): BelongsTo
    {
        return $this->belongsTo(WalletTransaction::class, 'wallet_transaction_id', 'id');
    }

    /**
     * Get related pending withdrawal
     *
     * @return HasOne
     */
    public function pendingApproval(): HasOne
    {
        return $this->hasOne(PendingApproval::class, 'transfer_record_id', 'id');
    }

    /**
     * Get transaction hash
     *
     * @return string|null
     */
    protected function getHashAttribute(): ?string
    {
        return $this->walletTransaction()->value('hash');
    }

    /**
     * Removable Attribute
     *
     * @return bool
     */
    protected function getRemovableAttribute(): bool
    {
        return $this->isRemovable();
    }

    /**
     * To be used as description for revenue credit
     *
     * @return string
     */
    public function getFeeDescription(): string
    {
        if ($this->type === 'send') {
            return trans('wallet.withdrawal_fee_description', [
                'name' => $this->walletAccount->user->name,
            ]);
        } else {
            return trans('wallet.deposit_fee_description', [
                'name' => $this->walletAccount->user->name,
            ]);
        }
    }

    /**
     * Check if transfer record is removable
     *
     * @return bool
     */
    public function isRemovable(): bool
    {
        return $this->type === 'send' && $this->external &&
            $this->confirmations < $this->required_confirmations &&
            $this->walletTransaction()->doesntExist();
    }

    /**
     * Scope Unconfirmed query
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeUnconfirmed(Builder $query): Builder
    {
        return $query->whereColumn('confirmations', '<', 'required_confirmations');
    }

    /**
     * Scope Confirmed query
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeConfirmed(Builder $query): Builder
    {
        return $query->whereColumn('confirmations', '>=', 'required_confirmations');
    }

    /**
     * Related Wallet account
     *
     * @return BelongsTo
     */
    public function walletAccount(): BelongsTo
    {
        return $this->belongsTo(WalletAccount::class, 'wallet_account_id', 'id');
    }

    /**
     * {@inheritDoc}
     */
    public function getWallet(): Wallet
    {
        return $this->walletAccount->wallet;
    }
}
