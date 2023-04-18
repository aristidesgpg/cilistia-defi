<?php

namespace App\Models\Support;

use Akaunting\Money\Currency;

interface CurrencyAttribute
{
    /**
     * Get currency attribute
     *
     * @return Currency
     */
    public function getCurrency(): Currency;
}
