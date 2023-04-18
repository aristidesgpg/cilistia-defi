<?php

namespace App\CoinAdapters\Abstracts;

abstract class AbstractBinanceTokenAdapter extends AbstractEthereumTokenAdapter
{
    /**
     * {@inheritDoc}
     */
    protected string $clientUrl = 'http://binance-api:7000/';

    /**
     * {@inheritDoc}
     */
    public function getNativeAssetId(): string
    {
        return 'bnb';
    }
}
