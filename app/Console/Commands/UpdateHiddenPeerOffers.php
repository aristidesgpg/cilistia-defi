<?php

namespace App\Console\Commands;

use App\Models\PeerOffer;
use App\Models\WalletAccount;
use Illuminate\Console\Command;
use Illuminate\Support\LazyCollection;
use Symfony\Component\Console\Command\Command as CommandAlias;

class UpdateHiddenPeerOffers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'peer-offers:update-hidden';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Shows all sell offers that can be fulfilled.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->models()->each(fn (PeerOffer $offer) => $this->process($offer));

        return CommandAlias::SUCCESS;
    }

    /**
     * @return LazyCollection
     */
    protected function models(): LazyCollection
    {
        return PeerOffer::whereType('sell')->opened()->whereDisplay(false)->lazyById();
    }

    /**
     * Process model
     *
     * @param  PeerOffer  $offer
     * @return void
     */
    protected function process(PeerOffer $offer): void
    {
        $offer->walletAccount->acquireLock(function (WalletAccount $account) use ($offer) {
            $offer->acquireLock(function (PeerOffer $offer) use ($account) {
                if ($offer->getMaxValueObject()->lessThan($account->getAvailableObject())) {
                    return tap($offer)->update(['display' => true]);
                } elseif (now()->diffInDays($offer->updated_at) >= 7) {
                    return tap($offer)->update(['closed_at' => now()]);
                }
            });
        });
    }
}
