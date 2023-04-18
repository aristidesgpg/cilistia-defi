<?php

namespace App\Models\Support;

use App\Models\Wallet;

interface WalletAttribute
{
    /**
     * Get parent wallet
     *
     * @return Wallet
     */
    public function getWallet(): Wallet;
}
