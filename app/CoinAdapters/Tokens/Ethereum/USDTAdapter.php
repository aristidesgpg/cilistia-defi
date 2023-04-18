<?php

namespace App\CoinAdapters\Tokens\Ethereum;

use App\CoinAdapters\Abstracts\AbstractEthereumTokenAdapter;
use Brick\Math\BigDecimal;

class USDTAdapter extends AbstractEthereumTokenAdapter
{
    /**
     * {@inheritDoc}
     */
    protected string $name = 'Tether USD (ERC)';

    /**
     * {@inheritDoc}
     */
    protected string $identifier = 'usdt-erc';

    /**
     * {@inheritDoc}
     */
    protected string $baseUnit = '1000000';

    /**
     * {@inheritDoc}
     */
    protected int $precision = 6;

    /**
     * {@inheritDoc}
     */
    protected int $currencyPrecision = 4;

    /**
     * {@inheritDoc}
     */
    protected string $symbol = 'USDT';

    /**
     * {@inheritDoc}
     */
    protected bool $symbolFirst = true;

    /**
     * {@inheritDoc}
     */
    protected string $color = '#4fb095';

    /**
     * {@inheritDoc}
     */
    protected string $contract = '0xdAC17F958D2ee523a2206206994597C13D831ec7';

    /**
     * {@inheritDoc}
     */
    protected string $testContract = '0x7219f70E6725876D3d5062f52683261e64949CD8';

    /**
     * {@inheritDoc}
     */
    public function getSvgIcon(): string
    {
        return asset('coin/usdt.svg');
    }

    /**
     * {@inheritDoc}
     */
    public function marketId(): string
    {
        return 'tether';
    }

    /**
     * {@inheritDoc}
     */
    public function getMinimumTransferable(): string
    {
        return (string) BigDecimal::of($this->getBaseUnit());
    }

    /**
     * {@inheritDoc}
     */
    public function getMaximumTransferable(): string
    {
        return (string) BigDecimal::of($this->getBaseUnit())->multipliedBy(1000000);
    }
}
