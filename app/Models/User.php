<?php

declare(strict_types=1);

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable {
  /** @use HasFactory<UserFactory> */
  use HasFactory, Notifiable;

  /**
   * Get the attributes that should be cast.
   *
   * @return array<string, string>
   */
  protected function casts(): array {
    return [
      'email_verified_at' => 'datetime',
      'password' => 'hashed',
    ];
  }

  /**
   * Get the lists owned by the user.
   *
   * @return HasMany<RestaurantList, $this>
   */
  public function ownedLists(): HasMany {
    return $this->hasMany(RestaurantList::class);
  }

  /**
   * Get all lists the user is a member of.
   *
   * @return BelongsToMany<RestaurantList, $this>
   */
  public function lists(): BelongsToMany {
    return $this->belongsToMany(RestaurantList::class, 'list_members')
      ->withPivot('role')
      ->withTimestamps();
  }
}
