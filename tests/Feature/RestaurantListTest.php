<?php

declare(strict_types=1);

use App\Models\RestaurantList;
use App\Models\User;

test('a user can create a restaurant list and invite code is generated automatically', function (): void {
  $user = User::factory()->create();

  $list = RestaurantList::factory()->create([
    'user_id' => $user->id,
    'name' => 'Restaurantes para o Fim de Semana',
  ]);

  expect($list->invite_code)->not->toBeEmpty()
    ->and($list->name)->toBe('Restaurantes para o Fim de Semana')
    ->and($list->owner->id)->toBe($user->id);
});

test('owner can view, update, delete, and invite to their list', function (): void {
  $owner = User::factory()->create();
  $list = RestaurantList::factory()->create(['user_id' => $owner->id]);

  expect($owner->can('view', $list))->toBeTrue()
    ->and($owner->can('update', $list))->toBeTrue()
    ->and($owner->can('delete', $list))->toBeTrue()
    ->and($owner->can('invite', $list))->toBeTrue();
});

test('non-members cannot view or update the list', function (): void {
  $owner = User::factory()->create();
  $stranger = User::factory()->create();
  $list = RestaurantList::factory()->create(['user_id' => $owner->id]);

  expect($stranger->can('view', $list))->toBeFalse()
    ->and($stranger->can('update', $list))->toBeFalse()
    ->and($stranger->can('delete', $list))->toBeFalse()
    ->and($stranger->can('invite', $list))->toBeFalse();
});

test('members can view and update the list but cannot delete or invite', function (): void {
  $owner = User::factory()->create();
  $member = User::factory()->create();
  $list = RestaurantList::factory()->create(['user_id' => $owner->id]);

  $list->members()->attach($member->id, ['role' => 'member']);

  expect($member->can('view', $list))->toBeTrue()
    ->and($member->can('update', $list))->toBeTrue()
    ->and($member->can('delete', $list))->toBeFalse()
    ->and($member->can('invite', $list))->toBeFalse();
});
