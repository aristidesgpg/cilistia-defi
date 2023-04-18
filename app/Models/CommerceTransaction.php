<?php

namespace App\Models;

use App\Casts\CoinFormatterCast;
use App\Exceptions\CommerceException;
use App\Helpers\CoinFormatter;
use App\Models\Support\Lock;
use App\Models\Support\Uuid;
use App\Models\Support\ValidateCoinFormatter;
use App\Models\Support\WalletAttribute;
use Illuminate\Broadcasting\Channel;
use Illuminate\Database\Eloquent\BroadcastsEvents;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Date;
use UnexpectedValueException;

class CommerceTransaction extends Model implements WalletAttribute
{
    use HasFactory, Uuid, Lock, ValidateCoinFormatter, BroadcastsEvents;

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'status' => 'pending',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public $fillable = ['status'];

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = ['customer', 'walletAccount'];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'value' => CoinFormatterCast::class,
        'dollar_price' => 'float',
        'expires_at' => 'datetime',
        'completed_at' => 'datetime',
        'canceled_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['wallet'];

    /**
     * Perform any actions required after the model boots.
     *
     * @return void
     */
    protected static function booted(): void
    {
        static::updating(function (self $record) {
            if ($record->isDirty('status') && $record->originalStatusIsFinal()) {
                throw new UnexpectedValueException('You cannot change status.');
            }
        });

        static::updating(function (self $record) {
            if ($record->isDirty('status')) {
                $attribute = match ($record->status) {
                    'canceled' => 'canceled_at',
                    'completed' => 'completed_at',
                    default => null
                };

                if (is_string($attribute)) {
                    $record->$attribute = Date::now();
                }
            }
        });
    }

    /**
     * Complete commerce transaction
     *
     * @return bool|void
     */
    public function complete()
    {
        if (!$this->isPending() || !$this->walletAddress) {
            throw new CommerceException('Transaction is not pending.');
        }

        $totalReceived = $this->walletAddress->getTotalReceivedFrom($this->created_at);

        if ($totalReceived->greaterThanOrEqual($this->value)) {
            return $this->update(['status' => 'completed']);
        }
    }

    /**
     * Cancel commerce transaction
     *
     * @return bool|void
     */
    public function cancel()
    {
        if (!$this->isPending() || !$this->isOverdue()) {
            throw new CommerceException('Transaction is not pending.');
        }

        if (!$walletAddress = $this->walletAddress) {
            return $this->update(['status' => 'canceled']);
        }

        $totalReceived = $walletAddress->getAbsoluteTotalReceivedFrom($this->created_at);

        if ($totalReceived->lessThan($this->value)) {
            return $this->update(['status' => 'canceled']);
        }
    }

    /**
     * Get price in currency
     *
     * @return float|string
     */
    protected function getPriceAttribute(): float|string
    {
        return $this->value->getPrice($this->currency, $this->dollar_price);
    }

    /**
     * Get formatted price
     *
     * @return string
     */
    protected function getFormattedPriceAttribute(): string
    {
        return $this->value->getFormattedPrice($this->currency, $this->dollar_price);
    }

    /**
     * Get total received
     *
     * @return Attribute
     */
    protected function received(): Attribute
    {
        return Attribute::get(function (): ?CoinFormatter {
            return $this->walletAddress?->getTotalReceivedFrom($this->created_at);
        });
    }

    /**
     * Get received price
     *
     * @return float|string|null
     */
    protected function getReceivedPriceAttribute(): float|string|null
    {
        return $this->received?->getPrice($this->currency, $this->dollar_price);
    }

    /**
     * Get formatted received price
     *
     * @return string|null
     */
    protected function getFormattedReceivedPriceAttribute(): ?string
    {
        return $this->received?->getFormattedPrice($this->currency, $this->dollar_price);
    }

    /**
     * Get unconfirmed received
     *
     * @return Attribute
     */
    protected function unconfirmedReceived(): Attribute
    {
        return Attribute::get(function (): ?CoinFormatter {
            return $this->walletAddress?->getUnconfirmedTotalReceivedFrom($this->created_at);
        });
    }

    /**
     * Get unconfirmed_received_price
     *
     * @return float|string|null
     */
    protected function getUnconfirmedReceivedPriceAttribute(): float|string|null
    {
        return $this->unconfirmed_received?->getPrice($this->currency, $this->dollar_price);
    }

    /**
     * Get formatted_unconfirmed_received_price
     *
     * @return string|null
     */
    protected function getFormattedUnconfirmedReceivedPriceAttribute(): ?string
    {
        return $this->unconfirmed_received?->getFormattedPrice($this->currency, $this->dollar_price);
    }

    /**
     * Get progress fulfilled
     *
     * @return float
     */
    protected function getProgressAttribute(): float
    {
        return min(round((($this->received?->getValue() ?: 0) / $this->value->getValue()) * 100), 100);
    }

    /**
     * Get wallet attribute
     *
     * @return Wallet
     */
    protected function getWalletAttribute(): Wallet
    {
        return $this->walletAccount->wallet;
    }

    /**
     * Check if original status was in final state
     *
     * @return bool
     */
    public function originalStatusIsFinal(): bool
    {
        return in_array($this->getOriginal('status'), ['completed', 'canceled']);
    }

    /**
     * Check if transaction is overdue
     *
     * @return bool
     */
    public function isOverdue(): bool
    {
        return now()->isAfter($this->expires_at);
    }

    /**
     * Check if transaction is pending
     *
     * @return bool
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
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
     * Check if transaction is canceled
     *
     * @return bool
     */
    public function isCanceled(): bool
    {
        return $this->status === 'canceled';
    }

    /**
     * Check if transaction is in final state
     *
     * @return bool
     */
    public function isFinal(): bool
    {
        return !$this->isPending();
    }

    /**
     * Get operator wallet account
     *
     * @return WalletAccount|null
     */
    public function getOperatorWalletAccount(): ?WalletAccount
    {
        return User::commerceOperator()?->getWalletAccount($this->wallet);
    }

    /**
     * Get fee description
     *
     * @return string
     */
    public function getFeeDescription(): string
    {
        return trans('commerce.fee_description', [
            'name' => $this->account->user->name,
        ]);
    }

    /**
     * Get the channels that model events should broadcast on.
     *
     * @param    $event
     * @return array
     */
    public function broadcastOn($event): array
    {
        return match ($event) {
            'updated' => [new Channel("Public.CommerceTransaction.{$this->id}")],
            default => []
        };
    }

    /**
     * Get the data to broadcast for the model.
     *
     * @param    $event
     * @return array
     */
    public function broadcastWith($event): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'progress' => $this->progress,
            'completed_at' => $this->completed_at,
            'canceled_at' => $this->canceled_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    /**
     * Scope completed transactions
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeIsCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope pending transactions
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeIsPending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope pending overdue transaction
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeIsPendingOverdue(Builder $query): Builder
    {
        return $query->where('expires_at', '<', now()->toDateTimeString())->isPending();
    }

    /**
     * Scope canceled transactions
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeIsCanceled(Builder $query): Builder
    {
        return $query->where('status', 'canceled');
    }

    /**
     * Receiving wallet address
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
     * Associated wallet account
     *
     * @return BelongsTo
     */
    public function walletAccount(): BelongsTo
    {
        return $this->belongsTo(WalletAccount::class, 'wallet_account_id', 'id');
    }

    /**
     * Associated customer
     *
     * @return BelongsTo
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(CommerceCustomer::class, 'commerce_customer_id', 'id');
    }

    /**
     * Related account
     *
     * @return BelongsTo
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(CommerceAccount::class, 'commerce_account_id', 'id');
    }

    /**
     * The action for which this transaction was created
     *
     * @return MorphTo
     */
    public function transactable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * {@inheritDoc}
     */
    public function getWallet(): Wallet
    {
        return $this->walletAccount->wallet;
    }
}
