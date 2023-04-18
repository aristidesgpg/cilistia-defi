<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\WalletResource;
use App\Models\Coin;
use App\Models\Wallet;
use App\Rules\Decimal;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\ValidationException;

class WithdrawalFeeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:manage_wallets');
    }

    /**
     * Get withdrawal fees
     *
     * @return AnonymousResourceCollection
     */
    public function all()
    {
        $records = Wallet::with('withdrawalFee')->get();

        return WalletResource::collection($records);
    }

    /**
     * Update withdrawal fees
     *
     * @param  Request  $request
     *
     * @throws ValidationException
     */
    public function update(Request $request)
    {
        $identifiers = Coin::all()->pluck('identifier');

        $validated = $this->validate($request, [
            'fees' => 'required|array:' . $identifiers->implode(','),
            'fees.*' => 'required|array:type,value',
            'fees.*.type' => 'required|in:fixed,percent',
            'fees.*.value' => ['required', 'numeric', 'min:0', new Decimal],
        ]);

        foreach ($validated['fees'] as $identifier => $data) {
            $wallet = Wallet::identifier($identifier)->firstOrFail();
            $withdrawalFee = $wallet->withdrawalFee()->firstOrNew();
            $withdrawalFee->fill($data)->save();
        }
    }
}
