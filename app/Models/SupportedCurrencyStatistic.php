<?php

namespace App\Models;

use Akaunting\Money\Currency;
use App\Casts\MoneyCast;
use App\Models\Support\CurrencyAttribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportedCurrencyStatistic extends Model implements CurrencyAttribute
{
    use HasFactory;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id', 'supported_currency_code', 'created_at', 'updated_at'];

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = ['supportedCurrency'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'balance' => MoneyCast::class . ':false',
        'balance_on_trade' => MoneyCast::class . ':false',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'formatted_balance',
        'formatted_balance_on_trade',
    ];

    /**
     * Perform any actions required after the model boots.
     *
     * @return void
     */
    protected static function booted(): void
    {
        static::saving(MoneyCast::assert('balance', 'balance_on_trade'));
    }

    /**
     * Formatted balance
     *
     * @return string
     */
    protected function getFormattedBalanceAttribute(): string
    {
        return $this->balance->format();
    }

    /**
     * Formatted balance on trade
     *
     * @return string
     */
    protected function getFormattedBalanceOnTradeAttribute(): string
    {
        return $this->balance_on_trade->format();
    }

    /**
     * Related supported currency
     *
     * @return BelongsTo
     */
    public function supportedCurrency(): BelongsTo
    {
        return $this->belongsTo(SupportedCurrency::class, 'supported_currency_code', 'code');
    }

    /**
     * {@inheritDoc}
     */
    public function getCurrency(): Currency
    {
        return currency($this->supportedCurrency->code);
    }
}
