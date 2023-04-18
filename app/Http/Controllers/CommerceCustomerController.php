<?php

namespace App\Http\Controllers;

use App\Http\Resources\CommerceCustomerResource;
use App\Http\Resources\CommerceTransactionResource;
use App\Models\CommerceAccount;
use App\Models\CommerceCustomer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;

class CommerceCustomerController extends Controller
{
    /**
     * Get statistics
     *
     * @return array
     */
    public function getStatistics(Request $request)
    {
        $this->validateDateInput($request);

        $from = $request->date('from');
        $to = $request->date('to');
        $account = $this->getAccount();

        $preceding = $from->clone()->subDays($to->diffInDays($from));
        $precedingTotal = $account->getCustomerCount($preceding, $from);
        $total = $account->getCustomerCount($from, $to);

        $change = getPercentageChange($total, $precedingTotal);

        return compact('total', 'change');
    }

    /**
     * Paginate records
     *
     * @return AnonymousResourceCollection
     */
    public function paginate()
    {
        $query = $this->getAccount()->customers()->withCount('transactions')->latest();

        return CommerceCustomerResource::collection($query->autoPaginate());
    }

    /**
     * Create customer record
     *
     * @return void
     */
    public function create(Request $request)
    {
        $account = $this->getAccount();

        $validated = $this->validateInput($request, $account);

        $customer = $account->customers()->create($validated);

        return CommerceCustomerResource::make($customer);
    }

    /**
     * Get customer record
     *
     * @return CommerceCustomerResource
     */
    public function get($id)
    {
        $customer = $this->getAccount()->customers()
            ->withCount('transactions')->findOrFail($id);

        return CommerceCustomerResource::make($customer);
    }

    /**
     * Update customer
     *
     * @return CommerceCustomerResource
     */
    public function update(Request $request, $id)
    {
        $account = $this->getAccount();

        $customer = $account->customers()->findOrFail($id);

        $customer->update($this->validateInput($request, $account, $customer));

        return CommerceCustomerResource::make($customer);
    }

    /**
     * Delete customer
     *
     * @return void
     */
    public function delete($id)
    {
        $customer = $this->getAccount()->customers()->findOrFail($id);

        $customer->acquireLockOrAbort(function (CommerceCustomer $customer) {
            $this->authorize('delete', $customer);

            $customer->delete();
        });
    }

    /**
     * Paginate customer transactions
     *
     * @return AnonymousResourceCollection
     */
    public function transactionPaginate($id)
    {
        $query = $this->getAccount()->customers()->findOrFail($id)->transactions()->latest();

        return CommerceTransactionResource::collection($query->autoPaginate());
    }

    /**
     * Validate date input
     *
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
     * Validate request
     *
     * @return array
     */
    protected function validateInput(Request $request, CommerceAccount $account, CommerceCustomer $customer = null)
    {
        $uniqueRule = ($customer?->getUniqueRule() ?: CommerceCustomer::uniqueRule())
            ->where('commerce_account_id', $account->id);

        return $request->validate([
            'email' => ['required', 'email', 'max:100', $uniqueRule],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
        ]);
    }

    /**
     * Get commerce account
     */
    protected function getAccount(): CommerceAccount
    {
        return Auth::user()->commerceAccount()->firstOrFail();
    }
}
