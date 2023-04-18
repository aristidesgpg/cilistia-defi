<?php

namespace App\CoinAdapters\Abstracts;

use App\CoinAdapters\Contracts\Adapter;

abstract class AbstractAdapter implements Adapter
{
    /**
     * Coin name
     *
     * @var string
     */
    protected string $name;

    /**
     * Coin identifier
     *
     * @var string
     */
    protected string $identifier;

    /**
     * Coin base unit
     *
     * @var string
     */
    protected string $baseUnit;

    /**
     * Coin value precision
     *
     * @var int
     */
    protected int $precision;

    /**
     * Coin price precision
     *
     * @var int
     */
    protected int $currencyPrecision;

    /**
     * Coin's symbol
     *
     * @var string
     */
    protected string $symbol;

    /**
     * Should show coin symbol first or not
     *
     * @var bool
     */
    protected bool $symbolFirst;

    /**
     * Coin highlight color
     *
     * @var string
     */
    protected string $color;

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * {@inheritDoc}
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * {@inheritDoc}
     */
    public function getBaseUnit(): string
    {
        return $this->baseUnit;
    }

    /**
     * {@inheritDoc}
     */
    public function getPrecision(): int
    {
        return $this->precision;
    }

    /**
     * {@inheritDoc}
     */
    public function getCurrencyPrecision(): int
    {
        return $this->currencyPrecision;
    }

    /**
     * {@inheritDoc}
     */
    public function getSymbol(): string
    {
        return $this->symbol;
    }

    /**
     * {@inheritDoc}
     */
    public function showSymbolFirst(): bool
    {
        return $this->symbolFirst;
    }

    /**
     * {@inheritDoc}
     */
    public function getColor(): string
    {
        return $this->color;
    }

    /**
     * Get transaction webhook url
     *
     * @return string
     */
    protected function getTransactionWebhookUrl(): string
    {
        return $this->route('webhook.coin.transaction', ['identifier' => $this->getIdentifier()]);
    }

    /**
     * Generate route
     *
     * @param  string  $name
     * @param  array|null  $parameters
     * @return string
     */
    protected function route(string $name, array $parameters = null): string
    {
        $baseUrl = app()->environment('production') ? url('/') : config('coin.webhook_url');

        return rtrim($baseUrl, '/') . route($name, $parameters, false);
    }
}
