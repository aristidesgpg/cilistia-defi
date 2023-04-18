<?php

namespace App\Http\Controllers;

use App\Http\Resources\StakePlanResource;
use App\Http\Resources\StakeResource;
use App\Models\Module;
use App\Models\Stake;
use App\Models\StakePlan;
use App\Models\WalletAccount;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StakePlanController extends Controller
{
    /**
     * Get paginated StakePlan
     *
     * @return AnonymousResourceCollection
     */
    public function paginate()
    {
        $records = StakePlan::query()->latest()->autoPaginate();

        return StakePlanResource::collection($records);
    }

    /**
     * Stake amount
     *
     * @param  Request  $request
     * @param  StakePlan  $plan
     * @return void
     *
     * @throws Exception
     */
    public function stake(Request $request, StakePlan $plan)
    {
        $rate = $plan->rates()->findOrFail($request->get('rate'));

        $validated = $request->validate([
            'amount' => [
                'required', 'numeric',
                "min:{$rate->min_value}",
                "max:{$rate->max_value}",
            ],
        ]);

        $account = $plan->wallet->getAccount(Auth::user());
        $amount = $account->parseCoin($validated['amount']);

        return $account->acquireLock(function (WalletAccount $walletAccount) use ($amount, $plan, $rate) {
            if ($walletAccount->getAvailableObject()->lessThan($amount)) {
                return abort(422, trans('wallet.insufficient_available'));
            }

            return DB::transaction(function () use ($walletAccount, $amount, $plan, $rate) {
                $stake = new Stake();

                $interest = $amount->multiply($rate->rate / 100);
                $operator = Module::staking()->getOperatorFor($walletAccount->user);

                $stake->redemption_date = now()->addDays($rate->days);

                $stake->value = $amount;
                $stake->yield = $amount->add($interest);
                $stake->days = $rate->days;
                $stake->rate = $rate->rate;

                $stake->walletAccount()->associate($walletAccount);
                $stake->plan()->associate($plan);

                $stake->save();

                $operatorWalletAccount = $stake->walletAccount->parseTarget($operator);

                $operatorDescription = $stake->getSubscriptionDescription($operatorWalletAccount->user);
                $redeemerDescription = $stake->getSubscriptionDescription($stake->walletAccount->user);

                $stake->walletAccount->debit($amount, $redeemerDescription);
                $operatorWalletAccount->credit($amount, $operatorDescription);

                return StakeResource::make($stake);
            });
        });
    }
}
