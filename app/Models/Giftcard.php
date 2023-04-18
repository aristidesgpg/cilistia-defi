<?php

namespace App\Models;

use Akaunting\Money\Currency;
use Akaunting\Money\Money;
use App\Casts\MoneyCast;
use App\Models\Support\CurrencyAttribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Giftcard extends Model implements CurrencyAttribute
{
    use HasFactory;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id', 'created_at', 'updated_at'];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'stock',
        'formatted_value',
    ];

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = ['brand', 'supportedCurrency'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'value' => MoneyCast::class . ':false',
    ];

    /**
     * Perform any actions required after the model boots.
     *
     * @return void
     */
    protected static function booted(): void
    {
        static::saving(MoneyCast::assert('value'));
    }

    /**
     * Get path for thumbnail
     *
     * @return string
     */
    public function path(): string
    {
        return "giftcards/{$this->id}";
    }

    /**
     * Set currency attribute
     *
     * @param  string  $value
     * @return void
     */
    protected function setCurrencyAttribute(string $value): void
    {
        $this->attributes['currency'] = strtoupper($value);
    }

    /**
     * Get logo url
     *
     * @param $value
     * @return string|null
     */
    protected function getThumbnailAttribute($value): ?string
    {
        return $value ? url($value) : null;
    }

    /**
     * Value Object
     *
     * @return Money
     */
    public function getValueObject(): Money
    {
        return $this->value;
    }

    /**
     * Formatted Value
     *
     * @return string
     */
    protected function getFormattedValueAttribute(): string
    {
        return $this->getValueObject()->format();
    }

    /**
     * Total amount in stock
     *
     * @return int
     */
    protected function getStockAttribute(): int
    {
        return $this->contents()->doesntHave('buyer')->count();
    }

    /**
     * Get price in another currency
     *
     * @param  User|null  $user
     * @return Money
     */
    public function getPrice(?User $user): Money
    {
        $currency = new Currency($user?->currency ?: defaultCurrency());

        return app('exchanger')->convert($this->getValueObject(), $currency);
    }

    /**
     * Get related brand
     *
     * @return BelongsTo
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(GiftcardBrand::class, 'brand_id', 'id');
    }

    /**
     * Giftcard contents
     *
     * @return HasMany
     */
    public function contents(): HasMany
    {
        return $this->hasMany(GiftcardContent::class, 'giftcard_id', 'id');
    }

    /**
     * Supported currency
     *
     * @return BelongsTo
     */
    public function supportedCurrency(): BelongsTo
    {
        return $this->belongsTo(SupportedCurrency::class, 'currency', 'code');
    }

    /**
     * {@inheritDoc}
     */
    public function getCurrency(): Currency
    {
        return currency($this->currency);
    }
}
