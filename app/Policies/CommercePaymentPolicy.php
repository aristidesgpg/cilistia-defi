<?php

namespace App\Policies;

use App\Models\CommercePayment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class CommercePaymentPolicy
{
    use HandlesAuthorization;

    /**
     * View permission
     *
     * @param  User  $user
     * @param  CommercePayment  $payment
     * @return bool
     */
    public function view(User $user, CommercePayment $payment): bool
    {
        return $user->can('manage_commerce') || $user->is($payment->account->user);
    }

    /**
     * Check if payment is publicly available
     *
     * @param  User|null  $user
     * @param  CommercePayment  $payment
     * @return Response
     */
    public function viewPage(?User $user, CommercePayment $payment): Response
    {
        if ($payment->isAvailable()) {
            return $this->allow();
        }

        return $this->deny(trans('commerce.payment_unavailable'));
    }

    /**
     * Permission to create commerce transaction
     *
     * @param  User|null  $user
     * @param  CommercePayment  $payment
     * @return bool
     */
    public function createTransaction(?User $user, CommercePayment $payment): bool
    {
        return $payment->isAvailable();
    }

    /**
     * Check if payment can be updated
     *
     * @param  User  $user
     * @param  CommercePayment  $payment
     * @return Response|bool
     */
    public function update(User $user, CommercePayment $payment): Response|bool
    {
        if ($payment->isThroughApi()) {
            return $this->deny(trans('commerce.cannot_update_payment'));
        }

        return $this->view($user, $payment);
    }

    /**
     * Prevent deleting payment with pending transactions
     *
     * @param  User  $user
     * @param  CommercePayment  $payment
     * @return Response|bool
     */
    public function delete(User $user, CommercePayment $payment): Response|bool
    {
        if (!$payment->isDeletable()) {
            return $this->deny(trans('commerce.cannot_delete_payment'));
        }

        return $this->view($user, $payment);
    }

    /**
     * Authorize status toggle
     *
     * @param  User  $user
     * @param  CommercePayment  $payment
     * @return Response|bool
     */
    public function toggleStatus(User $user, CommercePayment $payment): Response|bool
    {
        if ($payment->expires_at || $payment->isThroughApi()) {
            return $this->deny(trans('commerce.expiring_payment'));
        }

        return $this->view($user, $payment);
    }
}
