<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PriceRange;
use App\Models\Place;
use App\Models\RestaurantList;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Place>
 */
class PlaceFactory extends Factory {
  protected $model = Place::class;

  /**
   * Define the model's default state.
   *
   * @return array<string, mixed>
   */
  public function definition(): array {
    return [
      'restaurant_list_id' => RestaurantList::factory(),
      'name' => fake()->company().' '.fake()->randomElement(['Bar', 'Bistrô', 'Restaurante', 'Pizzaria', 'Burger']),
      'address' => fake()->streetAddress().', Salvador - BA',
      'google_maps_url' => 'https://maps.google.com/?q='.urlencode(fake()->company()),
      'price_range' => fake()->randomElement(PriceRange::cases()),
      'description' => fake()->sentence(),
      'visited' => fake()->boolean(25),
      'external_link' => fake()->url(),
    ];
  }
}
