<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\WalletResource;
use App\Models\Coin;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\ValidationException;

class ExchangeFeeController extends Controller
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
     * Get exchange fee
     *
     * @return AnonymousResourceCollection
     */
    public function all()
    {
        $records = Wallet::with('exchangeFees')->get();

        return WalletResource::collection($records);
    }

    /**
     * Update exchange fees
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
            'fees.*' => 'required|array:buy,sell',
            'fees.*.*' => 'required|numeric|min:0|max:99',
        ]);

        foreach ($validated['fees'] as $identifier => $categories) {
            $wallet = Wallet::identifier($identifier)->firstOrFail();

            foreach ($categories as $category => $value) {
                $exchangeFee = $wallet->exchangeFees()->firstOrNew(compact('category'));
                $exchangeFee->fill(compact('value'))->save();
            }
        }
    }
}
