<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessPaymentTransaction;
use App\Models\PaymentTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use NeoScrypts\Multipay\Drivers\AbstractDriver;

class GatewayController extends Controller
{
    /**
     * Handle gateway callback
     *
     * @param  Request  $request
     * @param $order
     * @return RedirectResponse
     */
    public function handleReturn(Request $request, $order)
    {
        $transaction = PaymentTransaction::findOrFail($order);
        $gateway = app('multipay')->gateway($transaction->gateway_name);
        $response = redirect()->route('main');

        switch ($gateway->handleReturn($request->input())) {
            case AbstractDriver::SUCCESS:
                ProcessPaymentTransaction::dispatch($transaction);

                return $response->notify(trans('payment.approved'), 'success');
            case AbstractDriver::FAILURE:
                return $response->notify(trans('payment.canceled'), 'error');
            case AbstractDriver::REDIRECT:
                return $response;
        }

        return $response->notify(trans('payment.unknown'), 'error');
    }

    /**
     * Handle notification
     *
     * @param  Request  $request
     * @param $order
     * @return JsonResponse|RedirectResponse
     */
    public function handleNotify(Request $request, $order)
    {
        $transaction = PaymentTransaction::findOrFail($order);
        $gateway = app('multipay')->gateway($transaction->gateway_name);

        if ($gateway->handleNotify($request->input()) == AbstractDriver::SUCCESS) {
            ProcessPaymentTransaction::dispatch($transaction);
        }

        if ($request->acceptsHtml()) {
            return redirect()->route('main');
        } else {
            return response()->json([], 202);
        }
    }
}
