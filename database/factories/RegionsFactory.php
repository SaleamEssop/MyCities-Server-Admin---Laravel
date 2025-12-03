<?php

namespace Database\Factories;

use App\Models\Regions;
use Illuminate\Database\Eloquent\Factories\Factory;

class RegionsFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Regions::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->city(),
            'electricity_base_unit_cost' => $this->faker->randomFloat(2, 1, 10),
            'electricity_base_unit' => 'KWH',
            'water_base_unit_cost' => $this->faker->randomFloat(2, 1, 10),
            'water_base_unit' => 'KL',
            'cost' => $this->faker->randomFloat(2, 100, 500),
            'water_email' => $this->faker->email(),
            'electricity_email' => $this->faker->email(),
        ];
    }
}
