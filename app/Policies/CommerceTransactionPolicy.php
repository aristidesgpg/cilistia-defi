<?php

namespace App\Policies;

use App\Models\CommerceTransaction;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CommerceTransactionPolicy
{
    use HandlesAuthorization;

    /**
     * View permission
     *
     * @param  User  $user
     * @param  CommerceTransaction  $transaction
     * @return bool
     */
    public function view(User $user, CommerceTransaction $transaction): bool
    {
        return $user->can('manage_commerce') || $user->is($transaction->account->user);
    }
}
