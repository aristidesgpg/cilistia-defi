<?php

namespace App\Abstracts;

use Illuminate\Support\ServiceProvider;

abstract class CoinServiceProvider extends ServiceProvider
{
    /**
     * Plugin name for resource bindings
     *
     * @var string
     */
    protected string $name;

    /**
     * Adapters to be registered
     *
     * @var array
     */
    protected array $adapters;

    /**
     * Register services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->registerConfig();
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->publishAssets();

        collect($this->adapters)->each(function ($adapter) {
            app('coin.manager')->addAdapter($adapter);
        });
    }

    /**
     * Where to find plugin assets
     *
     * @return string
     */
    abstract protected function resourcePath(): string;

    /**
     * Publish assets
     *
     * @return void
     */
    protected function publishAssets(): void
    {
        if (file_exists($path = rtrim($this->resourcePath(), '/') . '/assets')) {
            $this->publishes([$path => public_path("coin/$this->name")], 'coin-assets');
        }
    }

    /**
     * Register resources
     *
     * @return void
     */
    protected function registerConfig(): void
    {
        if (file_exists($path = rtrim($this->resourcePath(), '/') . '/config.php')) {
            $this->mergeConfigFrom($path, $this->name);
        }
    }
}
