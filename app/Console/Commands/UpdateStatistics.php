<?php

namespace App\Console\Commands;

use App\Models\SupportedCurrency;
use App\Models\Wallet;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;

class UpdateStatistics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statistics:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update system statistics';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->updateWalletStatistics();
        $this->updateSupportedCurrencyStatistics();

        return CommandAlias::SUCCESS;
    }

    /**
     * Update Supported Currency statistics
     *
     * @return void
     */
    protected function updateSupportedCurrencyStatistics(): void
    {
        SupportedCurrency::all()->each(function ($currency) {
            $accounts = $currency->paymentAccounts()->get();
            $statistics = $currency->statistic()->firstOrNew();

            $statistics->balance = $currency->parseMoney($accounts->sum(function ($account) {
                $query = $account->transactions()->completed()->latest();

                return $query->first()?->balance->getValue() ?: 0;
            }));

            $statistics->balance_on_trade = $currency->parseMoney($accounts->sum(function ($account) {
                return $account->balance_on_trade->getValue();
            }));

            $currency->statistic()->save($statistics);
        });
    }

    /**
     * Update wallet statistics
     *
     * @return void
     */
    protected function updateWalletStatistics(): void
    {
        Wallet::all()->each(function ($wallet) {
            $statistics = $wallet->statistic()->firstOrNew();
            $accounts = $wallet->accounts()->get();

            $statistics->balance = $wallet->parseCoin($accounts->sum(function ($account) {
                $query = $account->transferRecords()->confirmed()->latest();

                return $query->first()?->balance->getValue() ?: 0;
            }));

            $statistics->balance_on_trade = $wallet->parseCoin($accounts->sum(function ($account) {
                return $account->balance_on_trade->getValue();
            }));

            $wallet->statistic()->save($statistics);
        });
    }
}
