<?php

namespace App\CoinAdapters\Tokens\Binance;

use App\CoinAdapters\Abstracts\AbstractBinanceTokenAdapter;
use App\CoinAdapters\Support\CoinCapCoinPrice;
use Brick\Math\BigDecimal;

class SFMAdapter extends AbstractBinanceTokenAdapter
{
    use CoinCapCoinPrice;

    /**
     * {@inheritDoc}
     */
    protected string $name = 'SafeMoon (BEP)';

    /**
     * {@inheritDoc}
     */
    protected string $identifier = 'sfm-bep';

    /**
     * {@inheritDoc}
     */
    protected string $baseUnit = '1000000000';

    /**
     * {@inheritDoc}
     */
    protected int $precision = 9;

    /**
     * {@inheritDoc}
     */
    protected int $currencyPrecision = 7;

    /**
     * {@inheritDoc}
     */
    protected string $symbol = 'SFM';

    /**
     * {@inheritDoc}
     */
    protected bool $symbolFirst = true;

    /**
     * {@inheritDoc}
     */
    protected string $color = '#88b1ac';

    /**
     * {@inheritDoc}
     */
    protected string $contract = '0x42981d0bfbAf196529376EE702F2a9Eb9092fcB5';

    /**
     * {@inheritDoc}
     */
    protected string $testContract = '0x5061ee7Cb3A22149D4755f20954eb511433c443C';

    /**
     * {@inheritDoc}
     */
    public function getSvgIcon(): string
    {
        return asset('coin/sfm.svg');
    }

    /**
     * {@inheritDoc}
     */
    public function marketId(): string
    {
        return 'safemoon';
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
        return (string) BigDecimal::of($this->getBaseUnit())->multipliedBy(1000000000000);
    }
}
