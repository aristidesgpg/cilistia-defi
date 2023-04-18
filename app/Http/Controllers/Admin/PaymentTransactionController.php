<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentTransactionResource;
use App\Models\PaymentTransaction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PaymentTransactionController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:manage_payments');
    }

    /**
     * Paginate transactions
     *
     * @param  Request  $request
     * @return AnonymousResourceCollection
     */
    public function paginate(Request $request)
    {
        $query = PaymentTransaction::latest();

        match ($request->query('status')) {
            'completed' => $query->completed(),
            'pending-transfer' => $query->pendingTransfer(),
            'pending-gateway' => $query->pendingGateway(),
            'canceled' => $query->canceled(),
            default => null
        };

        match ($request->query('type')) {
            'receive' => $query->where('type', 'receive'),
            'send' => $query->where('type', 'send'),
            default => null
        };

        $this->filterByUser($query, $request);

        return PaymentTransactionResource::collection($query->autoPaginate());
    }

    /**
     * Filter query by user
     *
     * @param  Builder  $query
     * @param  Request  $request
     */
    protected function filterByUser(Builder $query, Request $request)
    {
        if ($search = $request->get('searchUser')) {
            $query->whereHas('account.user', function (Builder $query) use ($search) {
                $query->where('name', 'like', "%{$search}%");
            });
        }
    }

    /**
     * Complete transfer
     *
     * @param  PaymentTransaction  $transaction
     * @return void
     */
    public function completeTransfer(PaymentTransaction $transaction)
    {
        if (!$transaction->isPendingTransfer()) {
            abort(403, trans('auth.action_forbidden'));
        }

        $transaction->completeTransfer();
    }

    /**
     * Cancel transfer
     *
     * @param  PaymentTransaction  $transaction
     * @return void
     */
    public function cancelTransfer(PaymentTransaction $transaction)
    {
        if (!$transaction->isPendingTransfer()) {
            abort(403, trans('auth.action_forbidden'));
        }

        $transaction->cancelPending();
    }
}
