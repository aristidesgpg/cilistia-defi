<?php

namespace App\Helpers;

use App\CoinAdapters\Contracts\Adapter;
use App\Models\Coin;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class CoinManager
{
    /**
     * The array of added adapters
     *
     * @var array
     */
    protected array $adapters = [];

    /**
     * Add adapters to list
     *
     * @param  string|Adapter  $adapter
     * @return CoinManager
     */
    public function addAdapter(Adapter|string $adapter): static
    {
        if (!is_a($adapter, Adapter::class, true)) {
            throw new InvalidArgumentException('Unrecognized adapter instance.');
        }

        if (is_string($adapter)) {
            $adapter = new $adapter;
        }

        $this->adapters[$adapter->getIdentifier()] = $adapter;

        return $this;
    }

    /**
     * Get coin adapter
     *
     * @param  string  $identifier
     * @return Adapter
     */
    public function getAdapter(string $identifier): Adapter
    {
        if (!$this->hasAdapter($identifier)) {
            throw new InvalidArgumentException("Adapter [$identifier] does not exist.");
        }

        return $this->adapters[$identifier];
    }

    /**
     * Check if coin manager has adapter
     *
     * @param  string  $identifier
     * @return bool
     */
    public function hasAdapter(string $identifier): bool
    {
        return Arr::has($this->adapters, $identifier);
    }

    /**
     * Register adapter into the database
     *
     * @param  string  $identifier
     * @param  int  $minConf
     * @return void
     */
    public function register(string $identifier, int $minConf = 3): void
    {
        $adapter = $this->getAdapter($identifier);

        DB::transaction(function () use ($adapter, $minConf) {
            $coin = Coin::updateOrCreate([
                'identifier' => $adapter->getIdentifier(),
            ], [
                'name' => $adapter->getName(),
                'base_unit' => $adapter->getBaseUnit(),
                'precision' => $adapter->getPrecision(),
                'symbol' => $adapter->getSymbol(),
                'symbol_first' => $adapter->showSymbolFirst(),
                'color' => $adapter->getColor(),
            ]);

            $coin->wallet()->firstOr(function () use ($adapter, $minConf, $coin) {
                $passphrase = Str::random(100);

                $resource = $adapter->createWallet($passphrase);
                $adapter->setTransactionWebhook($resource, $minConf);

                return $coin->wallet()->create([
                    'min_conf' => $minConf,
                    'passphrase' => $passphrase,
                    'resource' => $resource,
                ]);
            });
        });
    }

    /**
     * Get list of adapters
     *
     * @return Collection
     */
    public function all(): Collection
    {
        return collect($this->adapters)->map(fn (Adapter $adapter) => [
            'name' => $adapter->getAdapterName(),
            'identifier' => $adapter->getIdentifier(),
            'symbol' => $adapter->getSymbol(),
        ]);
    }
}
