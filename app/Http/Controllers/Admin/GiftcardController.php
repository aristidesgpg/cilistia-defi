<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\GiftcardContentResource;
use App\Http\Resources\GiftcardResource;
use App\Models\Giftcard;
use App\Models\GiftcardContent;
use App\Rules\Decimal;
use Arr;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\ValidationException;

class GiftcardController extends Controller
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
     * Paginate giftcard
     *
     * @return AnonymousResourceCollection
     */
    public function paginate()
    {
        $records = paginate(Giftcard::latest()->withCount('contents'));

        return GiftcardResource::collection($records);
    }

    /**
     * Create giftcard
     *
     * @param  Request  $request
     * @return GiftcardResource
     */
    public function create(Request $request)
    {
        $validated = $this->validateRequest($request);

        $giftcard = new Giftcard(Arr::except($validated, ['value']));

        $giftcard->value = $giftcard->supportedCurrency->parseMoney($validated['value']);

        return GiftcardResource::make(tap($giftcard)->save());
    }

    /**
     * Update giftcard
     *
     * @param  Request  $request
     * @param  Giftcard  $giftcard
     * @return GiftcardResource
     */
    public function update(Request $request, Giftcard $giftcard)
    {
        $validated = $this->validateRequest($request);

        $giftcard->fill(Arr::except($validated, ['value']))->load('supportedCurrency');

        $giftcard->value = $giftcard->supportedCurrency->parseMoney($validated['value']);

        return GiftcardResource::make(tap($giftcard)->save());
    }

    /**
     * Upload thumbnail
     *
     * @param  Request  $request
     * @param  Giftcard  $giftcard
     *
     * @throws ValidationException
     */
    public function uploadThumbnail(Request $request, Giftcard $giftcard)
    {
        $this->validate($request, [
            'file' => 'required|mimetypes:image/png,image/jpeg|dimensions:ratio=1|file|max:100',
        ]);

        $file = $request->file('file');

        $thumbnail = savePublicFile($file, $giftcard->path());
        $giftcard->update(['thumbnail' => $thumbnail]);
    }

    /**
     * Giftcard already has a buyer
     *
     * @param  Giftcard  $giftcard
     * @return void
     */
    public function delete(Giftcard $giftcard)
    {
        if ($giftcard->contents()->has('buyer')->exists()) {
            abort(403, trans('giftcard.buyer_exists'));
        } else {
            $giftcard->delete();
        }
    }

    /**
     * Paginate contents of this giftcard
     *
     * @param  Giftcard  $giftcard
     * @return AnonymousResourceCollection
     */
    public function contentPaginate(Giftcard $giftcard)
    {
        $records = paginate($giftcard->contents()->doesntHave('buyer')->latest());

        return GiftcardContentResource::collection($records);
    }

    /**
     * @param  Request  $request
     * @param  Giftcard  $giftcard
     *
     * @throws ValidationException
     */
    public function createContent(Request $request, Giftcard $giftcard)
    {
        $validated = $this->validate($request, [
            'code' => 'required|string|max:300',
            'serial' => 'required|string|max:200',
        ]);

        $content = new GiftcardContent();
        $content->serial = $validated['serial'];
        $content->code = $validated['code'];
        $content->giftcard()->associate($giftcard);
        $content->save();
    }

    /**
     * Delete content
     *
     * @param  Giftcard  $giftcard
     * @param    $content
     * @return void
     */
    public function deleteContent(Giftcard $giftcard, $content)
    {
        $giftcardContent = $giftcard->contents()->doesntHave('buyer')->findOrFail($content);

        $giftcardContent->delete();
    }

    /**
     * Validate request
     *
     * @param  Request  $request
     * @return array
     *
     * @throws ValidationException
     */
    protected function validateRequest(Request $request)
    {
        return $this->validate($request, [
            'title' => ['required', 'string', 'max:250'],
            'label' => ['required', 'string', 'max:10'],
            'description' => ['required', 'string', 'max:10000'],
            'instruction' => ['required', 'string', 'max:10000'],
            'value' => ['required', 'numeric', 'gt:0', new Decimal],
            'currency' => ['required', 'exists:supported_currencies,code'],
            'brand_id' => ['required', 'exists:giftcard_brands,id'],
        ]);
    }
}
