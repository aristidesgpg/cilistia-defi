<?php

namespace App\Models;

use App\CoinAdapters\Contracts\Adapter;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Cache;
use UnexpectedValueException;

class Coin extends Model
{
    use HasFactory;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'symbol_first' => 'boolean',
        'precision' => 'int',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'price',
        'formatted_price',
        'svg_icon',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'adapter',
    ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id', 'created_at', 'updated_at'];

    /**
     * Perform any actions required after the model boots.
     *
     * @return void
     */
    protected static function booted(): void
    {
        static::retrieved(function (self $coin) {
            if ($coin->getDollarPrice() <= 0) {
                throw new UnexpectedValueException("[{$coin->getSymbol()}] price cannot be zero.");
            }
        });

        static::deleting(function (self $coin) {
            if (!$coin->isDeletable()) {
                throw new Exception("[{$coin->getSymbol()}] cannot be deleted.");
            }
        });
    }

    /**
     * Scope a query of identifier.
     *
     * @param  Builder  $query
     * @param $identifier
     * @return Builder
     */
    public function scopeId(Builder $query, $identifier): Builder
    {
        return $query->where('identifier', $identifier);
    }

    /**
     * Wallet relation
     *
     * @return HasOne
     */
    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class, 'coin_id', 'id');
    }

    /**
     * Get value property
     *
     * @return Attribute
     */
    protected function value(): Attribute
    {
        return Attribute::get(fn () => coin(1, $this, true));
    }

    /**
     * Get coin price
     *
     * @return float|string
     */
    protected function getPriceAttribute(): float|string
    {
        return $this->value->getPrice(defaultCurrency());
    }

    /**
     * Get formatted coin price
     *
     * @return string
     */
    protected function getFormattedPriceAttribute(): string
    {
        return $this->value->getFormattedPrice(defaultCurrency());
    }

    /**
     * Get currency precision
     *
     * @return int
     */
    protected function getCurrencyPrecisionAttribute(): int
    {
        return $this->getCurrencyPrecision();
    }

    /**
     * Get svg icon attribute
     *
     * @return Attribute
     */
    protected function svgIcon(): Attribute
    {
        return Attribute::get(fn () => $this->adapter->getSvgIcon())->shouldCache();
    }

    /**
     * Get coin adapter
     *
     * @return Attribute
     */
    protected function adapter(): Attribute
    {
        return Attribute::get(function ($_, $attributes): Adapter {
            return app('coin.manager')->getAdapter($attributes['identifier']);
        });
    }

    /**
     * Get cached copy of dollar price
     *
     * @return float
     */
    public function getDollarPrice(): float
    {
        $seconds = max(settings()->get('price_cache'), 5);
        $expires = now()->addSeconds($seconds);

        $key = "coin.{$this->identifier}.dollarPrice";

        return Cache::remember($key, $expires, function () {
            return $this->adapter->getDollarPrice();
        });
    }

    /**
     * Get last 24hr change
     *
     * @return float
     */
    public function getDollarPriceChange(): float
    {
        $key = "coin.{$this->identifier}.dollarPriceChange";

        return Cache::remember($key, now()->addHours(), function () {
            return $this->adapter->getDollarPriceChange();
        });
    }

    /**
     * Get market chart
     *
     * @param  string  $interval
     * @return array
     */
    public function getMarketChart(string $interval): array
    {
        $key = "coin.{$this->identifier}.marketChart.{$interval}";

        return Cache::remember($key, now()->add($interval, 1), function () use ($interval) {
            return $this->adapter->getMarketChart($interval);
        });
    }

    /**
     * Get identifier.
     *
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get precision.
     *
     * @return int
     */
    public function getPrecision(): int
    {
        return $this->adapter->getPrecision();
    }

    /**
     * Get currency precision
     *
     * @return int
     */
    public function getCurrencyPrecision(): int
    {
        return $this->adapter->getCurrencyPrecision();
    }

    /**
     * Get subunit.
     *
     * @return string
     */
    public function getBaseUnit(): string
    {
        return $this->adapter->getBaseUnit();
    }

    /**
     * Get symbol.
     *
     * @return string
     */
    public function getSymbol(): string
    {
        return $this->adapter->getSymbol();
    }

    /**
     * Check is symbol should be first.
     *
     * @return bool
     */
    public function isSymbolFirst(): bool
    {
        return $this->adapter->showSymbolFirst();
    }

    /**
     * Prevent removal of coin that has wallet accounts
     *
     * @return bool
     */
    public function isDeletable(): bool
    {
        return $this->wallet()->has('accounts')->doesntExist();
    }

    /**
     * Get prefix.
     *
     * @return string
     */
    public function getPrefix(): string
    {
        return $this->isSymbolFirst() ? $this->getSymbol() : '';
    }

    /**
     * Get suffix.
     *
     * @return string
     */
    public function getSuffix(): string
    {
        return !$this->isSymbolFirst() ? $this->getSymbol() : '';
    }
}
