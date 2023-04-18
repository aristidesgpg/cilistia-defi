<?php

namespace App\Http\Controllers;

use App\Http\Resources\BankAccountResource;
use App\Http\Resources\PaymentAccountResource;
use App\Http\Resources\PaymentTransactionResource;
use App\Models\FeatureLimit;
use App\Models\PaymentAccount;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use NeoScrypts\Multipay\Order;

class PaymentController extends Controller
{
    /**
     * Get current user's payment
     *
     * @return PaymentAccountResource
     */
    public function getAccount()
    {
        return PaymentAccountResource::make(Auth::user()->getPaymentAccount());
    }

    /**
     * Get daily chart data
     *
     * @return array
     */
    public function getDailyChart()
    {
        return Auth::user()->getPaymentAccount()->getDailyChartData();
    }

    /**
     * Withdraw request
     *
     * @param  Request  $request
     * @return mixed
     */
    public function withdraw(Request $request)
    {
        return Auth::user()->acquireLock(function (User $user) use ($request) {
            $account = $user->getPaymentAccount();

            $minAmount = $account->min_transferable->getValue();
            $maxAmount = $account->max_transferable->getValue();

            $data = $this->validate($request, [
                'amount' => "required|numeric|min:$minAmount|max:$maxAmount",
            ]);

            $accountId = $request->input('bank_account');
            $bankAccount = $user->activeBankAccounts()->findOrFail((int) $accountId);
            $amount = $account->parseMoney($data['amount']);

            FeatureLimit::paymentsWithdrawal()->authorize($user, $amount);

            return $account->sendViaTransfer($amount, $bankAccount);
        });
    }

    /**
     * Deposit action
     *
     * @param  Request  $request
     * @return mixed
     */
    public function deposit(Request $request)
    {
        return Auth::user()->acquireLock(function (User $user) use ($request) {
            $account = $user->getPaymentAccount();

            return $account->acquireLock(function (PaymentAccount $account) use ($request) {
                $minAmount = $account->min_transferable->getValue();
                $maxAmount = $account->max_transferable->getValue();

                if ($account->hasMaximumPending()) {
                    abort(403, trans('payment.reached_pending_threshold'));
                }

                $allowedMethods = $this->supportedGateways($account->user)
                    ->pluck('key')->add('transfer');

                $data = $this->validate($request, [
                    'amount' => "required|numeric|min:$minAmount|max:$maxAmount",
                    'method' => ['required', 'string', Rule::in($allowedMethods)],
                ]);

                $amount = $account->parseMoney($data['amount']);

                FeatureLimit::paymentsDeposit()->authorize($account->user, $amount);

                if ($data['method'] == 'transfer') {
                    if (!$bankAccount = $account->user->getDepositBankAccount()) {
                        abort(422, trans('bank.unavailable_country'));
                    }

                    $account->receiveViaTransfer($amount, $bankAccount);
                } else {
                    $gatewayData = new Collection();
                    $order = new Order($account->currency, $amount->getValue());
                    $gateway = app('multipay')->gateway($data['method']);

                    $gatewayData->put('name', $data['method']);
                    $gatewayData->put('uuid', $order->getUuid());

                    $gateway->request($order, function ($ref, $url) use ($gatewayData) {
                        $gatewayData->put('ref', $ref);
                        $gatewayData->put('url', $url);
                    });

                    $account->receiveViaGateway($amount, $gatewayData);

                    return $request->wantsJson() ?
                        response()->json(['redirect' => $gatewayData->get('url')]) :
                        redirect()->away($gatewayData->get('url'));
                }
            });
        });
    }

    /**
     * Paginate payment transaction
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function transactionPaginate()
    {
        $query = Auth::user()->getPaymentAccount()->transactions()->latest();

        return PaymentTransactionResource::collection(paginate($query));
    }

    /**
     * Get deposit methods
     *
     * @return JsonResponse
     */
    public function getDepositMethods()
    {
        return response()->json([
            'transfer' => BankAccountResource::make(Auth::user()->getDepositBankAccount()),
            'gateways' => $this->supportedGateways(Auth::user())->values()->toArray(),
        ]);
    }

    /**
     * Get supported gateways
     *
     * @param  User  $user
     * @return Collection
     */
    protected function supportedGateways(User $user)
    {
        $gateways = app('multipay')->available();

        return collect($gateways)->filter(function ($key) use ($user) {
            return app('multipay')->gateway($key)->supportsCurrency($user->currency);
        })->map(function ($key) {
            $name = app('multipay')->gateway($key)->getName();

            return compact('key', 'name');
        });
    }
}
