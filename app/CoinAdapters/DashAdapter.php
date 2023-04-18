<?php

namespace App\CoinAdapters;

use App\CoinAdapters\Abstracts\AbstractBitGoAdapter;
use Brick\Math\BigDecimal;

class DashAdapter extends AbstractBitGoAdapter
{
    /**
     * {@inheritDoc}
     */
    protected string $name = 'Dash';

    /**
     * {@inheritDoc}
     */
    protected string $identifier = 'dash';

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
    protected string $symbol = 'DASH';

    /**
     * {@inheritDoc}
     */
    protected bool $symbolFirst = true;

    /**
     * {@inheritDoc}
     */
    protected string $color = '#008DE4';

    /**
     * {@inheritDoc}
     */
    protected string $bitgoIdentifier = 'dash';

    /**
     * {@inheritDoc}
     */
    protected string $bitgoTestIdentifier = 'tdash';

    /**
     * {@inheritDoc}
     */
    public function getSvgIcon(): string
    {
        return asset('coin/dash.svg');
    }

    /**
     * {@inheritDoc}
     */
    public function marketId(): string
    {
        return 'dash';
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
