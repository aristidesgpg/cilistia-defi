<?php

namespace Database\Seeders;

use App\Models\Module;
use Illuminate\Database\Seeder;

class ModuleSeeder extends Seeder
{
    /**
     * Available modules
     *
     * @var array
     */
    protected array $data = [
        'staking' => 'Staking',
        'exchange' => 'Exchange',
        'payment' => 'Payment',
        'peer' => 'P2P',
        'commerce' => 'Commerce',
        'giftcard' => 'Giftcard',
        'wallet' => 'Wallet',
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->data as $name => $title) {
            Module::updateOrCreate(compact('name'), compact('title'));
        }
    }
}
