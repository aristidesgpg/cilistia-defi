<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\WalletResource;
use App\Models\Coin;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\ValidationException;

class PeerFeeController extends Controller
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
     * Get peer fees
     *
     * @return AnonymousResourceCollection
     */
    protected function all()
    {
        $records = Wallet::with('peerFees')->get();

        return WalletResource::collection($records);
    }

    /**
     * Update peer fees
     *
     * @param  Request  $request
     * @return void
     *
     * @throws ValidationException
     */
    protected function update(Request $request)
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
                $peerFee = $wallet->peerFees()->firstOrNew(compact('category'));
                $peerFee->fill(compact('value'))->save();
            }
        }
    }
}
