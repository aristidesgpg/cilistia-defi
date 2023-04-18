<?php

namespace App\CoinAdapters\Support;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

trait MoralisTokenPrice
{
    /**
     * Moralis chain
     *
     * @var string
     */
    protected string $moralisChain;

    /**
     * Get moralis client
     *
     * @return PendingRequest
     */
    protected function moralisClient(): PendingRequest
    {
        return Http::baseUrl('https://deep-index.moralis.io/api/v2/')
            ->withHeaders(['X-API-Key' => config('services.moralis.key')])
            ->acceptJson()->throw();
    }

    /**
     * {@inheritDoc}
     *
     * @throws RequestException
     */
    public function getDollarPrice(): float
    {
        return (float) $this->moralisClient()->get("erc20/{$this->getContract()}/price", [
            'chain' => $this->moralisChain,
        ])->json('usdPrice', 0);
    }

    /**
     * {@inheritDoc}
     */
    public function getDollarPriceChange(): float
    {
        return 0;
    }

    /**
     * {@inheritDoc}
     */
    public function getMarketChart(string $interval): array
    {
        return [];
    }
}
