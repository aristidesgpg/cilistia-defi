<?php

namespace App\Models\Support;

use App\Casts\MoneyCast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait ValidateMoney
{
    /**
     * Validate Money attributes
     *
     * @return void
     */
    protected static function bootValidateMoney(): void
    {
        static::saving(function (Model $model) {
            $attributes = collect($model->getCasts())->filter(function ($cast) {
                return Str::startsWith($cast, MoneyCast::class);
            });

            MoneyCast::assert($attributes->keys()->toArray())($model);
        });
    }
}
