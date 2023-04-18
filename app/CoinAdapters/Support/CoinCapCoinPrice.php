<?php

namespace App\CoinAdapters\Support;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

trait CoinCapCoinPrice
{
    /**
     * Market chart interval
     *
     * @var int[]
     */
    protected array $coinCapIntervals = [
        'hour' => 'm1',
        'day' => 'm15',
        'week' => 'h2',
        'month' => 'h6',
        'year' => 'd1',
    ];

    /**
     * Get coincap's client
     *
     * @return PendingRequest
     */
    protected function coinCapClient(): PendingRequest
    {
        $client = Http::baseUrl('https://api.coincap.io/v2/')->acceptJson()->throw();

        return tap($client, function ($client) {
            if ($token = config('services.coincap.key')) {
                $client->withToken($token);
            }
        });
    }

    /**
     * {@inheritDoc}
     */
    public function getDollarPrice(): float
    {
        return (float) $this->coinCapClient()->get("assets/{$this->marketId()}")->json('data.priceUsd');
    }

    /**
     * {@inheritDoc}
     */
    public function getDollarPriceChange(): float
    {
        return (float) $this->coinCapClient()->get("assets/{$this->marketId()}")->json('data.changePercent24Hr');
    }

    /**
     * {@inheritDoc}
     */
    public function getMarketChart(string $interval): array
    {
        $response = $this->coinCapClient()->get("assets/{$this->marketId()}/history", [
            'start' => now()->sub($interval, 1)->getPreciseTimestamp(3),
            'end' => now()->getPreciseTimestamp(3),
            'interval' => data_get($this->coinCapIntervals, $interval, 'd1'),
        ]);

        return $response->collect('data')->map(function ($content) {
            return [$content['time'], $content['priceUsd']];
        })->toArray();
    }
}
