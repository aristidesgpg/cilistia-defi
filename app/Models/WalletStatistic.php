<?php

namespace App\Models;

use App\Casts\CoinFormatterCast;
use App\Helpers\CoinFormatter;
use App\Models\Support\WalletAttribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalletStatistic extends Model implements WalletAttribute
{
    use HasFactory;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id', 'wallet_id', 'created_at', 'updated_at'];

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = ['wallet'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'balance' => CoinFormatterCast::class . ':false',
        'balance_on_trade' => CoinFormatterCast::class . ':false',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'balance_on_trade_price',
        'formatted_balance_on_trade_price',
        'balance_price',
        'formatted_balance_price',
    ];

    /**
     * Perform any actions required after the model boots.
     *
     * @return void
     */
    protected static function booted(): void
    {
        static::saving(CoinFormatterCast::assert('balance', 'balance_on_trade'));
    }

    /**
     * Get balance on trade coin object
     *
     * @return CoinFormatter
     */
    public function getBalanceOnTradeObject(): CoinFormatter
    {
        return $this->balance_on_trade;
    }

    /**
     * Balance on trade price
     *
     * @return float|string
     */
    protected function getBalanceOnTradePriceAttribute(): float|string
    {
        return $this->getBalanceOnTradeObject()->getPrice(defaultCurrency());
    }

    /**
     * Formatted balance on trade
     *
     * @return string
     */
    protected function getFormattedBalanceOnTradePriceAttribute(): string
    {
        return $this->getBalanceOnTradeObject()->getFormattedPrice(defaultCurrency());
    }

    /**
     * Get balance coin object
     *
     * @return CoinFormatter
     */
    public function getBalanceObject(): CoinFormatter
    {
        return $this->balance;
    }

    /**
     * Balance price
     *
     * @return float|string
     */
    protected function getBalancePriceAttribute(): float|string
    {
        return $this->getBalanceObject()->getPrice(defaultCurrency());
    }

    /**
     * Get formatted balance price
     *
     * @return string
     */
    protected function getFormattedBalancePriceAttribute(): string
    {
        return $this->getBalanceObject()->getFormattedPrice(defaultCurrency());
    }

    /**
     * Related wallet
     *
     * @return BelongsTo
     */
    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'wallet_id', 'id');
    }

    /**
     * {@inheritDoc}
     */
    public function getWallet(): Wallet
    {
        return $this->wallet;
    }
}
