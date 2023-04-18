<?php

namespace App\Helpers;

use App\Helpers\Settings\BrandSettings;
use App\Helpers\Settings\CommerceSettings;
use App\Helpers\Settings\ThemeSettings;
use App\Helpers\Settings\VerificationSettings;

/**
 * @property BrandSettings $brand
 * @property VerificationSettings $verification
 * @property ThemeSettings $theme
 * @property CommerceSettings $commerce
 */
class Settings
{
    use InteractsWithStore;

    /**
     * Initialize attributes with default value
     *
     * @var array
     */
    protected array $attributes = [
        'user_setup' => true,
        'enable_mail' => false,
        'enable_database' => true,
        'enable_sms' => false,
        'min_payment' => 50,
        'max_payment' => 1000,
        'long_term_period' => 3,
        'price_cache' => 60,
        'price_margin' => 50,
    ];

    /**
     * Define settings' children
     *
     * @var array|string[]
     */
    protected array $children = [
        'brand' => BrandSettings::class,
        'verification' => VerificationSettings::class,
        'theme' => ThemeSettings::class,
        'commerce' => CommerceSettings::class,
    ];
}
