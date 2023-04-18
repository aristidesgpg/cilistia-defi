<?php

namespace App\CoinAdapters;

use App\CoinAdapters\Abstracts\AbstractEthereumAdapter;
use Brick\Math\BigDecimal;

class BinanceAdapter extends AbstractEthereumAdapter
{
    /**
     * {@inheritDoc}
     */
    protected string $name = 'Binance Coin';

    /**
     * {@inheritDoc}
     */
    protected string $identifier = 'bnb';

    /**
     * {@inheritDoc}
     */
    protected string $baseUnit = '1000000000000000000';

    /**
     * {@inheritDoc}
     */
    protected int $precision = 8;

    /**
     * {@inheritDoc}
     */
    protected int $currencyPrecision = 2;

    /**
     * {@inheritDoc}
     */
    protected string $symbol = 'BNB';

    /**
     * {@inheritDoc}
     */
    protected bool $symbolFirst = true;

    /**
     * {@inheritDoc}
     */
    protected string $color = '#f0bc3d';

    /**
     * {@inheritDoc}
     */
    protected string $clientUrl = 'http://binance-api:7000/';

    /**
     * {@inheritDoc}
     */
    public function getSvgIcon(): string
    {
        return asset('coin/bnb.svg');
    }

    /**
     * {@inheritDoc}
     */
    public function marketId(): string
    {
        return 'binance-coin';
    }

    /**
     * {@inheritDoc}
     */
    public function getMinimumTransferable(): string
    {
        return (string) BigDecimal::of($this->getBaseUnit())->multipliedBy(0.0001);
    }

    /**
     * {@inheritDoc}
     */
    public function getMaximumTransferable(): string
    {
        return (string) BigDecimal::of($this->getBaseUnit())->multipliedBy(1000);
    }
}
