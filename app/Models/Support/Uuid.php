<?php

namespace App\Models\Support;

use Illuminate\Support\Str;

trait Uuid
{
    /**
     * Generate a primary UUID for the model.
     *
     * @return void
     */
    protected static function bootUuid()
    {
        static::creating(function (self $model) {
            $column = $model->getKeyName();

            if (empty($model->{$column})) {
                $model->{$column} = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the auto-incrementing key type.
     *
     * @return string
     */
    public function getKeyType()
    {
        return 'string';
    }

    /**
     * Get the value indicating whether the IDs are incrementing.
     *
     * @return bool
     */
    public function getIncrementing()
    {
        return false;
    }
}
