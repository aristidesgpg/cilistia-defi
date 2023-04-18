<?php

namespace App\Models;

use Akaunting\Money\Currency;
use App\Casts\MoneyCast;
use App\Casts\PurifyHtml;
use App\Models\Support\CurrencyAttribute;
use App\Models\Support\Lock;
use App\Models\Support\Uuid;
use App\Models\Support\ValidateMoney;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\URL;
use UnexpectedValueException;

class CommercePayment extends Model implements CurrencyAttribute
{
    use HasFactory, Uuid, Lock, ValidateMoney;

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'status' => true,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public $fillable = ['type', 'status', 'source', 'title', 'description', 'currency', 'message', 'redirect'];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'amount' => MoneyCast::class,
        'description' => PurifyHtml::class,
        'status' => 'boolean',
        'expires_at' => 'datetime',
    ];

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = ['account', 'supportedCurrency', 'wallets'];

    /**
     * Perform any actions required after the model boots.
     */
    protected static function booted(): void
    {
        static::creating(function (self $payment) {
            if ($payment->isThroughApi() && $payment->type !== 'single') {
                throw new UnexpectedValueException('Payment API must be single.');
            }

            if ($payment->isThroughApi() && !$payment->redirect) {
                throw new UnexpectedValueException('Payment API must redirect.');
            }

            if ($payment->isThroughApi() && !$payment->expires_at) {
                throw new UnexpectedValueException('Payment API must have expiry date.');
            }

            if ($payment->isThroughApi() && !$payment->status) {
                throw new UnexpectedValueException('Payment API must be active.');
            }
        });

        static::updating(function (self $payment) {
            if ($payment->isThroughApi() || $payment->isDirty('source', 'type', 'amount', 'currency')) {
                throw new UnexpectedValueException('You cannot change payment property.');
            }
        });
    }

    /**
     * Payment link
     */
    protected function getLinkAttribute(): string
    {
        return URL::route('page.commerce-payment.view', ['payment' => $this->id]);
    }

    /**
     * Check if payment is available
     */
    public function isAvailable(): bool
    {
        return $this->isActive() && !$this->isComplete();
    }

    /**
     * Check if payment is active
     */
    public function isActive(): bool
    {
        return $this->expires_at ? now()->isBefore($this->expires_at) : $this->status;
    }

    /**
     * Check if payment is complete
     *
     * @return bool
     */
    public function isComplete(): bool
    {
        return match ($this->type) {
            'single' => $this->transactions()->isCompleted()->exists(),
            default => false
        };
    }

    /**
     * Check if payment was created through API
     */
    public function isThroughApi(): bool
    {
        return $this->source === 'api';
    }

    /**
     * Check if payment was created through Web
     */
    public function isThroughWeb(): bool
    {
        return $this->source === 'web';
    }

    /**
     * Get customer by email
     *
     * @param  string  $email
     * @return CommerceCustomer
     */
    public function getCustomerByEmail(string $email): CommerceCustomer
    {
        return $this->account->customers()->whereEmail($email)->firstOrFail();
    }

    /**
     * Get formatted amount
     */
    protected function getFormattedAmountAttribute(): string
    {
        return $this->amount->format();
    }

    /**
     * Scope web payments
     */
    public function scopeIsThroughWeb(Builder $query): Builder
    {
        return $query->where('source', 'web');
    }

    /**
     * Scope api payments
     */
    public function scopeIsThroughApi(Builder $query): Builder
    {
        return $query->where('source', 'api');
    }

    /**
     * Retrieve the model for a bound value.
     *
     * @param  mixed  $value
     * @param  string|null  $field
     */
    public function resolveRouteBinding($value, $field = null): Model
    {
        try {
            return $this->resolveRouteBindingQuery($this, $value, $field)->firstOrFail();
        } catch (ModelNotFoundException) {
            abort(404, trans('commerce.payment_not_found'));
        }
    }

    /**
     * Supported currency
     */
    public function supportedCurrency(): BelongsTo
    {
        return $this->belongsTo(SupportedCurrency::class, 'currency', 'code');
    }

    /**
     * Related commerce account
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(CommerceAccount::class, 'commerce_account_id', 'id');
    }

    /**
     * Accepted wallets for payment
     */
    public function wallets(): BelongsToMany
    {
        return $this->belongsToMany(Wallet::class, 'commerce_payment_wallet', 'commerce_payment_id', 'wallet_id')->withTimestamps();
    }

    /**
     * Get related commerce transactions
     */
    public function transactions(): MorphMany
    {
        return $this->morphMany(CommerceTransaction::class, 'transactable');
    }

    /**
     * Check if payment is deletable
     */
    public function isDeletable(): bool
    {
        return $this->isThroughWeb() && $this->transactions()->isPending()->doesntExist();
    }

    /**
     * {@inheritDoc}
     */
    public function getCurrency(): Currency
    {
        return currency($this->currency);
    }
}
