<?php

namespace App\Http\Controllers\WebHook;

use App\CoinAdapters\Resources\Transaction;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessWalletTransaction;
use App\Models\Coin;
use Illuminate\Http\Request;

class CoinController extends Controller
{
    /**
     * Handle transaction webhook for coin
     *
     * @param  Request  $request
     * @param $identifier
     * @return void
     *
     * @throws \Exception
     */
    public function handleTransaction(Request $request, $identifier)
    {
        if ($coin = $this->getCoinByIdentifier($identifier)) {
            $resource = $coin->adapter->handleTransactionWebhook($coin->wallet->resource, $request->all());

            if ($resource instanceof Transaction) {
                ProcessWalletTransaction::dispatch($resource, $coin->wallet);
            }
        }
    }

    /**
     * Get coin model
     *
     * @param $identifier
     * @return Coin
     */
    protected function getCoinByIdentifier($identifier)
    {
        return Coin::where('identifier', $identifier)->first();
    }
}
