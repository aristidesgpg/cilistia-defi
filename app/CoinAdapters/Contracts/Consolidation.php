<?php

namespace App\CoinAdapters\Contracts;

use App\CoinAdapters\Resources\Wallet;

interface Consolidation
{
    /**
     * Consolidate funds from the address
     *
     * @param  Wallet  $wallet
     * @param  string  $address
     * @param  string  $passphrase
     * @return void
     */
    public function consolidate(Wallet $wallet, string $address, string $passphrase): void;
}
