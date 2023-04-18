<?php

namespace App\Http\Resources;

use App\Models\WalletAddress;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin WalletAddress
 */
class WalletAddressResource extends JsonResource
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
            'address' => $this->address,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'total_received' => $this->total_received->getValue(),
            'total_received_price' => $this->total_received_price,
            'formatted_total_received_price' => $this->formatted_total_received_price,
        ];
    }
}
