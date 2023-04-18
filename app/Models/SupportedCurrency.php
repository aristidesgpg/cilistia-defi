<?php

namespace App\Models;

use Akaunting\Money\Currency;
use Akaunting\Money\Money;
use App\Casts\MoneyCast;
use App\Models\Support\Cache;
use App\Models\Support\CurrencyAttribute;
use App\Models\Support\StaticCache;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Arr;

class SupportedCurrency extends Model implements CurrencyAttribute
{
    use HasFactory, Cache, StaticCache;

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'code';

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['created_at', 'updated_at'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'min_amount' => MoneyCast::class . ':false',
        'max_amount' => MoneyCast::class . ':false',
        'default' => 'boolean',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'exchange_rate',
        'exchange_type',
        'formatted_min_amount',
        'formatted_max_amount',
    ];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted(): void
    {
        static::saving(MoneyCast::assert('min_amount', 'max_amount'));

        static::creating(function (self $record) {
            $record->code = strtoupper($record->code);
        });

        static::deleting(function (self $record) {
            if ($record->default) {
                throw new Exception('Cannot delete default');
            }
        });
    }

    /**
     * Cast amount as Money object
     *
     * @param $amount
     * @param  bool  $convertToBase
     * @return Money
     */
    public function castMoney($amount, bool $convertToBase = false): Money
    {
        return new Money($amount, currency($this->code), $convertToBase);
    }

    /**
     * Parse amount as money
     *
     * @param $amount
     * @return Money
     */
    public function parseMoney($amount): Money
    {
        return $this->castMoney($amount, true);
    }

    /**
     * Scope default query
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeDefault(Builder $query): Builder
    {
        return $query->oldest()->where('default', true);
    }

    /**
     * Related payment accounts
     *
     * @return HasMany
     */
    public function paymentAccounts(): HasMany
    {
        return $this->hasMany(PaymentAccount::class, 'currency', 'code');
    }

    /**
     * Statistics
     *
     * @return HasOne
     */
    public function statistic(): HasOne
    {
        return $this->hasOne(SupportedCurrencyStatistic::class, 'supported_currency_code', 'code');
    }

    /**
     * Exchange rate
     *
     * @return array|null
     */
    protected function getExchangeRate(): ?array
    {
        return $this->remember('exchange_rate', function () {
            return app('exchanger')->getDriver()->find($this->code);
        });
    }

    /**
     * Get formatted_min_amount
     *
     * @return string|null
     */
    protected function getFormattedMinAmountAttribute(): ?string
    {
        return $this->min_amount?->format();
    }

    /**
     * Get formatted_max_amount
     *
     * @return string|null
     */
    protected function getFormattedMaxAmountAttribute(): ?string
    {
        return $this->max_amount?->format();
    }

    /**
     * Get exchange rate
     *
     * @return float|string|null
     */
    protected function getExchangeRateAttribute(): float|string|null
    {
        return Arr::get($this->getExchangeRate(), 'exchange_rate');
    }

    /**
     * Exchange type, auto|manual
     *
     * @return string|null
     */
    protected function getExchangeTypeAttribute(): string|null
    {
        return Arr::get($this->getExchangeRate(), 'type');
    }

    /**
     * Get currency symbol
     *
     * @return string
     */
    protected function getSymbolAttribute(): string
    {
        return (new Currency($this->code))->getSymbol();
    }

    /**
     * Find currency by code
     *
     * @param  string|null  $code
     * @return SupportedCurrency|null
     */
    public static function findByCode(?string $code): ?SupportedCurrency
    {
        return static::staticRemember("currency:$code", function () use ($code) {
            return $code ? static::find($code) : null;
        });
    }

    /**
     * Get default currency code
     *
     * @return string
     */
    public static function getDefaultCode(): string
    {
        return static::staticRemember('default_currency', function () {
            return static::default()->first()?->code ?: 'USD';
        });
    }

    /**
     * {@inheritDoc}
     */
    public function getCurrency(): Currency
    {
        return currency($this->code);
    }
}
