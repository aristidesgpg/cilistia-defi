<?php

namespace App\CoinAdapters\Support;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

trait CoinGeckoCoinPrice
{
    /**
     * Initialize CoinGecko
     *
     * @return PendingRequest
     */
    protected function coinGeckoClient(): PendingRequest
    {
        if ($key = config('services.coingecko.key')) {
            $client = Http::baseUrl('https://pro-api.coingecko.com/api/v3/')->withHeaders(['x-cg-pro-api-key' => $key]);
        } else {
            $client = Http::baseUrl('https://api.coingecko.com/api/v3/');
        }

        return $client->acceptJson()->throw();
    }

    /**
     * {@inheritDoc}
     */
    public function getDollarPrice(): float
    {
        return (float) $this->coinGeckoClient()->get('simple/price', [
            'ids' => $this->marketId(),
            'vs_currencies' => 'usd',
        ])->collect($this->marketId())->get('usd');
    }

    /**
     * {@inheritDoc}
     */
    public function getDollarPriceChange(): float
    {
        return (float) $this->coinGeckoClient()->get('simple/price', [
            'ids' => $this->marketId(),
            'include_24hr_change' => 'true',
            'vs_currencies' => 'usd',
        ])->collect($this->marketId())->get('usd_24h_change');
    }

    /**
     * {@inheritDoc}
     */
    public function getMarketChart(string $interval): array
    {
        return $this->coinGeckoClient()->get("coins/{$this->marketId()}/market_chart/range", [
            'from' => now()->sub($interval, 1)->getPreciseTimestamp(0),
            'to' => now()->getPreciseTimestamp(0),
            'vs_currency' => 'usd',
        ])->json('prices', []);
    }
}
