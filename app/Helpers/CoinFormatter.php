<?php

namespace App\Helpers;

use Akaunting\Money\Money;
use App\Models\Coin;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use InvalidArgumentException;

class CoinFormatter
{
    /**
     * Coin model
     *
     * @var Coin
     */
    protected Coin $coin;

    /**
     * Base amount
     *
     * @var BigDecimal
     */
    protected BigDecimal $amount;

    /**
     * Coin value
     *
     * @var float
     */
    protected float $value;

    /**
     * Rounding mode
     *
     * @var int
     */
    protected int $rounding = RoundingMode::HALF_DOWN;

    /**
     * Coin constructor.
     *
     * @param $amount
     * @param  Coin  $coin
     * @param  bool  $convertToBase
     */
    public function __construct($amount, Coin $coin, bool $convertToBase = false)
    {
        $this->coin = $coin;
        $this->amount = $this->parseAmount($amount, $convertToBase);
    }

    /**
     * Parse amount.
     *
     * @param $amount
     * @param  bool  $convertToBase
     * @return BigDecimal
     */
    protected function parseAmount($amount, bool $convertToBase = false): BigDecimal
    {
        return $this->convertToBase(BigDecimal::of($amount), $convertToBase)->toScale(0, $this->rounding);
    }

    /**
     * Convert amount to base unit.
     *
     * @param  BigDecimal  $amount
     * @param  bool  $convertToBase
     * @return BigDecimal
     */
    protected function convertToBase(BigDecimal $amount, bool $convertToBase = false): BigDecimal
    {
        if (!$convertToBase) {
            return $amount;
        }

        return $amount->multipliedBy($this->coin->getBaseUnit());
    }

    /**
     * Less than comparison
     *
     * @param  CoinFormatter  $that
     * @return bool
     */
    public function lessThan(self $that): bool
    {
        $this->assertSameCoin($that);

        return $this->amount->isLessThan($that->getAmount());
    }

    /**
     * Less than or equal comparison
     *
     * @param  CoinFormatter  $that
     * @return bool
     */
    public function lessThanOrEqual(self $that): bool
    {
        $this->assertSameCoin($that);

        return $this->amount->isLessThanOrEqualTo($that->getAmount());
    }

    /**
     * Greater than comparison
     *
     * @param  CoinFormatter  $that
     * @return bool
     */
    public function greaterThan(self $that): bool
    {
        $this->assertSameCoin($that);

        return $this->amount->isGreaterThan($that->getAmount());
    }

    /**
     * Greater than or equal comparison
     *
     * @param  CoinFormatter  $that
     * @return bool
     */
    public function greaterThanOrEqual(self $that): bool
    {
        $this->assertSameCoin($that);

        return $this->amount->isGreaterThanOrEqualTo($that->getAmount());
    }

    /**
     * Check for zero value
     *
     * @return bool
     */
    public function isZero(): bool
    {
        return $this->amount->isZero();
    }

    /**
     * Check for negative or zero value
     *
     * @return bool
     */
    public function isNegativeOrZero(): bool
    {
        return $this->amount->isNegativeOrZero();
    }

    /**
     * Check for negative value
     *
     * @return bool
     */
    public function isNegative(): bool
    {
        return $this->amount->isNegative();
    }

    /**
     * Check for positive value
     *
     * @return bool
     */
    public function isPositive(): bool
    {
        return $this->amount->isPositive();
    }

    /**
     * Add operation
     *
     * @param  CoinFormatter  $that
     * @return CoinFormatter
     */
    public function add(self $that): CoinFormatter
    {
        $this->assertSameCoin($that);

        return new self($this->amount->plus($that->getAmount()), $this->coin);
    }

    /**
     * Subtract operation
     *
     * @param  CoinFormatter  $that
     * @return CoinFormatter
     */
    public function subtract(self $that): CoinFormatter
    {
        $this->assertSameCoin($that);

        return new self($this->amount->minus($that->getAmount()), $this->coin);
    }

    /**
     * Multiply operation
     *
     * @param  float  $multiplier
     * @return CoinFormatter
     */
    public function multiply(float $multiplier): CoinFormatter
    {
        return new self($this->amount->multipliedBy($multiplier), $this->coin);
    }

    /**
     * Assert that operation is done on the same Coin object
     *
     * @param  CoinFormatter  $that
     */
    protected function assertSameCoin(self $that): void
    {
        if ($this->coin->isNot($that->getCoin())) {
            throw new InvalidArgumentException('Different base coin');
        }
    }

    /**
     * Get Coin Model
     *
     * @return Coin
     */
    public function getCoin(): Coin
    {
        return $this->coin;
    }

    /**
     * Get amount in Base Unit.
     *
     * @return string
     */
    public function getAmount(): string
    {
        return (string) $this->amount;
    }

    /**
     * Get value as float.
     *
     * @return float
     */
    public function getValue(): float
    {
        if (!isset($this->value)) {
            $this->value = $this->amount->exactlyDividedBy($this->coin->getBaseUnit())
                ->toScale($this->coin->getPrecision(), $this->rounding)->toFloat();
        }

        return $this->value;
    }

    /**
     * Get underlying dollar price
     *
     * @return float
     */
    public function getDollarPrice(): float
    {
        return $this->coin->getDollarPrice();
    }

    /**
     * Calculate Price
     *
     * @param  float|null  $price
     * @return float
     */
    public function calcPrice(float $price = null): float
    {
        if (is_null($price)) {
            $price = $this->coin->getDollarPrice();
        }

        return $this->getValue() * $price;
    }

    /**
     * Get price
     *
     * @param  string  $currency
     * @param  float|null  $dollarPrice
     * @param  bool  $format
     * @return float|string
     */
    public function getPrice(string $currency = 'USD', float $dollarPrice = null, bool $format = false): float|string
    {
        return convertCurrency($this->calcPrice($dollarPrice), 'USD', strtoupper($currency), $format, $this->coin->currency_precision);
    }

    /**
     * Get formatted price
     *
     * @param  string  $currency
     * @param  float|null  $dollarPrice
     * @return string
     */
    public function getFormattedPrice(string $currency = 'USD', float $dollarPrice = null): string
    {
        return convertCurrency($this->calcPrice($dollarPrice), 'USD', strtoupper($currency), true, $this->coin->currency_precision);
    }

    /**
     * Get price as money object
     *
     * @param  string  $currency
     * @param  float|null  $dollarPrice
     * @return Money
     */
    public function getPriceAsMoney(string $currency = 'USD', float $dollarPrice = null): Money
    {
        return app('exchanger')->convert(
            money($this->calcPrice($dollarPrice), 'USD', true, $this->coin->currency_precision),
            currency($currency, $this->coin->currency_precision)
        );
    }
}
