<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Place;
use App\Models\User;

class PlacePolicy {
  /**
   * Determine whether the user can view the model.
   */
  public function view(User $user, Place $place): bool {
    return $user->can('view', $place->restaurantList);
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
  public function update(User $user, Place $place): bool {
    return $user->can('update', $place->restaurantList);
  }

  /**
   * Determine whether the user can delete the model.
   */
  public function delete(User $user, Place $place): bool {
    return $user->can('update', $place->restaurantList);
  }
}
