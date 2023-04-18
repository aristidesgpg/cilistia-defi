<?php

namespace App\Http\Controllers\Admin;

use App\CoinAdapters\Exceptions\AdapterException;
use App\Http\Controllers\Controller;
use App\Http\Resources\WalletResource;
use App\Models\Coin;
use App\Models\Wallet;
use BadMethodCallException;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Throwable;

class WalletController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:manage_wallets');
    }

    /**
     * Get adapters
     *
     * @return array
     */
    public function getAdapters()
    {
        return resolve('coin.manager')->all()->filter(function ($data) {
            return Coin::whereIdentifier($data['identifier'])->doesntExist();
        })->values()->toArray();
    }

    /**
     * Create wallet
     *
     * @param  Request  $request
     *
     * @throws ValidationException
     * @throws Throwable
     */
    public function create(Request $request)
    {
        Auth::user()->acquireLock(function () use ($request) {
            $validated = $request->validate([
                'identifier' => 'required|string|max:10',
                'min_conf' => 'required|integer|min:1|max:99',
            ]);

            resolve('coin.manager')->register(
                data_get($validated, 'identifier'),
                data_get($validated, 'min_conf')
            );
        });
    }

    /**
     * Delete wallet
     *
     * @param $identifier
     */
    public function delete($identifier)
    {
        $coin = Coin::query()
            ->where('identifier', $identifier)
            ->whereDoesntHave('wallet.accounts')
            ->firstOrFail();

        $coin->delete();
    }

    /**
     * Reset webhook
     *
     * @param $identifier
     */
    public function resetWebhook($identifier)
    {
        $wallet = $this->getWallet($identifier);

        $wallet->coin->adapter->resetTransactionWebhook($wallet->resource, $wallet->min_conf);
    }

    /**
     * Consolidate address funds
     *
     * @param  Request  $request
     * @param $identifier
     * @return void
     *
     * @throws Throwable
     */
    public function consolidate(Request $request, $identifier)
    {
        ['address' => $address] = $request->validate([
            'address' => 'required|string|max:250',
        ]);

        try {
            $this->getWallet($identifier)->addresses()
                ->findOrFail($address)->consolidate();
        } catch (AdapterException $exception) {
            abort(422, $exception->getMessage());
        }
    }

    /**
     * Relay transaction
     *
     * @param  Request  $request
     * @param $identifier
     * @return void
     *
     * @throws Exception
     */
    public function relayTransaction(Request $request, $identifier)
    {
        ['hash' => $hash] = $this->validate($request, [
            'hash' => 'required|string|max:250',
        ]);

        try {
            $this->getWallet($identifier)->relayTransaction($hash);
        } catch (AdapterException $exception) {
            abort(422, $exception->getMessage());
        }
    }

    /**
     * Get fee address
     *
     * @param $identifier
     * @return array|void
     */
    public function getFeeAddress($identifier)
    {
        try {
            return ['address' => $this->getWallet($identifier)->getFeeAddress()];
        } catch (BadMethodCallException $exception) {
            abort(422, $exception->getMessage());
        }
    }

    /**
     * Paginate wallets
     *
     * @return AnonymousResourceCollection
     */
    public function paginate(Request $request)
    {
        $query = Wallet::with('statistic')->withCount('accounts');

        $this->filterByCoin($query, $request);

        return WalletResource::collection(paginate($query));
    }

    /**
     * Filter query by coin
     *
     * @param  Builder  $query
     * @param  Request  $request
     */
    protected function filterByCoin(Builder $query, Request $request)
    {
        if ($search = $request->get('searchCoin')) {
            $query->whereHas('coin', function (Builder $query) use ($search) {
                $query->where('name', 'like', "%{$search}%");
            });
        }
    }

    /**
     * Get wallet by identifier
     *
     * @param  string  $identifier
     * @return Wallet
     */
    protected function getWallet(string $identifier): Wallet
    {
        return Wallet::identifier($identifier)->firstOrFail();
    }
}
