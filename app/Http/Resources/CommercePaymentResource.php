<?php

namespace App\Http\Resources;

use App\Models\CommercePayment;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin CommercePayment
 *
 * @property CommercePayment $resource
 */
class CommercePaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function toArray($request): array
    {
        if (is_null($this->resource)) {
            return [];
        }

        return [
            'id' => $this->id,
            'type' => $this->type,
            'status' => $this->status,
            'amount' => $this->amount->getValue(),
            'formatted_amount' => $this->formatted_amount,
            'title' => $this->title,
            'description' => $this->description,
            'currency' => $this->currency,
            'link' => $this->link,
            'redirect' => $this->redirect,
            'message' => $this->message,
            'expires_at' => $this->expires_at,
            'available' => $this->isAvailable(),
            'active' => $this->isActive(),
            'complete' => $this->isComplete(),
            'account' => CommerceAccountResource::make($this->whenLoaded('account')),
            'wallets' => WalletResource::collection($this->whenLoaded('wallets')),

            $this->mergeWhen($request->user()?->can('view', $this->resource), [
                'deletable' => $request->user()?->can('delete', $this->resource),
                'transactions_count' => $this->whenCounted('transactions'),
                'source' => $this->source,
            ]),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
