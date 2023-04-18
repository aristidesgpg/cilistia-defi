<?php

namespace App\Console\Commands;

use App\Models\Wallet;
use App\Models\WalletAddress;
use App\Models\WalletTransaction;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Symfony\Component\Console\Command\Command as CommandAlias;
use Symfony\Component\Console\Helper\ProgressBar;

class MigrateWalletResources extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wallets:migrate-resources';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate wallet resources from previous version';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->info('Migrating Wallet resources.');
        $this->withProgressBar(Wallet::count(), function ($progress) {
            Wallet::lazyById()->each(fn ($wallet) => $this->migrateResource($wallet, $progress));
        });

        $this->newLine(2);

        $this->info('Migrating Transaction resources.');
        $this->withProgressBar(WalletTransaction::count(), function ($progress) {
            WalletTransaction::lazyById()->each(fn ($transaction) => $this->migrateResource($transaction, $progress));
        });

        $this->newLine(2);

        $this->info('Migrating Address resources.');
        $this->withProgressBar(WalletAddress::count(), function ($progress) {
            WalletAddress::lazyById()->each(fn ($address) => $this->migrateResource($address, $progress));
        });

        $this->newLine(2);
        $this->info('Resource migration complete.');

        return CommandAlias::SUCCESS;
    }

    /**
     * Migrate wallet resources
     *
     * @param  Model  $model
     * @param  ProgressBar  $progressBar
     * @return void
     */
    protected function migrateResource(Model $model, ProgressBar $progressBar): void
    {
        $resource = $model->getRawOriginal('resource');

        if (is_string($resource) && !Str::isJson($resource)) {
            $model->update(['resource' => $model->resource]);
        }

        $progressBar->advance();
    }
}
