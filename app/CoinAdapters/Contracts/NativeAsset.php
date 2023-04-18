<?php

namespace App\CoinAdapters\Contracts;

use App\CoinAdapters\Resources\Wallet;

interface NativeAsset
{
    /**
     * Get fee address to be used for funding transaction
     *
     * @param  Wallet  $wallet
     * @return string
     */
    public function getFeeAddress(Wallet $wallet): string;

    /**
     * Get fee asset identifier
     *
     * @return string
     */
    public function getNativeAssetId(): string;
}
