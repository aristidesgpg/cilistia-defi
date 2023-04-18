<?php

namespace App\Http\Resources;

use App\Models\CommerceTransaction;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin CommerceTransaction
 *
 * @property CommerceTransaction $resource
 */
class CommerceTransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        if (is_null($this->resource)) {
            return [];
        }

        return [
            'id' => $this->id,
            'currency' => $this->currency,
            'value' => $this->value->getValue(),
            'formatted_price' => $this->formatted_price,
            'price' => $this->price,
            'unconfirmed_received' => $this->unconfirmed_received?->getValue(),
            'formatted_unconfirmed_received_price' => $this->formatted_unconfirmed_received_price,
            'unconfirmed_received_price' => $this->unconfirmed_received_price,
            'received' => $this->received?->getValue(),
            'formatted_received_price' => $this->formatted_received_price,
            'received_price' => $this->received_price,
            'progress' => $this->progress,
            'status' => $this->status,
            'expires_at' => $this->expires_at,
            'completed_at' => $this->completed_at,
            'canceled_at' => $this->canceled_at,
            'address' => $this->address,

            $this->mergeWhen($request->user()?->can('view', $this->resource), [
                'wallet_account' => WalletAccountResource::make($this->whenLoaded('walletAccount')),
            ]),

            'account' => CommerceAccountResource::make($this->whenLoaded('account')),
            'customer' => CommerceCustomerResource::make($this->whenLoaded('customer')),
            'wallet' => WalletResource::make($this->whenAppended('wallet')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
