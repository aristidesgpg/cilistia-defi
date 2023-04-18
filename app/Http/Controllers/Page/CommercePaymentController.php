<?php

namespace App\Http\Controllers\Page;

use App\CoinAdapters\Exceptions\AdapterException;
use App\Helpers\LocaleManager;
use App\Http\Controllers\Controller;
use App\Http\Resources\CommerceCustomerResource;
use App\Http\Resources\CommercePaymentResource;
use App\Http\Resources\CommerceTransactionResource;
use App\Models\CommerceAccount;
use App\Models\CommerceCustomer;
use App\Models\CommercePayment;
use App\Models\Module;
use ArrayObject;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class CommercePaymentController extends Controller
{
    /**
     * Locale manager
     *
     * @var LocaleManager
     */
    protected LocaleManager $localeManager;

    /**
     * CommercePaymentController constructor.
     */
    public function __construct(LocaleManager $localeManager)
    {
        $this->localeManager = $localeManager;
    }

    /**
     * Render payment page
     *
     * @return View
     */
    public function view(CommercePayment $payment)
    {
        return view('pages.commerce-payment', [
            'data' => $this->getData($payment),
        ]);
    }

    /**
     * Get commerce payment
     *
     * @return CommercePaymentResource
     */
    public function get(CommercePayment $payment)
    {
        return CommercePaymentResource::make($payment);
    }

    /**
     * Get customer by email
     *
     * @param  CommercePayment  $payment
     * @param  string  $email
     * @return CommerceCustomerResource
     */
    public function getCustomer(CommercePayment $payment, string $email)
    {
        $customer = $payment->getCustomerByEmail($email);

        return CommerceCustomerResource::make($customer);
    }

    /**
     * Create customer
     *
     * @param  Request  $request
     * @param  CommercePayment  $payment
     * @return CommerceCustomerResource
     */
    public function createCustomer(Request $request, CommercePayment $payment)
    {
        $validated = $this->validateCustomerInput($request, $payment->account);

        $customer = $payment->account->customers()->create($validated);

        return CommerceCustomerResource::make($customer);
    }

    /**
     * Get active transaction
     *
     * @param  CommercePayment  $payment
     * @param  string  $email
     * @return CommerceTransactionResource
     */
    public function getActiveTransaction(CommercePayment $payment, string $email)
    {
        $customer = $payment->getCustomerByEmail($email);

        $transaction = $customer->activeTransactions($payment)->firstOrFail();

        return CommerceTransactionResource::make($transaction);
    }

    /**
     * Create transaction
     *
     * @param  Request  $request
     * @param  CommercePayment  $payment
     * @param  string  $email
     * @return mixed
     */
    public function createTransaction(Request $request, CommercePayment $payment, string $email)
    {
        ['coin' => $id] = $request->validate([
            'coin' => 'required|string|max:10',
        ]);

        $wallet = $payment->wallets()->identifier($id)->firstOrFail();
        $walletAccount = $wallet->getAccount($payment->account->user);

        return $payment->acquireLockOrAbort(function (CommercePayment $payment) use ($email, $walletAccount) {
            $this->authorize('createTransaction', $payment);

            $customer = $payment->getCustomerByEmail($email);

            return $customer->acquireLockOrAbort(function (CommerceCustomer $customer) use ($payment, $walletAccount) {
                $this->authorize('createTransaction', [$customer, $payment]);

                $transaction = $customer->createTransaction($payment->amount, $walletAccount, $payment);

                return CommerceTransactionResource::make($transaction);
            });
        });
    }

    /**
     * Get payment transaction
     *
     * @param  CommercePayment  $payment
     * @param  string  $id
     * @return CommerceTransactionResource
     */
    public function getTransaction(CommercePayment $payment, string $id)
    {
        $transaction = $payment->transactions()->findOrFail($id);

        return CommerceTransactionResource::make($transaction);
    }

    /**
     * Relay transaction
     *
     * @param  Request  $request
     * @param  CommercePayment  $payment
     * @param  string  $id
     * @return void
     */
    public function updateTransaction(Request $request, CommercePayment $payment, string $id)
    {
        ['hash' => $hash] = $request->validate([
            'hash' => 'required|string|alpha_dash:ascii|max:100',
        ]);

        try {
            $transaction = $payment->transactions()->findOrFail($id);

            $transaction->walletAccount->wallet->relayTransaction($hash);
        } catch (AdapterException) {
            abort(422, trans('wallet.failed_transaction_relay'));
        }
    }

    /**
     * Validate customer input
     *
     * @param  Request  $request
     * @param  CommerceAccount  $account
     * @return array
     */
    protected function validateCustomerInput(Request $request, CommerceAccount $account)
    {
        $uniqueRule = CommerceCustomer::uniqueRule()->where('commerce_account_id', $account->id);

        return $request->validate([
            'email' => ['required', 'email', 'max:100', $uniqueRule],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
        ]);
    }

    /**
     * Get page data
     *
     * @return array
     */
    protected function getData(CommercePayment $payment)
    {
        return [
            'name' => config('app.name'),
            'commercePayment' => [
                'id' => $payment->id,
                'resource' => CommercePaymentResource::make($payment),
                'settings' => settings()->commerce->all(),
            ],
            'settings' => [
                'locales' => $this->getLocales(),
                'modules' => $this->getModules(),
                'recaptcha' => [
                    'enable' => config('services.recaptcha.enable'),
                    'sitekey' => config('services.recaptcha.sitekey'),
                    'size' => config('services.recaptcha.size'),
                ],
                'baseCurrency' => app('exchanger')->config('base_currency'),
                'theme' => settings()->theme->all(),
                'brand' => settings()->brand->all(),
            ],
            'broadcast' => getBroadcastConfig(),
            'notification' => session('notification'),
            'csrfToken' => csrf_token(),
        ];
    }

    /**
     * Get modules
     *
     * @return Collection
     */
    protected function getModules()
    {
        return Module::all()->pluck('status', 'name');
    }

    /**
     * Get supported locales object
     *
     * @return ArrayObject
     */
    protected function getLocales()
    {
        $locales = $this->localeManager->getLocales();

        return new ArrayObject($locales->toArray());
    }
}
