<?php

namespace App\CoinAdapters\Contracts;

interface Market
{
    /**
     * Market identifier
     *
     * @return string
     */
    public function marketId(): string;
}
