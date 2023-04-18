<?php

namespace App\CoinAdapters\Tokens\Ethereum;

use App\CoinAdapters\Abstracts\AbstractEthereumTokenAdapter;
use Brick\Math\BigDecimal;

class LINKAdapter extends AbstractEthereumTokenAdapter
{
    /**
     * {@inheritDoc}
     */
    protected string $name = 'ChainLink (ERC)';

    /**
     * {@inheritDoc}
     */
    protected string $identifier = 'link-erc';

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
    protected string $symbol = 'LINK';

    /**
     * {@inheritDoc}
     */
    protected bool $symbolFirst = true;

    /**
     * {@inheritDoc}
     */
    protected string $color = '#325ed3';

    /**
     * {@inheritDoc}
     */
    protected string $contract = '0x514910771AF9Ca656af840dff83E8264EcF986CA';

    /**
     * {@inheritDoc}
     */
    protected string $testContract = '0xE20028376B349b36B274C954dEEfA6bFfBee333b';

    /**
     * {@inheritDoc}
     */
    public function getSvgIcon(): string
    {
        return asset('coin/link.svg');
    }

    /**
     * {@inheritDoc}
     */
    public function marketId(): string
    {
        return 'chainlink';
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
