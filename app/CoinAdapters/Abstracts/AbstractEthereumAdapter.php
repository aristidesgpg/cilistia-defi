<?php

namespace App\CoinAdapters\Abstracts;

use App\CoinAdapters\Contracts\Consolidation;
use App\CoinAdapters\Contracts\Market;
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

abstract class AbstractEthereumAdapter extends AbstractAdapter implements Consolidation, Market
{
    use CoinCapCoinPrice;

    /**
     * Client URL
     *
     * @var string
     */
    protected string $clientUrl;

    /**
     * Ethereum client request
     *
     * @var PendingRequest
     */
    protected PendingRequest $client;

    /**
     * Ethereum constructor.
     *
     * @return void
     */
    public function __construct()
    {
        $this->client = Http::baseUrl($this->clientUrl)->acceptJson()
            ->throw(fn ($response, $e) => $this->handleError($response, $e));
    }

    /**
     * Get adapter name
     *
     * @return string
     */
    public function getAdapterName(): string
    {
        return "{$this->getName()}";
    }

    /**
     * {@inheritDoc}
     *
     * @throws ValidationException
     */
    public function createWallet(string $passphrase): Wallet
    {
        $response = $this->client->post('wallets', ['password' => $passphrase]);

        return $this->makeWalletResource($response->collect());
    }

    /**
     * {@inheritDoc}
     *
     * @throws ValidationException
     */
    public function createAddress(Wallet $wallet, string $passphrase, string $label = null): Address
    {
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
        $response = $this->client->post("wallets/{$wallet->getId()}/send", [
            'address' => $address,
            'password' => $passphrase,
            'value' => $amount,
        ]);

        return $this->makeTransactionResource($response->collect());
    }

    /**
     * {@inheritDoc}
     */
    public function consolidate(Wallet $wallet, string $address, string $passphrase): void
    {
        $this->client->post("wallets/{$wallet->getId()}/consolidate", [
            'address' => $address,
            'password' => $passphrase,
        ]);
    }

    /**
     * {@inheritDoc}
     *
     * @throws AdapterException
     * @throws ValidationException
     */
    public function getTransaction(Wallet $wallet, string $id): Transaction
    {
        $response = $this->client->get("wallets/{$wallet->getId()}/transactions/$id");

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
        $this->client->post('webhooks/transaction', [
            'wallet' => $wallet->getId(),
            'url' => $this->getTransactionWebhookUrl(),
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
        $gasPrice = $this->client->get('gas-price')->json('gasPrice');

        return (string) BigDecimal::of($gasPrice)->multipliedBy(21000)
            ->multipliedBy(($inputs * 1.5) + 1);
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
