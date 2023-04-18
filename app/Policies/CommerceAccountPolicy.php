<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CommerceAccountPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the given user can create posts.
     *
     * @param  User  $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->commerceAccount()->doesntExist();
    }
}
