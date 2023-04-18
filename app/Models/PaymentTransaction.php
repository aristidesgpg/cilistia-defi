<?php

namespace App\Models;

use Akaunting\Money\Currency;
use Akaunting\Money\Money;
use App\Casts\MoneyCast;
use App\Events\PaymentTransactionSaved;
use App\Exceptions\LockException;
use App\Models\Support\CurrencyAttribute;
use App\Models\Support\Lock;
use App\Models\Support\Uuid;
use App\Models\Support\ValidateMoney;
use App\Notifications\PaymentCredit;
use App\Notifications\PaymentDebit;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use UnexpectedValueException;

class PaymentTransaction extends Model implements CurrencyAttribute
{
    use HasFactory, Uuid, Lock, ValidateMoney;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['payment_account_id', 'created_at', 'updated_at'];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'value' => MoneyCast::class,
        'balance' => MoneyCast::class,
    ];

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = ['account'];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'formatted_value',
        'formatted_balance',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['transfer_beneficiary', 'transfer_number', 'transfer_note'];

    /**
     * The event map for the model.
     *
     * @var array
     */
    protected $dispatchesEvents = [
        'saved' => PaymentTransactionSaved::class,
    ];

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
        static::updating(function (self $record) {
            if ($record->isDirty('status') && $record->originalStatusIsFinal()) {
                throw new UnexpectedValueException('You cannot change status.');
            }

            if ($record->isDirty('type', 'gateway_name', 'gateway_ref')) {
                throw new UnexpectedValueException('You cannot change property.');
            }
        });

        static::saved(function (self $record) {
            if ($record->isDirty('status') && $record->isCompleted()) {
                match ($record->type) {
                    'receive' => $record->account->user->notify(new PaymentCredit($record)),
                    'send' => $record->account->user->notify(new PaymentDebit($record))
                };
            }
        });

        static::saved(function (self $record) {
            if ($record->isCompleted() && $record->getBalanceObject()->isZero()) {
                $balance = $record->account->fresh()->getBalanceObject();
                $record->updateQuietly(['balance' => $balance]);
            }
        });
    }

    /**
     * Get value object
     *
     * @return Money
     */
    public function getValueObject(): Money
    {
        return $this->value;
    }

    /**
     * Get formatted value
     *
     * @return string
     */
    protected function getFormattedValueAttribute(): string
    {
        return $this->getValueObject()->format();
    }

    /**
     * Get balance object
     *
     * @return Money
     */
    public function getBalanceObject(): Money
    {
        return $this->balance;
    }

    /**
     * Get formatted balance
     *
     * @return string
     */
    protected function getFormattedBalanceAttribute(): string
    {
        return $this->getBalanceObject()->format();
    }

    /**
     * Complete gateway
     *
     * @return PaymentTransaction|null
     *
     * @throws LockException
     */
    public function completeGateway(): ?PaymentTransaction
    {
        return $this->acquireLockOrThrow(function (self $record) {
            if (!$record->isPendingGateway() || $record->type !== 'receive') {
                throw new Exception('Transaction is not a pending gateway.');
            }

            $gateway = app('multipay')->gateway($record->gateway_name);

            if ($gateway->verify($record->gateway_ref)) {
                FeatureLimit::paymentsDeposit()->setUsage($record->getValueObject(), $record->account->user);

                return tap($record)->update(['status' => 'completed']);
            }
        });
    }

    /**
     * Complete transfer
     *
     * @return PaymentTransaction
     *
     * @throws LockException
     */
    public function completeTransfer(): PaymentTransaction
    {
        return $this->acquireLockOrThrow(function (self $record) {
            if (!$record->isPendingTransfer()) {
                throw new Exception('Transaction is not pending.');
            }

            $value = $record->getValueObject();

            match ($record->type) {
                'receive' => FeatureLimit::paymentsDeposit()->setUsage($value, $record->account->user),
                'send' => FeatureLimit::paymentsWithdrawal()->setUsage($value, $record->account->user)
            };

            return tap($record)->update(['status' => 'completed']);
        });
    }

    /**
     * Check if transaction is completed
     *
     * @return bool
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if transaction is pending
     *
     * @return bool
     */
    public function isPending(): bool
    {
        return in_array($this->status, ['pending-gateway', 'pending-transfer']);
    }

    /**
     * Check pending gateway
     *
     * @return bool
     */
    public function isPendingGateway(): bool
    {
        return $this->status === 'pending-gateway';
    }

    /**
     * Check pending transfer
     *
     * @return bool
     */
    public function isPendingTransfer(): bool
    {
        return $this->status === 'pending-transfer';
    }

    /**
     * Check if it is overdue
     *
     * @return bool
     */
    public function isPendingOverdue(): bool
    {
        return $this->isPending() && $this->created_at->clone()->addHours(3) < now();
    }

    /**
     * Check if original value of status was in final state
     *
     * @return bool
     */
    public function originalStatusIsFinal(): bool
    {
        return in_array($this->getOriginal('status'), ['completed', 'canceled']);
    }

    /**
     * Cancel transaction
     *
     * @return PaymentTransaction
     *
     * @throws LockException
     */
    public function cancelPending(): PaymentTransaction
    {
        return $this->acquireLockOrThrow(function (self $record) {
            if (!$record->isPending()) {
                throw new Exception('Transaction is not pending.');
            }

            return tap($record)->update(['status' => 'canceled']);
        });
    }

    /**
     * Get payment account
     *
     * @return BelongsTo
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(PaymentAccount::class, 'payment_account_id', 'id');
    }

    /**
     * Scope completed query
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope canceled query
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeCanceled(Builder $query): Builder
    {
        return $query->where('status', 'canceled');
    }

    /**
     * Scope pending query
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->whereIn('status', ['pending-transfer', 'pending-gateway']);
    }

    /**
     * Scope pending transfer query
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopePendingTransfer(Builder $query): Builder
    {
        return $query->where('status', 'pending-transfer');
    }

    /**
     * Scope pending gateway query
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopePendingGateway(Builder $query): Builder
    {
        return $query->where('status', 'pending-gateway');
    }

    /**
     * {@inheritDoc}
     */
    public function getCurrency(): Currency
    {
        return currency($this->account->currency);
    }
}
