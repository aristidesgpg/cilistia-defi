<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;

class WalletPassphrase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wallets:passphrase';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show wallet passphrases';

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
        $user = User::superAdmin()->firstOrFail();

        $this->warn('It is unsafe to expose your password.');

        if (!$this->confirm('Do you wish to proceed?')) {
            return CommandAlias::SUCCESS;
        }

        if (!$user->isTwoFactorEnabled()) {
            $this->error('You need to enable 2FA.');

            return CommandAlias::FAILURE;
        }

        $input = $this->secret('Enter your 2FA code');

        if (!$user->verifyTwoFactorToken($input)) {
            $this->error('Verification failed.');

            return CommandAlias::FAILURE;
        }

        $records = Wallet::all()->map(fn ($wallet) => [$wallet->coin->name, $wallet->passphrase]);
        $this->table(['Name', 'Passphrase'], $records);

        return CommandAlias::SUCCESS;
    }
}
