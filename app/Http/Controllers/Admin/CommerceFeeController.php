<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\WalletResource;
use App\Models\Coin;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CommerceFeeController extends Controller
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
     * Get commerce fees
     *
     * @return AnonymousResourceCollection
     */
    public function all()
    {
        $wallets = Wallet::with('commerceFee')->get();

        return WalletResource::collection($wallets);
    }

    /**
     * Update commerce fee
     *
     * @param  Request  $request
     */
    public function update(Request $request)
    {
        $identifiers = Coin::all()->pluck('identifier');

        $validated = $this->validate($request, [
            'fees' => 'required|array:' . $identifiers->implode(','),
            'fees.*' => 'required|numeric|min:0|max:99',
        ]);

        foreach ($validated['fees'] as $identifier => $value) {
            $wallet = Wallet::identifier($identifier)->firstOrFail();
            $commerceFee = $wallet->commerceFee()->firstOrNew();

            $commerceFee->fill(compact('value'))->save();
        }
    }
}
