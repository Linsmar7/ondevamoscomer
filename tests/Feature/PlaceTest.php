<?php

declare(strict_types=1);

use App\Enums\PriceRange;
use App\Models\Place;
use App\Models\RestaurantList;
use App\Models\User;

test('a place belongs to a restaurant list and casts price_range enum properly', function (): void {
  $place = Place::factory()->create([
    'price_range' => PriceRange::Moderate,
    'visited' => false,
  ]);

  expect($place->price_range)->toBe(PriceRange::Moderate)
    ->and($place->price_range->label())->toBe('$$ (Moderado)')
    ->and($place->visited)->toBeFalse()
    ->and($place->restaurantList)->toBeInstanceOf(RestaurantList::class);
});

test('google calendar web intent url generates valid calendar template link', function (): void {
  $place = Place::factory()->create([
    'name' => 'Bottino Ristorante Italiano',
    'address' => 'Praça Brg. Faria Rocha - Rio Vermelho, Salvador - BA',
    'price_range' => PriceRange::VeryExpensive,
    'description' => 'Pedir massa artesanal',
  ]);

  $url = $place->googleCalendarUrl();

  expect($url)->toContain('https://calendar.google.com/calendar/render?action=TEMPLATE')
    ->and($url)->toContain('Bottino+Ristorante+Italiano')
    ->and($url)->toContain('Rio+Vermelho');
});

test('owner and members have authorization over places in the list', function (): void {
  $owner = User::factory()->create();
  $member = User::factory()->create();
  $stranger = User::factory()->create();

  $list = RestaurantList::factory()->create(['user_id' => $owner->id]);
  $list->members()->attach($member->id, ['role' => 'member']);

  $place = Place::factory()->create(['restaurant_list_id' => $list->id]);

  expect($owner->can('view', $place))->toBeTrue()
    ->and($owner->can('update', $place))->toBeTrue()
    ->and($member->can('view', $place))->toBeTrue()
    ->and($member->can('update', $place))->toBeTrue()
    ->and($stranger->can('view', $place))->toBeFalse()
    ->and($stranger->can('update', $place))->toBeFalse();
});
