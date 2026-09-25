<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\RestaurantListFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable(['user_id', 'name', 'description', 'invite_code'])]
class RestaurantList extends Model {
  /** @use HasFactory<RestaurantListFactory> */
  use HasFactory;

  /**
   * The "booted" method of the model.
   */
  protected static function booted(): void {
    static::creating(function (RestaurantList $list): void {
      if (empty($list->invite_code)) {
        $list->invite_code = Str::random(16);
      }
    });
  }

  /**
   * The owner of the list.
   *
   * @return BelongsTo<User, $this>
   */
  public function owner(): BelongsTo {
    return $this->belongsTo(User::class, 'user_id');
  }

  /**
   * Members belonging to the list.
   *
   * @return BelongsToMany<User, $this>
   */
  public function members(): BelongsToMany {
    return $this->belongsToMany(User::class, 'list_members')
      ->withPivot('role')
      ->withTimestamps();
  }

  /**
   * Places added to this list.
   *
   * @return HasMany<Place, $this>
   */
  public function places(): HasMany {
    return $this->hasMany(Place::class);
  }
}
