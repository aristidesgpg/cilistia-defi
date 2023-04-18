<?php

namespace App\Http\Controllers;

use App\Http\Requests\VerifiedRequest;
use App\Http\Resources\ExchangeTradeResource;
use App\Models\Earning;
use App\Models\ExchangeTrade;
use App\Models\FeatureLimit;
use App\Models\Module;
use App\Models\PaymentAccount;
use App\Models\User;
use App\Models\WalletAccount;
use App\Rules\Decimal;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ExchangeTradeController extends Controller
{
    /**
     * Calculate sell order
     *
     * @param  Request  $request
     * @return array
     *
     * @throws ValidationException
     */
    public function calculateSell(Request $request)
    {
        $account = $this->findAccount($request);
        $validated = $this->validateCalculateRequest($request);

        $amount = $account->parseCoin($validated['amount']);
        $exchangeFee = $account->getSellExchangeFee($amount);

        $deductible = $amount->add($exchangeFee)->getValue();
        $fee = $exchangeFee->getValue();

        return compact('deductible', 'fee');
    }

    /**
     * Calculate buy order
     *
     * @param  Request  $request
     * @return array
     *
     * @throws ValidationException
     */
    public function calculateBuy(Request $request)
    {
        $account = $this->findAccount($request);
        $validated = $this->validateCalculateRequest($request);

        $amount = $account->parseCoin($validated['amount']);
        $exchangeFee = $account->getBuyExchangeFee($amount);

        $currency = $request->user()->currency;
        $deductible = $amount->add($exchangeFee)->getPrice($currency);
        $fee = $exchangeFee->getPrice($currency);

        return compact('deductible', 'fee');
    }

    /**
     * Sell coin
     *
     * @param  VerifiedRequest  $request
     * @return mixed
     *
     * @throws AuthorizationException
     */
    public function sell(VerifiedRequest $request)
    {
        return Auth::user()->acquireLock(function (User $user) use ($request) {
            $account = $this->findAccount($request);
            $validated = $this->validateCreateRequest($request, $account);

            $record = $account->acquireLockOrAbort(function (WalletAccount $walletAccount) use ($validated, $user) {
                $paymentAccount = $user->getPaymentAccount();
                $operator = Module::exchange()->getOperatorFor($user);

                $amount = $walletAccount->parseCoin($validated['amount']);
                $exchangeFee = $walletAccount->getSellExchangeFee($amount);

                $deductible = $amount->add($exchangeFee);

                if ($walletAccount->getAvailableObject()->lessThan($deductible)) {
                    return abort(422, trans('wallet.insufficient_available'));
                }

                $payment = $paymentAccount->parseMoney($amount->getPrice($paymentAccount->currency));

                FeatureLimit::walletExchange()->authorize($user, $payment);

                $exchangeTrade = new ExchangeTrade();
                $exchangeTrade->type = 'sell';
                $exchangeTrade->wallet_value = $deductible;
                $exchangeTrade->fee_value = $exchangeFee;
                $exchangeTrade->payment_value = $payment;
                $exchangeTrade->dollar_price = $amount->getDollarPrice();
                $exchangeTrade->status = 'completed';
                $exchangeTrade->completed_at = now();

                $exchangeTrade->trader()->associate($operator);
                $exchangeTrade->paymentAccount()->associate($paymentAccount);
                $exchangeTrade->walletAccount()->associate($walletAccount);

                return DB::transaction(function () use ($exchangeTrade, $user) {
                    $walletValue = $exchangeTrade->getWalletValueObject();
                    $feeValue = $exchangeTrade->getFeeValueObject();
                    $paymentValue = $exchangeTrade->getPaymentValueObject();

                    $traderWalletAccount = $exchangeTrade->walletAccount->parseTarget($exchangeTrade->trader);
                    $targetDescription = $exchangeTrade->getTransferDescription($exchangeTrade->walletAccount->user);
                    $traderDescription = $exchangeTrade->getTransferDescription($exchangeTrade->trader);

                    $exchangeTrade->paymentAccount->credit($paymentValue, $targetDescription);
                    $traderWalletAccount->credit($walletValue, $traderDescription, $exchangeTrade->dollar_price);
                    $exchangeTrade->walletAccount->debit($walletValue, $targetDescription, $exchangeTrade->dollar_price);
                    Earning::saveCoin($feeValue, $traderDescription, $exchangeTrade->trader);
                    FeatureLimit::walletExchange()->setUsage($paymentValue, $user);

                    return tap($exchangeTrade)->save();
                });
            });

            return ExchangeTradeResource::make($record);
        });
    }

    /**
     * Buy coin
     *
     * @param  VerifiedRequest  $request
     * @return mixed
     *
     * @throws AuthorizationException
     */
    public function buy(VerifiedRequest $request)
    {
        return Auth::user()->acquireLock(function (User $user) use ($request) {
            $paymentAccount = $user->getPaymentAccount();

            $record = $paymentAccount->acquireLockOrAbort(function (PaymentAccount $paymentAccount) use ($request, $user) {
                $walletAccount = $this->findAccount($request);
                $validated = $this->validateCreateRequest($request, $walletAccount);
                $operator = Module::exchange()->getOperatorFor($user);

                $amount = $walletAccount->parseCoin($validated['amount']);
                $exchangeFee = $walletAccount->getBuyExchangeFee($amount);

                $deductible = $paymentAccount->parseMoney(
                    $amount->add($exchangeFee)->getPrice($paymentAccount->currency)
                );

                if ($paymentAccount->getAvailableObject()->lessThan($deductible)) {
                    return abort(422, trans('payment.insufficient_balance'));
                }

                FeatureLimit::walletExchange()->authorize($user, $deductible);

                $exchangeTrade = new ExchangeTrade();
                $exchangeTrade->type = 'buy';
                $exchangeTrade->wallet_value = $amount;
                $exchangeTrade->payment_value = $deductible;
                $exchangeTrade->fee_value = $exchangeFee;
                $exchangeTrade->dollar_price = $amount->getDollarPrice();
                $exchangeTrade->status = 'pending';

                $exchangeTrade->trader()->associate($operator);
                $exchangeTrade->paymentAccount()->associate($paymentAccount);
                $exchangeTrade->walletAccount()->associate($walletAccount);

                return tap($exchangeTrade)->save();
            });

            return ExchangeTradeResource::make($record);
        });
    }

    /**
     * Paginate exchange trades
     *
     * @param  Request  $request
     * @return AnonymousResourceCollection
     */
    public function paginate(Request $request)
    {
        $query = Auth::user()->exchangeTrades()->latest();

        if ($account = $request->get('account')) {
            $query = $query->where('wallet_accounts.id', $account);
        }

        return ExchangeTradeResource::collection(paginate($query));
    }

    /**
     * Validate calculate request
     *
     * @param  Request  $request
     * @return array
     */
    protected function validateCalculateRequest(Request $request)
    {
        return $request->validate([
            'amount' => ['required', 'numeric', 'min:0', new Decimal],
        ]);
    }

    /**
     * Validate action request
     *
     * @param  VerifiedRequest  $request
     * @param  WalletAccount  $account
     * @return array
     */
    protected function validateCreateRequest(VerifiedRequest $request, WalletAccount $account)
    {
        return $request->validate([
            'amount' => [
                'required', 'numeric',
                "min:{$account->min_transferable->getValue()}",
                "max:{$account->max_transferable->getValue()}",
            ],
        ]);
    }

    /**
     * Get wallet account
     *
     * @param  Request  $request
     * @return WalletAccount
     */
    protected function findAccount(Request $request)
    {
        return Auth::user()->walletAccounts()->findOrFail((int) $request->get('account'));
    }
}
