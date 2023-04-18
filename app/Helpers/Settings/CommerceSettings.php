<?php

namespace App\Helpers\Settings;

use App\Helpers\InteractsWithStore;

class CommerceSettings
{
    use InteractsWithStore;

    /**
     * Initialize attributes with default value
     *
     * @var array
     */
    protected array $attributes = [
        'pending_transactions' => 10,
        'transaction_interval' => 10,
    ];
}
