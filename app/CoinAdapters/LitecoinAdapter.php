<?php

namespace App\CoinAdapters;

use App\CoinAdapters\Abstracts\AbstractBitGoAdapter;
use Brick\Math\BigDecimal;

class LitecoinAdapter extends AbstractBitGoAdapter
{
    /**
     * {@inheritDoc}
     */
    protected string $name = 'Litecoin';

    /**
     * {@inheritDoc}
     */
    protected string $identifier = 'ltc';

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
    protected string $symbol = 'LTC';

    /**
     * {@inheritDoc}
     */
    protected bool $symbolFirst = true;

    /**
     * {@inheritDoc}
     */
    protected string $color = '#B8B8B8';

    /**
     * {@inheritDoc}
     */
    protected string $bitgoIdentifier = 'ltc';

    /**
     * {@inheritDoc}
     */
    protected string $bitgoTestIdentifier = 'tltc';

    /**
     * {@inheritDoc}
     */
    public function getSvgIcon(): string
    {
        return asset('coin/ltc.svg');
    }

    /**
     * {@inheritDoc}
     */
    public function marketId(): string
    {
        return 'litecoin';
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
        return (string) BigDecimal::of($this->getBaseUnit())->multipliedBy(0.002);
    }

    /**
     * {@inheritDoc}
     */
    public function getMaximumTransferable(): string
    {
        return (string) BigDecimal::of($this->getBaseUnit())->multipliedBy(1000);
    }
}
