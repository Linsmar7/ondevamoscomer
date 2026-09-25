<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\RestaurantList;
use App\Models\User;

class RestaurantListPolicy {
  /**
   * Determine whether the user can view any models.
   */
  public function viewAny(User $user): bool {
    return true;
  }

  /**
   * Determine whether the user can view the model.
   */
  public function view(User $user, RestaurantList $restaurantList): bool {
    return $restaurantList->user_id === $user->id
      || $restaurantList->members()->where('users.id', $user->id)->exists();
  }

  /**
   * Determine whether the user can create models.
   */
  public function create(User $user): bool {
    return true;
  }

  /**
   * Determine whether the user can update the model.
   */
  public function update(User $user, RestaurantList $restaurantList): bool {
    return $restaurantList->user_id === $user->id
      || $restaurantList->members()->where('users.id', $user->id)->exists();
  }

  /**
   * Determine whether the user can delete the model.
   */
  public function delete(User $user, RestaurantList $restaurantList): bool {
    return $restaurantList->user_id === $user->id;
  }

  /**
   * Determine whether the user can manage members / generate invites.
   */
  public function invite(User $user, RestaurantList $restaurantList): bool {
    return $restaurantList->user_id === $user->id;
  }
}
