<?php

namespace App\Console\Commands;

use App\Models\PeerTrade;
use Illuminate\Console\Command;
use Illuminate\Support\LazyCollection;
use Symfony\Component\Console\Command\Command as CommandAlias;

class CancelPeerTrades extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'peer-trades:cancel';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cancel expired peer trades';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->models()->each(fn (PeerTrade $trade) => $this->process($trade));

        return CommandAlias::SUCCESS;
    }

    /**
     * @return LazyCollection
     */
    protected function models(): LazyCollection
    {
        return PeerTrade::inProgress()->whereNull('confirmed_at')->lazyById();
    }

    /**
     * Process action
     *
     * @param  PeerTrade  $trade
     * @return void
     */
    protected function process(PeerTrade $trade): void
    {
        $trade->acquireLock(function (PeerTrade $trade) {
            if ($trade->shouldAutoCancel()) {
                $trade->update(['status' => 'canceled']);
            }
        });
    }
}
