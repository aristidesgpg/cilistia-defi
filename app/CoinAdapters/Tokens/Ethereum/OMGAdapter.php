<?php

namespace App\CoinAdapters\Tokens\Ethereum;

use App\CoinAdapters\Abstracts\AbstractEthereumTokenAdapter;
use App\CoinAdapters\Support\CoinCapCoinPrice;
use Brick\Math\BigDecimal;

class OMGAdapter extends AbstractEthereumTokenAdapter
{
    use CoinCapCoinPrice;

    /**
     * {@inheritDoc}
     */
    protected string $name = 'OmiseGo (ERC)';

    /**
     * {@inheritDoc}
     */
    protected string $identifier = 'omg-erc';

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
    protected string $symbol = 'OMG';

    /**
     * {@inheritDoc}
     */
    protected bool $symbolFirst = true;

    /**
     * {@inheritDoc}
     */
    protected string $color = '#0f0f0f';

    /**
     * {@inheritDoc}
     */
    protected string $contract = '0xd26114cd6EE289AccF82350c8d8487fedB8A0C07';

    /**
     * {@inheritDoc}
     */
    protected string $testContract = '0x30B469D650FEdb1dE4972eee6887d980B204D94d';

    /**
     * {@inheritDoc}
     */
    public function getSvgIcon(): string
    {
        return asset('coin/omg.svg');
    }

    /**
     * {@inheritDoc}
     */
    public function marketId(): string
    {
        return 'omg';
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
