<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\GiftcardContentResource;
use App\Models\GiftcardContent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class GiftcardContentController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:manage_giftcards');
    }

    /**
     * Paginate purchased content
     *
     * @param  Request  $request
     * @return AnonymousResourceCollection
     */
    public function purchasedPaginate(Request $request)
    {
        $query = GiftcardContent::has('buyer')->latest();

        $this->filterByBuyer($query, $request);

        return GiftcardContentResource::collection(paginate($query));
    }

    /**
     * Filter query by buyer
     *
     * @param  Builder  $query
     * @param  Request  $request
     */
    protected function filterByBuyer(Builder $query, Request $request)
    {
        if ($search = $request->get('searchUser')) {
            $query->whereHas('buyer', function (Builder $query) use ($search) {
                $query->where('name', 'like', "%$search%");
            });
        }
    }
}
