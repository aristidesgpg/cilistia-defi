<?php

namespace Database\Factories;

use App\Models\PeerPaymentCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PeerPaymentCategory>
 */
class PeerPaymentCategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name' => $this->faker->company(),
            'description' => $this->faker->paragraph(),
        ];
    }
}
