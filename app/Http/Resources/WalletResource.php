<?php

namespace App\Http\Resources;

use App\Models\Wallet;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Wallet
 */
class WalletResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        if (is_null($this->resource)) {
            return [];
        }

        return [
            'id' => $this->id,
            'consolidates' => $this->consolidates,
            'price_change' => $this->getPriceChange(),
            'native_asset' => $this->native_asset,
            'min_conf' => $this->min_conf,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'coin' => CoinResource::make($this->whenLoaded('coin')),

            $this->mergeWhen($request->user()?->can('manage_wallets'), [
                'accounts_count' => $this->whenNotNull($this->accounts_count),
                'statistic' => $this->whenLoaded('statistic'),
                'commerce_fee' => $this->whenLoaded('commerceFee'),
                'peer_fees' => $this->whenLoaded('peerFees'),
                'withdrawal_fee' => $this->whenLoaded('withdrawalFee'),
                'exchange_fees' => $this->whenLoaded('exchangeFees'),
            ]),
        ];
    }
}
