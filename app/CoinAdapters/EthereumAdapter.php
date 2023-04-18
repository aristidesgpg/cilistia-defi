<?php

namespace App\CoinAdapters;

use App\CoinAdapters\Abstracts\AbstractEthereumAdapter;
use Brick\Math\BigDecimal;

class EthereumAdapter extends AbstractEthereumAdapter
{
    /**
     * {@inheritDoc}
     */
    protected string $name = 'Ethereum';

    /**
     * {@inheritDoc}
     */
    protected string $identifier = 'eth';

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
    protected string $symbol = 'ETH';

    /**
     * {@inheritDoc}
     */
    protected bool $symbolFirst = true;

    /**
     * {@inheritDoc}
     */
    protected string $color = '#627EEA';

    /**
     * {@inheritDoc}
     */
    protected string $clientUrl = 'http://ethereum-api:7000/';

    /**
     * {@inheritDoc}
     */
    public function getSvgIcon(): string
    {
        return asset('coin/eth.svg');
    }

    /**
     * {@inheritDoc}
     */
    public function marketId(): string
    {
        return 'ethereum';
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
