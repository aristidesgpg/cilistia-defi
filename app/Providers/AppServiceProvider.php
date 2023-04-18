<?php

namespace App\Providers;

use App\CoinAdapters\BinanceAdapter;
use App\CoinAdapters\BitcoinAdapter;
use App\CoinAdapters\BitcoinCashAdapter;
use App\CoinAdapters\DashAdapter;
use App\CoinAdapters\EthereumAdapter;
use App\CoinAdapters\LitecoinAdapter;
use App\CoinAdapters\Tokens\Binance\BUSDAdapter;
use App\CoinAdapters\Tokens\Binance\SFMAdapter;
use App\CoinAdapters\Tokens\Ethereum\LINKAdapter;
use App\CoinAdapters\Tokens\Ethereum\OMGAdapter;
use App\CoinAdapters\Tokens\Ethereum\SHIBAdapter;
use App\CoinAdapters\Tokens\Ethereum\USDTAdapter;
use App\Helpers\CoinManager;
use App\Helpers\CspPolicy;
use App\Helpers\Settings;
use App\Helpers\ValueStore;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->registerValueStore();
        $this->registerSettings();
        $this->registerCoinAdapters();
        $this->registerCoinManager();
        $this->registerCspPolicy();
    }

    /**
     * Register Value Store
     *
     * @return void
     */
    protected function registerValueStore(): void
    {
        $this->app->singleton(ValueStore::class, function () {
            return ValueStore::make(storage_path('app/settings.json'));
        });
    }

    /**
     * Register Settings
     *
     * @return void
     */
    protected function registerSettings(): void
    {
        $settings = new Settings();
        $this->app->instance(Settings::class, $settings);
        $this->app->instance('settings', $settings);
    }

    /**
     * Register coin adapters
     *
     * @return void
     */
    protected function registerCoinAdapters(): void
    {
        $this->app->singleton('coin.adapters', fn () => collect());
    }

    /**
     * Register coin manager
     *
     * @return void
     */
    protected function registerCoinManager(): void
    {
        $this->app->singleton('coin.manager', function () {
            return tap(new CoinManager(), function (CoinManager $manager) {
                $manager->addAdapter(BitcoinAdapter::class);
                $manager->addAdapter(BitcoinCashAdapter::class);
                $manager->addAdapter(DashAdapter::class);
                $manager->addAdapter(LitecoinAdapter::class);
                $manager->addAdapter(EthereumAdapter::class);
                $manager->addAdapter(BinanceAdapter::class);

                $manager->addAdapter(BUSDAdapter::class);
                $manager->addAdapter(SFMAdapter::class);
                $manager->addAdapter(LINKAdapter::class);
                $manager->addAdapter(OMGAdapter::class);
                $manager->addAdapter(SHIBAdapter::class);
                $manager->addAdapter(USDTAdapter::class);
            });
        });
    }

    /**
     * Register CSP Policy
     *
     * @return void
     */
    protected function registerCspPolicy(): void
    {
        $this->app->singleton(CspPolicy::class, fn () => new CspPolicy());
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        Model::preventSilentlyDiscardingAttributes($this->app->isLocal());
        JsonResource::withoutWrapping();
    }
}
