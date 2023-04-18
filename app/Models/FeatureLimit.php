<?php

namespace App\Models;

use Akaunting\Money\Money;
use App\Models\Support\Cache;
use App\Models\Support\StaticCache;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeatureLimit extends Model
{
    use HasFactory, Cache, StaticCache;

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'name';

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
    protected $guarded = ['name', 'created_at', 'updated_at'];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'title',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'unverified_limit' => 'float',
        'basic_limit' => 'float',
        'advanced_limit' => 'float',
    ];

    /**
     * Get title attribute
     *
     * @return string
     */
    protected function getTitleAttribute(): string
    {
        return trans("feature.$this->name");
    }

    /**
     * Check if feature is enabled for user
     *
     * @param  User  $user
     * @return bool
     */
    public function isEnabledFor(User $user): bool
    {
        return $this->getLimit($user) > 0;
    }

    /**
     * Get user's limit
     *
     * @param  User  $user
     * @return float
     */
    public function getLimit(User $user): float
    {
        $status = $user->verification->getLevel();

        return $this->{"{$status}_limit"} ?: 0;
    }

    /**
     * Get total usage
     *
     * @param  User  $user
     * @return float
     */
    public function getUsage(User $user): float
    {
        return $this->remember("usage.$user->id", function () use ($user) {
            return $this->usages()->whereDate('created_at', '>=', now()->startOf($this->period))
                ->where('user_id', $user->id)->sum('value');
        });
    }

    /**
     * Available
     *
     * @param  User  $user
     * @return float
     */
    public function getAvailable(User $user): float
    {
        return max($this->getLimit($user) - $this->getUsage($user), 0);
    }

    /**
     * Check availability
     *
     * @param  float|Money  $value
     * @param  User  $user
     * @return bool
     */
    public function checkAvailability(float|Money $value, User $user): bool
    {
        return $this->getAvailable($user) >= $this->parseValue($value);
    }

    /**
     * Set feature usage
     *
     * @param  float|Money  $value
     * @param  User  $user
     */
    public function setUsage(float|Money $value, User $user)
    {
        $this->usages()->create([
            'value' => $this->parseValue($value),
            'user_id' => $user->id,
        ]);
    }

    /**
     * Validate limit value
     *
     * @param  float|Money  $value
     * @return float
     */
    protected function parseValue(float|Money $value): float
    {
        if (is_float($value)) {
            return $value;
        }

        $precision = $value->getCurrency()->getPrecision();
        $currency = currency('USD', max($precision, 2));

        return app('exchanger')->convert($value, $currency)->getValue();
    }

    /**
     * Authorize user for this feature
     *
     * @param  User  $user
     * @param  float|Money  $value
     * @return void
     */
    public function authorize(User $user, float|Money $value): void
    {
        if (!$this->isEnabledFor($user)) {
            abort(403, trans('feature.disabled'));
        }

        if (!$this->checkAvailability($value, $user)) {
            abort(403, trans('feature.limit_reached'));
        }
    }

    /**
     * Feature usage logs
     *
     * @return HasMany
     */
    public function usages(): HasMany
    {
        return $this->hasMany(FeatureUsage::class, 'feature_name', 'name');
    }

    /**
     * Bank deposit
     *
     * @return self
     */
    public static function paymentsDeposit(): FeatureLimit
    {
        return static::staticRemember('payments_deposit', function () {
            return self::findOrFail('payments_deposit');
        });
    }

    /**
     * Bank Withdrawal
     *
     * @return self
     */
    public static function paymentsWithdrawal(): FeatureLimit
    {
        return static::staticRemember('payments_withdrawal', function () {
            return self::findOrFail('payments_withdrawal');
        });
    }

    /**
     * Wallet Exchange
     *
     * @return self
     */
    public static function walletExchange(): FeatureLimit
    {
        return static::staticRemember('wallet_exchange', function () {
            return self::findOrFail('wallet_exchange');
        });
    }

    /**
     * Giftcard Trade
     *
     * @return self
     */
    public static function giftcardTrade(): FeatureLimit
    {
        return static::staticRemember('giftcard_trade', function () {
            return self::findOrFail('giftcard_trade');
        });
    }
}
