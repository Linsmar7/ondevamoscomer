<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\RestaurantList;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<RestaurantList>
 */
class RestaurantListFactory extends Factory
{
    protected $model = RestaurantList::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->words(2, true).' '.fake()->randomElement(['Food', 'Lugares', 'Restaurantes', 'Rolês']),
            'description' => fake()->sentence(),
            'invite_code' => Str::random(16),
        ];
    }
}
