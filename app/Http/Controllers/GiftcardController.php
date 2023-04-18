<?php

namespace App\Http\Controllers;

use Akaunting\Money\Money;
use App\Http\Requests\VerifiedRequest;
use App\Http\Resources\GiftcardResource;
use App\Models\FeatureLimit;
use App\Models\Giftcard;
use App\Models\GiftcardContent;
use App\Models\Module;
use App\Models\PaymentAccount;
use App\Models\User;
use App\Notifications\GiftcardPurchase;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GiftcardController extends Controller
{
    /**
     * Get giftcard records
     *
     * @param  Request  $request
     * @return AnonymousResourceCollection
     *
     * @throws ValidationException
     */
    public function cart(Request $request)
    {
        $validated = $this->validate($request, [
            'ids' => 'required|array|min:1|max:100',
            'ids.*' => 'required|integer|min:1|max_digits:36',
        ]);

        $records = Giftcard::whereIn('id', $validated['ids'])->get();

        return GiftcardResource::collection($records);
    }

    /**
     * Purchase giftcards
     *
     * @param  VerifiedRequest  $request
     *
     * @throws ValidationException|AuthorizationException
     */
    public function purchase(VerifiedRequest $request)
    {
        $validated = $this->validate($request, [
            'items' => 'required|array|min:1',
            'items.*' => 'required|array:id,quantity',
            'items.*.id' => 'required|exists:giftcards,id',
            'items.*.quantity' => 'required|integer|min:1|max:100',
        ]);

        $items = collect($validated['items']);

        Auth::user()->acquireLock(function (User $user) use ($items) {
            $account = $user->getPaymentAccount();
            $operator = Module::giftcard()->getOperatorFor($user);

            $account->acquireLock(function (PaymentAccount $paymentAccount) use ($items, $operator) {
                $items->transform(function ($item) use ($paymentAccount) {
                    $giftcard = Giftcard::find($item['id']);
                    $price = $giftcard->getPrice($paymentAccount->user);

                    $quantity = $item['quantity'];

                    if ($giftcard->stock < $quantity) {
                        return abort(422, trans('giftcard.insufficient_stock'));
                    }

                    $cost = $price->multiply($quantity);

                    return compact('giftcard', 'cost', 'quantity');
                });

                $total = $items->reduce(function (Money $total, $item) {
                    return $total->add($item['cost']);
                }, $paymentAccount->parseMoney(0));

                FeatureLimit::giftcardTrade()->authorize($paymentAccount->user, $total);

                if ($paymentAccount->getAvailableObject()->lessThan($total)) {
                    return abort(422, trans('payment.insufficient_balance'));
                }

                DB::transaction(function () use ($items, $paymentAccount, $total, $operator) {
                    $items->each(function ($item) use ($paymentAccount) {
                        $contents = $this->getContents($item['giftcard'], $item['quantity']);

                        $contents->each(function (GiftcardContent $content) use ($paymentAccount) {
                            $purchasedContent = $content->acquireLock(function (GiftcardContent $content) use ($paymentAccount) {
                                if (!is_null($content->buyer)) {
                                    return abort(422, trans('giftcard.buyer_exists'));
                                }
                                $content->bought_at = now();
                                $content->buyer()->associate($paymentAccount->user);

                                return tap($content)->save();
                            });

                            if (!$purchasedContent instanceof GiftcardContent) {
                                return abort(422, trans('giftcard.buyer_exists'));
                            }
                        });
                    });

                    $paymentAccount->debit($total, $this->getBuyerDescription($items));
                    $operatorPaymentAccount = $operator->getPaymentAccountByCurrency($paymentAccount->currency);
                    $operatorPaymentAccount->credit($total, $this->getSellerDescription($items));
                    FeatureLimit::giftcardTrade()->setUsage($total, $paymentAccount->user);
                });

                $paymentAccount->user->notify(new GiftcardPurchase($items, $total));
            });
        });
    }

    /**
     * Get giftcard contents
     *
     * @param  Giftcard  $giftcard
     * @param  int  $quantity
     * @return Collection
     */
    protected function getContents(Giftcard $giftcard, int $quantity)
    {
        $contents = $giftcard->contents()->doesntHave('buyer')->limit($quantity)->get();

        return tap($contents, function ($contents) use ($quantity) {
            if ($contents->count() != $quantity) {
                return abort(422, trans('giftcard.insufficient_stock'));
            }
        });
    }

    /**
     * Get purchase description
     *
     * @param  Collection  $items
     * @return string
     */
    protected function getBuyerDescription(Collection $items): string
    {
        return trans('giftcard.buy_description', ['count' => $items->count()]);
    }

    /**
     * Get operator's description
     *
     * @param  Collection  $items
     * @return string
     */
    protected function getSellerDescription(Collection $items): string
    {
        return trans('giftcard.sell_description', ['count' => $items->count()]);
    }

    /**
     * Paginate giftcards
     *
     * @return AnonymousResourceCollection
     */
    public function paginate()
    {
        $records = paginate(Giftcard::query());

        return GiftcardResource::collection($records);
    }
}
