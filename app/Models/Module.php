<?php

namespace App\Models;

use App\Models\Support\StaticCache;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Module extends Model
{
    use HasFactory, StaticCache;

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
    protected $guarded = ['name', 'operator_id', 'created_at', 'updated_at'];

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = ['operator'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'status' => 'boolean',
    ];

    /**
     * Check if module is enabled
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->status;
    }

    /**
     * Get operator for User
     *
     * @param  User  $user
     * @return User
     *
     * @throws AuthorizationException
     */
    public function getOperatorFor(User $user): User
    {
        if (!$this->operator) {
            throw new AuthorizationException(trans('common.operator_unavailable'));
        }

        if ($this->operator->is($user)) {
            throw new AuthorizationException(trans('common.operator_cannot_trade'));
        }

        return $this->operator;
    }

    /**
     * Check if operator exists
     *
     * @return bool
     */
    public function hasOperator(): bool
    {
        return $this->operator()->exists();
    }

    /**
     * Get operator's account.
     *
     * @return BelongsTo
     */
    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operator_id', 'id')->role(Role::operator());
    }

    /**
     * Get staking module
     *
     * @return self
     */
    public static function staking(): Module
    {
        return static::staticRemember('staking', function () {
            return self::findOrFail('staking');
        });
    }

    /**
     * Get exchange module
     *
     * @return self
     */
    public static function exchange(): Module
    {
        return static::staticRemember('exchange', function () {
            return self::findOrFail('exchange');
        });
    }

    /**
     * Get payment module
     *
     * @return Module
     */
    public static function payment(): Module
    {
        return static::staticRemember('payment', function () {
            return self::findOrFail('payment');
        });
    }

    /**
     * Get commerce module
     *
     * @return Module
     */
    public static function commerce(): Module
    {
        return static::staticRemember('commerce', function () {
            return self::findOrFail('commerce');
        });
    }

    /**
     * Get peer module
     *
     * @return self
     */
    public static function peer(): Module
    {
        return static::staticRemember('peer', function () {
            return self::findOrFail('peer');
        });
    }

    /**
     * Get giftcard module
     *
     * @return self
     */
    public static function giftcard(): Module
    {
        return static::staticRemember('giftcard', function () {
            return self::findOrFail('giftcard');
        });
    }

    /**
     * Get wallet module
     *
     * @return self
     */
    public static function wallet(): Module
    {
        return static::staticRemember('wallet', function () {
            return self::findOrFail('wallet');
        });
    }
}
