<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Place;
use App\Models\User;
use App\Models\VisitHistory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VisitHistory>
 */
class VisitHistoryFactory extends Factory {
  protected $model = VisitHistory::class;

  /**
   * Define the model's default state.
   *
   * @return array<string, mixed>
   */
  public function definition(): array {
    return [
      'place_id' => Place::factory(),
      'user_id' => User::factory(),
      'visited_at' => fake()->dateTimeBetween('-1 month', 'now'),
      'notes' => fake()->optional()->sentence(),
    ];
  }
}
