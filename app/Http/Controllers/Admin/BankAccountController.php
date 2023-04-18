<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\VerifiedRequest;
use App\Http\Resources\BankAccountResource;
use App\Models\Bank;
use App\Models\BankAccount;

class BankAccountController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:manage_banks');
    }

    /**
     * Paginate accounts
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function paginate()
    {
        $query = BankAccount::doesntHave('user')->has('supportedCurrency')
            ->has('bank.operatingCountries')->latest();

        return BankAccountResource::collection(paginate($query));
    }

    /**
     * Create bank account
     *
     * @param  VerifiedRequest  $request
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function create(VerifiedRequest $request)
    {
        $validated = $this->validate($request, [
            'beneficiary' => 'required|string|max:250',
            'number' => 'required|string|max:255',
            'currency' => 'required|exists:supported_currencies,code',
            'country' => 'required|exists:operating_countries,code',
            'note' => 'nullable|string|max:1000',
        ]);

        $account = new BankAccount($validated);

        $bank = Bank::country($request->input('country'))
            ->findOrFail($request->input('bank_id'));

        $account->bank()->associate($bank)->save();
    }

    /**
     * Delete account
     *
     * @param  BankAccount  $account
     */
    public function delete(BankAccount $account)
    {
        $account->delete();
    }
}
