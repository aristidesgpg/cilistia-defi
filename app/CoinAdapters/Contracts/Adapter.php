<?php

namespace App\CoinAdapters\Contracts;

use App\CoinAdapters\Resources\Address;
use App\CoinAdapters\Resources\Transaction;
use App\CoinAdapters\Resources\Wallet;

interface Adapter
{
    /**
     * Get adapter name
     *
     * @return string
     */
    public function getAdapterName(): string;

    /**
     * Get coin name
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Get coin identifier
     *
     * @return string
     */
    public function getIdentifier(): string;

    /**
     * Get coin unit
     *
     * @return string
     */
    public function getBaseUnit(): string;

    /**
     * Get coin precision
     *
     * @return int
     */
    public function getPrecision(): int;

    /**
     * Get currency precision
     *
     * @return int
     */
    public function getCurrencyPrecision(): int;

    /**
     * Get coin symbol
     *
     * @return string
     */
    public function getSymbol(): string;

    /**
     * Show symbol first
     *
     * @return bool
     */
    public function showSymbolFirst(): bool;

    /**
     * Get color used for highlighting
     *
     * @return string
     */
    public function getColor(): string;

    /**
     * Get svg icon url
     *
     * @return string
     */
    public function getSvgIcon(): string;

    /**
     * Generate wallet
     *
     * @param  string  $passphrase
     * @return Wallet
     */
    public function createWallet(string $passphrase): Wallet;

    /**
     * Create address for users
     *
     * @param  Wallet  $wallet
     * @param  string  $passphrase
     * @param  string|null  $label
     * @return Address
     */
    public function createAddress(Wallet $wallet, string $passphrase, string $label = null): Address;

    /**
     * Send transaction
     *
     * @param  Wallet  $wallet
     * @param  string  $address
     * @param  string  $amount
     * @param  string  $passphrase
     * @return Transaction
     */
    public function send(Wallet $wallet, string $address, string $amount, string $passphrase): Transaction;

    /**
     * Get wallet transaction by id
     *
     * @param  Wallet  $wallet
     * @param  string  $id
     * @return Transaction
     */
    public function getTransaction(Wallet $wallet, string $id): Transaction;

    /**
     * Handle coin webhook and return the transaction data
     *
     * @param  Wallet  $wallet
     * @param  array  $payload
     * @return Transaction|null
     */
    public function handleTransactionWebhook(Wallet $wallet, array $payload): ?Transaction;

    /**
     * Add webhook for wallet.
     *
     * @param  Wallet  $wallet
     * @param  int  $minConf
     * @return void
     */
    public function setTransactionWebhook(Wallet $wallet, int $minConf = 3): void;

    /**
     * Reset webhook for wallet.
     *
     * @param  Wallet  $wallet
     * @param  int  $minConf
     * @return void
     */
    public function resetTransactionWebhook(Wallet $wallet, int $minConf = 3): void;

    /**
     * Get the dollar price
     *
     * @return float
     */
    public function getDollarPrice(): float;

    /**
     * Get last 24hr change
     *
     * @return float
     */
    public function getDollarPriceChange(): float;

    /**
     * Get market chart
     *
     * @param  string  $interval
     * @return array
     */
    public function getMarketChart(string $interval): array;

    /**
     * Estimate the transaction fee
     *
     * @param  string  $amount
     * @param  int  $inputs
     * @return string
     */
    public function estimateTransactionFee(string $amount, int $inputs): string;

    /**
     * Get minimum transferable amount.
     *
     * @return string
     */
    public function getMinimumTransferable(): string;

    /**
     * Get maximum transferable amount.
     *
     * @return string
     */
    public function getMaximumTransferable(): string;
}
