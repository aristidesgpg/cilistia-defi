<?php

namespace App\Console\Commands;

use App\Models\Wallet;
use Illuminate\Console\Command;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Console\Command\Command as CommandAlias;

class MigrateEthereumWallet extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ethereum:migrate-wallet';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate ethereum wallets to new format';

    /**
     * Execute the console command.
     *
     * @return int
     *
     * @throws RequestException
     */
    public function handle(): int
    {
        $client = Http::timeout(3600)->acceptJson();

        $network = $this->choice('Which network?', ['ethereum', 'binance']);

        if ($network !== 'binance') {
            $client->baseUrl('http://ethereum-api:7000/');
        } else {
            $client->baseUrl('http://binance-api:7000/');
        }

        $identifier = $this->ask('Enter Coin or Token Identifier');
        $wallet = Wallet::identifier($identifier)->firstOrFail();

        $response = $client->post('wallet/migrate', [
            'password' => $wallet->passphrase,
            'wallet' => $wallet->resource->getId(),
        ]);

        $this->info($response->throw()->json('info'));

        return CommandAlias::SUCCESS;
    }
}
