<?php

namespace App\Http\Controllers;

use App\Http\Resources\GiftcardBrandResource;
use App\Models\GiftcardBrand;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class GiftcardBrandController extends Controller
{
    /**
     * Get brands
     *
     * @return AnonymousResourceCollection
     */
    public function all()
    {
        return GiftcardBrandResource::collection(GiftcardBrand::all());
    }
}
