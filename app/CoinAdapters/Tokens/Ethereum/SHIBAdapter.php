<?php

namespace App\CoinAdapters\Tokens\Ethereum;

use App\CoinAdapters\Abstracts\AbstractEthereumTokenAdapter;
use Brick\Math\BigDecimal;

class SHIBAdapter extends AbstractEthereumTokenAdapter
{
    /**
     * {@inheritDoc}
     */
    protected string $name = 'Shiba Inu (ERC)';

    /**
     * {@inheritDoc}
     */
    protected string $identifier = 'shib-erc';

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
    protected int $currencyPrecision = 8;

    /**
     * {@inheritDoc}
     */
    protected string $symbol = 'SHIB';

    /**
     * {@inheritDoc}
     */
    protected bool $symbolFirst = true;

    /**
     * {@inheritDoc}
     */
    protected string $color = '#d67235';

    /**
     * {@inheritDoc}
     */
    protected string $contract = '0x95aD61b0a150d79219dCF64E1E6Cc01f0B64C4cE';

    /**
     * {@inheritDoc}
     */
    protected string $testContract = '0xECf7490A60Ed0e44EAaFA8E27A5AB008D0895271';

    /**
     * {@inheritDoc}
     */
    public function getSvgIcon(): string
    {
        return asset('coin/shib.svg');
    }

    /**
     * {@inheritDoc}
     */
    public function marketId(): string
    {
        return 'shiba-inu';
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
        return (string) BigDecimal::of($this->getBaseUnit())->multipliedBy(100000000000000);
    }
}
