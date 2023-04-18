<?php

namespace App\Models;

use App\Casts\CoinFormatterCast;
use App\Helpers\CoinFormatter;
use App\Models\Support\Cache;
use App\Models\Support\Lock;
use App\Models\Support\WalletAttribute;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Stake extends Model implements WalletAttribute
{
    use HasFactory, Lock, Cache;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['status'];

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = ['plan', 'walletAccount'];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'value' => CoinFormatterCast::class,
        'yield' => CoinFormatterCast::class,
        'redemption_date' => 'date',
        'days' => 'float',
        'rate' => 'float',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'status' => 'holding',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['coin', 'annual_rate'];

    /**
     * Perform any actions required after the model boots.
     *
     * @return void
     */
    protected static function booted(): void
    {
        static::saving(CoinFormatterCast::assert('value', 'yield'));
    }

    /**
     * Get value object
     *
     * @return CoinFormatter
     */
    public function getValueObject(): CoinFormatter
    {
        return $this->value;
    }

    /**
     * Get yield object
     *
     * @return CoinFormatter
     */
    public function getYieldObject(): CoinFormatter
    {
        return $this->yield;
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
     * Calculate annual rate
     *
     * @return Attribute
     */
    protected function annualRate(): Attribute
    {
        return Attribute::get(function ($value, $attributes) {
            return round(365 * $attributes['rate'] / $attributes['days'], 2);
        });
    }

    /**
     * Get subscription description
     *
     * @param  User  $recipient
     * @return string
     */
    public function getSubscriptionDescription(User $recipient): string
    {
        return match (true) {
            $recipient->is($this->walletAccount->user) => trans('stake.subscription_description'),
            default => trans('stake.operator_subscription_description', [
                'name' => $this->walletAccount->user->name,
            ])
        };
    }

    /**
     * Get redemption description
     *
     * @param  User  $recipient
     * @return string
     */
    public function getRedemptionDescription(User $recipient): string
    {
        return match (true) {
            $recipient->is($this->walletAccount->user) => trans('stake.redemption_description'),
            default => trans('stake.operator_redemption_description', [
                'name' => $this->walletAccount->user->name,
            ])
        };
    }

    /**
     * Related WalletAccount
     *
     * @return BelongsTo
     */
    public function walletAccount(): BelongsTo
    {
        return $this->belongsTo(WalletAccount::class, 'wallet_account_id', 'id');
    }

    /**
     * Related StakePlan
     *
     * @return BelongsTo
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(StakePlan::class, 'plan_id', 'id');
    }

    /**
     * {@inheritDoc}
     */
    public function getWallet(): Wallet
    {
        return $this->walletAccount->wallet;
    }
}
