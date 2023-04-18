<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\CommerceTransactionResource;
use App\Models\CommercePayment;
use App\Models\CommerceTransaction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class CommerceTransactionController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:manage_commerce');
    }

    /**
     * Paginate transaction records
     *
     * @param  Request  $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function paginate(Request $request)
    {
        $query = CommerceTransaction::latest();

        $this->applyFilters($query, $request);

        return CommerceTransactionResource::collection($query->autoPaginate());
    }

    /**
     * Get statistics of status
     *
     * @return array
     */
    public function getStatusStatistics()
    {
        $query = CommerceTransaction::query();

        return [
            'pending' => $query->clone()->whereStatus('pending')->count(),
            'completed' => $query->clone()->whereStatus('completed')->count(),
            'canceled' => $query->clone()->whereStatus('canceled')->count(),
            'all' => $query->clone()->count(),
        ];
    }

    /**
     * Apply transaction filters
     *
     * @param    $query
     * @param  Request  $request
     * @return void
     */
    protected function applyFilters($query, Request $request)
    {
        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->query('searchUser')) {
            $query->whereHas('walletAccount.user', function (Builder $query) use ($search) {
                $query->where('name', 'like', "%$search%");
            });
        }

        if ($customer = $request->query('customer')) {
            $query->where('commerce_customer_id', $customer);
        }

        if ($walletAccount = $request->query('wallet_account')) {
            $query->where('wallet_account_id', $walletAccount);
        }

        if ($currency = $request->query('currency')) {
            $query->where('currency', $currency);
        }

        if ($payment = $request->query('payment')) {
            $query->where('transactable_type', CommercePayment::class);
            $query->where('transactable_id', $payment);
        }
    }
}
