<?php

namespace App\Models\Support;

use App\Casts\CoinFormatterCast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait ValidateCoinFormatter
{
    /**
     * Validate CoinFormatter attributes
     *
     * @return void
     */
    protected static function bootValidateCoinFormatter(): void
    {
        static::saving(function (Model $model) {
            $attributes = collect($model->getCasts())->filter(function ($cast) {
                return Str::startsWith($cast, CoinFormatterCast::class);
            });

            CoinFormatterCast::assert($attributes->keys()->toArray())($model);
        });
    }
}
