<?php

namespace App\CoinAdapters\Tokens\Binance;

use App\CoinAdapters\Abstracts\AbstractBinanceTokenAdapter;
use Brick\Math\BigDecimal;

class BUSDAdapter extends AbstractBinanceTokenAdapter
{
    /**
     * {@inheritDoc}
     */
    protected string $name = 'Binance USD (BEP)';

    /**
     * {@inheritDoc}
     */
    protected string $identifier = 'busd-bep';

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
    protected int $currencyPrecision = 4;

    /**
     * {@inheritDoc}
     */
    protected string $symbol = 'BUSD';

    /**
     * {@inheritDoc}
     */
    protected bool $symbolFirst = true;

    /**
     * {@inheritDoc}
     */
    protected string $color = '#ecb919';

    /**
     * {@inheritDoc}
     */
    protected string $contract = '0xe9e7CEA3DedcA5984780Bafc599bD69ADd087D56';

    /**
     * {@inheritDoc}
     */
    protected string $testContract = '0x3eC4A443CcD1eF1033F0D556ed8cfb3247D93033';

    /**
     * {@inheritDoc}
     */
    public function getSvgIcon(): string
    {
        return asset('coin/busd.svg');
    }

    /**
     * {@inheritDoc}
     */
    public function marketId(): string
    {
        return 'binance-usd';
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
