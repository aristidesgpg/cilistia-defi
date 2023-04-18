<?php

namespace App\Http\Controllers;

use App\Exceptions\LockException;
use App\Http\Requests\VerifiedRequest;
use App\Http\Resources\TransferRecordResource;
use App\Http\Resources\WalletAccountResource;
use App\Http\Resources\WalletAddressResource;
use App\Models\Wallet;
use App\Models\WalletAccount;
use App\Rules\Decimal;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\ValidationException;

class WalletAccountController extends Controller
{
    /**
     * Get all wallet accounts
     *
     * @return AnonymousResourceCollection
     */
    public function all()
    {
        $accounts = $this->walletAccounts()->get()
            ->tap(function ($accounts) use (&$totalAvailablePrice) {
                $totalAvailablePrice = $accounts->sum('available_price');
            })
            ->each(function ($account) use ($totalAvailablePrice) {
                $divisor = $totalAvailablePrice > 0 ? $totalAvailablePrice : 1;
                $quota = ceil(($account->available_price * 100) / $divisor);
                $account->setAttribute('available_price_quota', $quota);
            });

        return WalletAccountResource::collection($accounts);
    }

    /**
     * Get total available price
     *
     * @return JsonResponse
     */
    public function totalAvailablePrice()
    {
        $price = $this->walletAccounts()->get()->sum('available_price');

        $formattedPrice = formatCurrency($price, Auth::user()->currency);

        return response()->json([
            'formatted_price' => $formattedPrice,
            'price' => $price,
        ]);
    }

    /**
     * Get aggregate price
     *
     * @return JsonResponse
     */
    public function aggregatePrice()
    {
        $accounts = $this->walletAccounts()->get();

        $available = $accounts->sum('available_price');
        $formattedAvailable = formatCurrency($available, Auth::user()->currency);

        $balanceOnTrade = $accounts->sum('balance_on_trade_price');
        $formattedBalanceOnTrade = formatCurrency($balanceOnTrade, Auth::user()->currency);

        $balance = $accounts->sum('balance_price');
        $formattedBalance = formatCurrency($balance, Auth::user()->currency);

        return response()->json([
            'available' => $available,
            'balance_on_trade' => $balanceOnTrade,
            'balance' => $balance,
            'formatted_available' => $formattedAvailable,
            'formatted_balance_on_trade' => $formattedBalanceOnTrade,
            'formatted_balance' => $formattedBalance,
            'currency' => Auth::user()->currency,
        ]);
    }

    /**
     * Estimate transaction fee
     *
     * @param  Request  $request
     * @param $id
     * @return JsonResponse
     *
     * @throws ValidationException
     * @throws \Exception
     */
    public function estimateFee(Request $request, $id)
    {
        $account = $this->walletAccounts()->findOrFail($id);

        $this->validate($request, [
            'amount' => ['nullable', 'numeric', 'min:0', new Decimal],
            'address' => ['nullable', 'string', 'min:0', 'max:250'],
        ]);

        $currency = $account->user->currency;
        $amount = $account->parseCoin($request->get('amount') ?: 0);
        $address = $request->get('address');

        if ($address && $account->wallet->hasInternal($address)) {
            return response()->json(null);
        }

        $withdrawalFee = $account->getWithdrawalFee($amount);
        $transactionFee = $account->getTransactionFee($amount);
        $estimateFee = $transactionFee->add($withdrawalFee);

        return response()->json([
            'price' => $estimateFee->getFormattedPrice($currency),
            'value' => $estimateFee->getValue(),
        ]);
    }

    /**
     * Send amount
     *
     * @param  VerifiedRequest  $request
     * @param $id
     * @return TransferRecordResource
     *
     * @throws LockException
     */
    public function send(VerifiedRequest $request, $id)
    {
        return Auth::user()->acquireLock(function () use ($request, $id) {
            $account = $this->walletAccounts()->findOrFail($id);

            $data = $this->validate($request, [
                'amount' => [
                    'required', 'numeric',
                    "min:{$account->min_transferable->getValue()}",
                    "max:{$account->max_transferable->getValue()}",
                ],
                'address' => ['required'],
            ]);

            $record = $account->send($data['amount'], $data['address']);

            return TransferRecordResource::make($record);
        });
    }

    /**
     * Get the latest address
     *
     * @param $id
     * @return WalletAddressResource
     */
    public function latestAddress($id)
    {
        $account = $this->walletAccounts()->findOrFail($id);

        $address = $account->standardAddresses()->firstOrFail();

        return WalletAddressResource::make($address);
    }

    /**
     * Generate address
     *
     * @param $id
     * @return WalletAddressResource
     *
     * @throws \Exception
     */
    public function generateAddress($id)
    {
        $account = $this->walletAccounts()->findOrFail($id);

        return $account->acquireLock(function () use ($account) {
            $latest = $account->standardAddresses()->first();

            if ($latest?->total_received->isNegativeOrZero()) {
                abort(403, trans('wallet.last_address_not_used'));
            }

            $address = $account->createAddress();

            return WalletAddressResource::make($address);
        });
    }

    /**
     * Get authenticated user's wallet account
     *
     * @return WalletAccount|\Illuminate\Database\Eloquent\Relations\HasMany
     */
    private function walletAccounts()
    {
        return Auth::user()->walletAccounts();
    }
}
