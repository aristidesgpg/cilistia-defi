<?php

namespace App\CoinAdapters;

use App\CoinAdapters\Abstracts\AbstractBitGoAdapter;
use Brick\Math\BigDecimal;

class BitcoinCashAdapter extends AbstractBitGoAdapter
{
    /**
     * {@inheritDoc}
     */
    protected string $name = 'Bitcoin Cash';

    /**
     * {@inheritDoc}
     */
    protected string $identifier = 'bch';

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
    protected string $symbol = 'BCH';

    /**
     * {@inheritDoc}
     */
    protected bool $symbolFirst = true;

    /**
     * {@inheritDoc}
     */
    protected string $color = '#80BD3B';

    /**
     * {@inheritDoc}
     */
    protected string $bitgoIdentifier = 'bch';

    /**
     * {@inheritDoc}
     */
    protected string $bitgoTestIdentifier = 'tbch';

    /**
     * {@inheritDoc}
     */
    public function getSvgIcon(): string
    {
        return asset('coin/bch.svg');
    }

    /**
     * {@inheritDoc}
     */
    public function marketId(): string
    {
        return 'bitcoin-cash';
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
