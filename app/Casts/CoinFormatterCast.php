<?php

namespace App\Casts;

use App\Helpers\CoinFormatter;
use App\Models\Support\WalletAttribute;
use Closure;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Database\Eloquent\SerializesCastableAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use UnexpectedValueException;

class CoinFormatterCast implements CastsAttributes, SerializesCastableAttributes
{
    /**
     * Determine whether to cast from base unit or not
     *
     * @var bool
     */
    protected bool $inBaseUnit;

    /**
     * Create a new cast class instance.
     *
     * @param  bool|string  $inBaseUnit
     */
    public function __construct(bool|string $inBaseUnit = true)
    {
        $this->inBaseUnit = is_string($inBaseUnit) ? $inBaseUnit !== 'false' : $inBaseUnit;
    }

    /**
     * Cast the given value.
     *
     * @param  Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return CoinFormatter|null
     */
    public function get($model, string $key, $value, array $attributes): ?CoinFormatter
    {
        if (is_null($value)) {
            return null;
        }

        if (!$model instanceof WalletAttribute) {
            throw new InvalidArgumentException('Missing wallet attribute.');
        }

        if (!$this->inBaseUnit) {
            $value = floatval($value);
        }

        return $model->getWallet()->castCoin($value, !$this->inBaseUnit);
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return string|float|null
     */
    public function set($model, string $key, $value, array $attributes): string|float|null
    {
        if (is_null($value)) {
            return null;
        }

        if (!$value instanceof CoinFormatter) {
            throw new InvalidArgumentException('Attribute is not a CoinFormatter object');
        }

        return $this->inBaseUnit ? $value->getAmount() : $value->getValue();
    }

    /**
     * Get the serialized representation of the value.
     *
     * @param  Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return float|null
     */
    public function serialize($model, string $key, $value, array $attributes): ?float
    {
        if (!$value instanceof CoinFormatter) {
            return $value;
        }

        return $value->getValue();
    }

    /**
     * Assert that attributes has the same base coin
     *
     * @param  array|string  $attributes
     * @return Closure
     */
    public static function assert(array|string $attributes): Closure
    {
        $attributes = is_array($attributes) ? $attributes : func_get_args();

        return function (Model $model) use ($attributes) {
            if (!$model instanceof WalletAttribute) {
                throw new InvalidArgumentException('Missing wallet attribute.');
            }

            $wallet = $model->getWallet();

            collect($attributes)->filter(fn ($name) => $model->isDirty($name))->each(function ($name) use ($model, $wallet) {
                $attribute = $model->getAttribute($name);

                if (is_null($attribute)) {
                    return null;
                }

                if (!$attribute instanceof CoinFormatter) {
                    throw new UnexpectedValueException('Attribute is not a CoinFormatter object');
                }

                if ($attribute->getCoin()->isNot($wallet->coin)) {
                    throw new UnexpectedValueException('Different base coin.');
                }
            });
        };
    }
}
