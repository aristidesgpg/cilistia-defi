<?php

namespace App\CoinAdapters\Abstracts;

use App\CoinAdapters\Contracts\Consolidation;
use App\CoinAdapters\Contracts\Market;
use App\CoinAdapters\Contracts\NativeAsset;
use App\CoinAdapters\Exceptions\AdapterException;
use App\CoinAdapters\Exceptions\ValidationException;
use App\CoinAdapters\Resources\Address;
use App\CoinAdapters\Resources\Transaction;
use App\CoinAdapters\Resources\Wallet;
use App\CoinAdapters\Support\CoinCapCoinPrice;
use Brick\Math\BigDecimal;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

abstract class AbstractEthereumTokenAdapter extends AbstractAdapter implements Consolidation, NativeAsset, Market
{
    use CoinCapCoinPrice;

    /**
     * Client URL
     *
     * @var string
     */
    protected string $clientUrl = 'http://ethereum-api:7000/';

    /**
     * Mainnet contract address
     *
     * @var string
     */
    protected string $contract;

    /**
     * Testnet contract address
     *
     * @var string
     */
    protected string $testContract;

    /**
     * Ethereum client request
     *
     * @var PendingRequest
     */
    protected PendingRequest $client;

    /**
     * Ethereum token constructor
     *
     * @return void
     */
    public function __construct()
    {
        $this->client = Http::baseUrl($this->clientUrl)->acceptJson()
            ->throw(fn ($response, $e) => $this->handleError($response, $e));
    }

    /**
     * Get contract address
     *
     * @return string
     */
    public function getContract(): string
    {
        if (config('coin.testnet')) {
            return $this->testContract;
        } else {
            return $this->contract;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getAdapterName(): string
    {
        return "{$this->getName()}";
    }

    /**
     * Check token's availability
     *
     * @return void
     *
     * @throws AdapterException
     */
    public function assertAvailable(): void
    {
        $response = $this->client->get("tokens/{$this->getContract()}/status");

        $context = $this->getIdentifier();

        if ($response->failed() || !$response->json('status')) {
            throw new AdapterException("[$context] Token is unavailable.");
        }
    }

    /**
     * {@inheritDoc}
     *
     * @throws AdapterException
     * @throws ValidationException
     */
    public function createWallet(string $passphrase): Wallet
    {
        $this->assertAvailable();

        $response = $this->client->post('wallets', ['password' => $passphrase]);

        return $this->makeWalletResource($response->collect());
    }

    /**
     * {@inheritDoc}
     *
     * @throws AdapterException
     * @throws ValidationException
     */
    public function createAddress(Wallet $wallet, string $passphrase, string $label = null): Address
    {
        $this->assertAvailable();

        $response = $this->client->post("wallets/{$wallet->getId()}/addresses", ['password' => $passphrase]);

        return $this->makeAddressResource($response->collect()->put('label', $label));
    }

    /**
     * {@inheritDoc}
     *
     * @throws AdapterException
     * @throws ValidationException
     */
    public function send(Wallet $wallet, string $address, string $amount, string $passphrase): Transaction
    {
        $this->assertAvailable();

        $response = $this->client->post("wallets/{$wallet->getId()}/tokens/{$this->getContract()}/send", [
            'address' => $address,
            'password' => $passphrase,
            'value' => $amount,
        ]);

        return $this->makeTransactionResource($response->collect());
    }

    /**
     * {@inheritDoc}
     *
     * @throws AdapterException
     */
    public function consolidate(Wallet $wallet, string $address, string $passphrase): void
    {
        $this->assertAvailable();

        $this->client->post("wallets/{$wallet->getId()}/tokens/{$this->getContract()}/consolidate", [
            'address' => $address,
            'password' => $passphrase,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function getFeeAddress(Wallet $wallet): string
    {
        $response = $this->client->get("wallets/{$wallet->getId()}");

        return $response->json('address');
    }

    /**
     * {@inheritDoc}
     */
    public function getNativeAssetId(): string
    {
        return 'eth';
    }

    /**
     * {@inheritDoc}
     *
     * @throws AdapterException
     * @throws ValidationException
     */
    public function getTransaction(Wallet $wallet, string $id): Transaction
    {
        $response = $this->client->get("wallets/{$wallet->getId()}/tokens/{$this->getContract()}/transfer/$id");

        return $this->makeTransactionResource($response->collect());
    }

    /**
     * {@inheritDoc}
     *
     * @throws AdapterException
     * @throws ValidationException
     */
    public function handleTransactionWebhook(Wallet $wallet, array $payload): ?Transaction
    {
        $hash = data_get($payload, 'hash');

        return $hash ? $this->getTransaction($wallet, $hash) : null;
    }

    /**
     * {@inheritDoc}
     */
    public function setTransactionWebhook(Wallet $wallet, int $minConf = 3): void
    {
        $this->client->post('webhooks/token-transfer', [
            'wallet' => $wallet->getId(),
            'url' => $this->getTransactionWebhookUrl(),
            'contract' => $this->getContract(),
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function resetTransactionWebhook(Wallet $wallet, int $minConf = 3): void
    {
        $this->setTransactionWebhook($wallet, $minConf);
    }

    /**
     * {@inheritDoc}
     */
    public function estimateTransactionFee(string $amount, int $inputs): string
    {
        return (string) BigDecimal::of(0);
    }

    /**
     * Make wallet resource
     *
     * @param  Collection  $data
     * @return Wallet
     *
     * @throws ValidationException
     */
    protected function makeWalletResource(Collection $data): Wallet
    {
        return new Wallet([
            'id' => $data->get('id'),
            'address' => $data->get('address'),
            'data' => $data->toArray(),
        ]);
    }

    /**
     * Make address resource
     *
     * @param  Collection  $data
     * @return Address
     *
     * @throws ValidationException
     */
    protected function makeAddressResource(Collection $data): Address
    {
        return new Address([
            'id' => $data->get('id'),
            'address' => $data->get('address'),
            'label' => $data->get('label'),
        ]);
    }

    /**
     * Make transaction resource
     *
     * @param  Collection  $data
     * @return Transaction
     *
     * @throws AdapterException
     * @throws ValidationException
     */
    protected function makeTransactionResource(Collection $data): Transaction
    {
        $date = $this->parseDate($data->get('timestamp'));

        return new Transaction([
            'id' => $data->get('hash'),
            'hash' => $data->get('hash'),
            'type' => $data->get('type'),
            'from' => $data->get('from'),
            'to' => $data->get('to'),
            'value' => $data->get('value'),
            'confirmations' => $data->get('confirmations') ?: 0,
            'date' => $date->toDateTimeString(),
        ]);
    }

    /**
     * Parse date from timestamp
     *
     * @param $timestamp
     * @return Carbon
     */
    protected function parseDate($timestamp): Carbon
    {
        return $timestamp ? Carbon::createFromTimestamp($timestamp) : Carbon::now();
    }

    /**
     * Handle request error
     *
     * @param  Response  $response
     * @param  RequestException  $e
     * @return void
     *
     * @throws AdapterException
     */
    protected function handleError(Response $response, RequestException $e): void
    {
        if ($message = $response->json('message')) {
            $context = $this->getIdentifier();
            throw new AdapterException("[$context] $message", $response->status());
        }
    }
}
