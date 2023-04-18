<?php

namespace Database\Factories;

use Akaunting\Money\Money;
use Illuminate\Database\Eloquent\Factories\Factory;

class GiftcardFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => $this->faker->words(3, true),
            'label' => $this->faker->word(),
            'description' => $this->faker->paragraph(),
            'instruction' => $this->faker->paragraph(),
            'value' => Money::USD($this->faker->numberBetween(10, 100), true),
            'currency' => 'USD',
        ];
    }
}
