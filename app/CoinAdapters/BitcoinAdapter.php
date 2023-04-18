<?php

namespace App\CoinAdapters;

use App\CoinAdapters\Abstracts\AbstractBitGoAdapter;
use Brick\Math\BigDecimal;

class BitcoinAdapter extends AbstractBitGoAdapter
{
    /**
     * {@inheritDoc}
     */
    protected string $name = 'Bitcoin';

    /**
     * {@inheritDoc}
     */
    protected string $identifier = 'btc';

    /**
     * {@inheritDoc}
     */
    protected string $baseUnit = '100000000';

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
    protected string $symbol = 'BTC';

    /**
     * {@inheritDoc}
     */
    protected bool $symbolFirst = true;

    /**
     * {@inheritDoc}
     */
    protected string $color = '#AD7B16';

    /**
     * {@inheritDoc}
     */
    protected string $bitgoIdentifier = 'btc';

    /**
     * {@inheritdoc}
     */
    protected string $bitgoTestIdentifier = 'tbtc';

    /**
     * {@inheritDoc}
     */
    public function getSvgIcon(): string
    {
        return asset('coin/btc.svg');
    }

    /**
     * {@inheritDoc}
     */
    public function marketId(): string
    {
        return 'bitcoin';
    }

    /**
     * {@inheritDoc}
     */
    public function estimateTransactionFee(string $amount, int $inputs): string
    {
        $bitgoPercent = config('services.bitgo.fee_percent', 0.01);
        $bitgoFee = BigDecimal::of($amount)->multipliedBy($bitgoPercent);

        return (string) BigDecimal::of($this->getFeeEstimate())
            ->multipliedBy(($inputs * 360) + 78 + $inputs)->plus($bitgoFee);
    }

    /**
     * {@inheritDoc}
     */
    public function getMinimumTransferable(): string
    {
        return (string) BigDecimal::of($this->getBaseUnit())->multipliedBy(0.00003);
    }

    /**
     * {@inheritDoc}
     */
    public function getMaximumTransferable(): string
    {
        return (string) BigDecimal::of($this->getBaseUnit())->multipliedBy(1000);
    }
}
