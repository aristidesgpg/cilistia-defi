<?php

namespace App\Listeners;

use App\Events\TransferRecordSaved;
use App\Models\CommerceTransaction;
use Illuminate\Contracts\Queue\ShouldQueue;

class CompleteCommerceTransaction implements ShouldQueue
{
    /**
     * Handle the event.
     *
     * @param  TransferRecordSaved  $event
     * @return void
     */
    public function handle(TransferRecordSaved $event): void
    {
        $transaction = $event->getWalletAddress()?->commerceTransactions()->first();

        $transaction?->acquireLock(function (CommerceTransaction $transaction) {
            if ($transaction->isPending()) {
                return $transaction->complete();
            }
        });
    }
}
