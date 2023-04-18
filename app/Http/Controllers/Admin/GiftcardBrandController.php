<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\GiftcardBrandResource;
use App\Models\GiftcardBrand;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class GiftcardBrandController extends Controller
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
     * Get brands
     *
     * @return AnonymousResourceCollection
     */
    public function all()
    {
        return GiftcardBrandResource::collection(GiftcardBrand::all());
    }

    /**
     * Paginate brand records
     *
     * @return AnonymousResourceCollection
     */
    public function paginate()
    {
        $records = paginate(GiftcardBrand::latest()->withCount('giftcards'));

        return GiftcardBrandResource::collection($records);
    }

    /**
     * Create Giftcard Brand
     *
     * @param  Request  $request
     *
     * @throws ValidationException
     */
    public function create(Request $request)
    {
        $validated = $this->validate($request, [
            'name' => ['required', 'string', 'max:250', 'unique:giftcard_brands'],
            'description' => ['required', 'string', 'max:10000'],
        ]);

        GiftcardBrand::create($validated);
    }

    /**
     * Update brand
     *
     * @param  Request  $request
     * @param  GiftcardBrand  $brand
     *
     * @throws ValidationException
     */
    public function update(Request $request, GiftcardBrand $brand)
    {
        $validated = $this->validate($request, [
            'name' => ['required', 'string', 'max:250', Rule::unique('giftcard_brands')->ignore($brand)],
            'description' => ['required', 'string', 'max:10000'],
        ]);

        $brand->update($validated);
    }

    /**
     * Delete brand
     *
     * @param  GiftcardBrand  $brand
     * @return void
     */
    public function delete(GiftcardBrand $brand)
    {
        if ($brand->giftcards()->has('contents.buyer')->exists()) {
            abort(403, trans('giftcard.buyer_exists'));
        } else {
            $brand->delete();
        }
    }
}
