<?php

namespace App\Http\Controllers;

use App\Http\Resources\GiftcardContentResource;
use App\Models\GiftcardContent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;

class GiftcardContentController extends Controller
{
    /**
     * Paginate contents
     *
     * @return AnonymousResourceCollection
     */
    public function paginate()
    {
        $query = GiftcardContent::whereHas('buyer', function (Builder $query) {
            return $query->where('name', Auth::user()->name);
        });

        return GiftcardContentResource::collection($query->autoPaginate());
    }
}
