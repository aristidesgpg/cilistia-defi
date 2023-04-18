<?php

namespace App\Http\Controllers;

use App\Http\Resources\CommerceTransactionResource;
use App\Models\CommerceAccount;
use App\Models\CommercePayment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;

class CommerceTransactionController extends Controller
{
    /**
     * Get transaction statistics
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function getStatistics(Request $request)
    {
        $this->validateDateInput($request);

        $from = $request->date('from');
        $to = $request->date('to');
        $account = $this->getAccount();

        $preceding = $from->clone()->subDays($to->diffInDays($from));
        $status = $request->query('status', 'completed');

        $aggregate = $account->getTransactionStatusAggregate($status, $from, $to);
        $precedingAggregate = $account->getTransactionStatusAggregate($status, $preceding, $from);

        $price = $aggregate->sum('total_price');
        $precedingPrice = $precedingAggregate->sum('total_price');
        $priceChange = getPercentageChange($price, $precedingPrice);

        $total = $aggregate->sum('total');
        $precedingTotal = $precedingAggregate->sum('total');
        $totalChange = getPercentageChange($total, $precedingTotal);

        $money = money($price, $account->user->currency, true);

        return response()->json([
            'total' => $total,
            'total_change' => $totalChange,
            'price' => $money->getValue(),
            'formatted_price' => $money->format(),
            'price_change' => $priceChange,
        ]);
    }

    /**
     * Get transaction chart
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function getChart(Request $request)
    {
        $this->validateDateInput($request);

        $from = $request->date('from');
        $to = $request->date('to');
        $account = $this->getAccount();

        $diffInDays = $to->diffInDays($from);
        $query = $account->transactions();

        $query->whereStatus($request->query('status', 'completed'));
        $query->where('created_at', '>=', $from->toDateTimeString());
        $query->where('created_at', '<=', $to->toDateTimeString());

        $intervalInMinutes = match (true) {
            $diffInDays <= 1 => 90,
            $diffInDays <= 7 => 630,
            $diffInDays <= 30 => 2700,
            $diffInDays <= 60 => 5400,
            $diffInDays <= 90 => 8100,
            $diffInDays <= 180 => 16200,
            $diffInDays <= 360 => 32400,
            default => 43800
        };

        $interval = $intervalInMinutes * 60;

        $query->selectRaw("from_unixtime(floor(unix_timestamp(created_at) div ($interval)) * ($interval)) as date");
        $query->selectRaw('count(*) as total');
        $query->selectRaw('sum(value) as total_value');
        $query->selectRaw('wallet_account_id');
        $query->groupBy('date', 'wallet_account_id');

        $aggregate = $query->get()->map(function ($result) use ($account) {
            $walletAccount = $account->getWalletAccount($result->wallet_account_id);
            $totalValue = $walletAccount->wallet->castCoin($result->total_value);
            $totalPrice = $totalValue->getPriceAsMoney($account->user->currency);

            return [
                'date' => $result->date,
                'total_price' => $totalPrice->getValue(),
                'total' => $result->total,
            ];
        });

        $aggregateByDate = $aggregate->groupBy('date')->map(function ($data, $date) use ($account) {
            $totalPrice = money(collect($data)->sum('total_price'), $account->user->currency, true);

            return [
                'date' => (string) Date::parse($date),
                'total_price' => $totalPrice->getValue(),
                'formatted_total_price' => $totalPrice->format(),
                'total' => collect($data)->sum('total'),
            ];
        });

        return response()->json($aggregateByDate->values());
    }

    /**
     * Get wallet aggregate
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function getWalletAggregate(Request $request)
    {
        $status = $request->query('status', 'completed');

        $account = $this->getAccount();

        $aggregate = $account->getTransactionStatusAggregate($status);

        return response()->json($aggregate);
    }

    /**
     * Get transactions pagination
     *
     * @param  Request  $request
     * @return AnonymousResourceCollection
     */
    public function paginate(Request $request)
    {
        $query = $this->getAccount()->transactions()->latest();

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
        $query = $this->getAccount()->transactions();

        return [
            'pending' => $query->clone()->whereStatus('pending')->count(),
            'completed' => $query->clone()->whereStatus('completed')->count(),
            'canceled' => $query->clone()->whereStatus('canceled')->count(),
            'all' => $query->clone()->count(),
        ];
    }

    /**
     * Validate date input
     *
     * @param  Request  $request
     * @return array
     */
    protected function validateDateInput(Request $request)
    {
        return $request->validate([
            'to' => 'required|date|before_or_equal:now|after:from',
            'from' => 'required|date|before:now',
        ]);
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

    /**
     * Get commerce account
     *
     * @return CommerceAccount
     */
    protected function getAccount(): CommerceAccount
    {
        return Auth::user()->commerceAccount()->firstOrFail();
    }
}
