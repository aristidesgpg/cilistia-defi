<?php

namespace App\Models\Support;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

trait ValidationRules
{
    /**
     * Get unique rule
     *
     * @param  string  $column
     * @param  null  $ignore
     * @return Unique
     */
    public static function uniqueRule(string $column = 'NULL', $ignore = null): Unique
    {
        $instance = new self;

        if (is_null($ignore)) {
            return Rule::unique($instance->getTable(), $column);
        }

        if ($instance->getKeyType() === 'int') {
            $ignore = (int) $ignore;
        }

        return Rule::unique($instance->getTable(), $column)->ignore($ignore, $instance->getKeyName());
    }

    /**
     * Get unique rule
     *
     * @param  string  $column
     * @return Unique
     */
    public function getUniqueRule(string $column = 'NULL'): Unique
    {
        return Rule::unique($this->getTable(), $column)->ignore($this);
    }
}
