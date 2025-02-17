<?php

namespace App\Http\Resources;

use App\Models\SupportedCurrency;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin SupportedCurrency
 */
class SupportedCurrencyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        if (is_null($this->resource)) {
            return [];
        }

        return [
            'code' => $this->code,
            'name' => $this->name,
            'min_amount' => $this->min_amount?->getValue(),
            'formatted_min_amount' => $this->formatted_min_amount,
            'max_amount' => $this->max_amount?->getValue(),
            'formatted_max_amount' => $this->formatted_max_amount,
            'default' => $this->default,
            'exchange_rate' => $this->exchange_rate,
            'exchange_type' => $this->exchange_type,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            $this->mergeWhen($request->user()?->can('manage_payments'), [
                'payment_accounts_count' => $this->whenNotNull($this->payment_accounts_count),
                'statistic' => $this->whenLoaded('statistic'),
            ]),
        ];
    }
}
