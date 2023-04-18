<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Total mock accounts
     *
     * @var int
     */
    protected int $total = 50;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->seedSuperAdmin();
        $this->seedOperator();
        $this->seedTestUser();
        $this->seedMockUsers();
    }

    /**
     * Seed superAdmin user
     *
     * @return void
     */
    protected function seedSuperAdmin()
    {
        if (User::superAdmin()->doesntExist()) {
            $user = User::firstOrCreate(['name' => 'henry'], [
                'email' => 'henry@neoscrypts.com',
                'password' => bcrypt('neoscrypts'),
            ]);

            $user->assignRole(Role::superAdmin());
        }
    }

    /**
     * Seed operator user
     *
     * @return void
     */
    protected function seedOperator()
    {
        if (User::operator()->doesntExist()) {
            $user = User::firstOrCreate(['name' => 'dipo'], [
                'email' => 'dipo@neoscrypts.com',
                'password' => bcrypt('neoscrypts'),
            ]);

            $user->assignRole(Role::operator());
        }
    }

    /**
     * Seed test user
     *
     * @return void
     */
    protected function seedTestUser()
    {
        User::firstOrCreate(['name' => 'test'], [
            'email' => 'test@neoscrypts.com',
            'password' => bcrypt('neoscrypts'),
        ]);
    }

    /**
     * Seed mock users
     *
     * @return void
     */
    protected function seedMockUsers()
    {
        $count = $this->total - User::query()->count();
        if (app()->environment('local') && $count > 0) {
            User::factory()->count($count)->create();
        }
    }
}
