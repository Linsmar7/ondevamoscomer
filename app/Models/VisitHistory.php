<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\VisitHistoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['place_id', 'user_id', 'visited_at', 'notes'])]
class VisitHistory extends Model {
  /** @use HasFactory<VisitHistoryFactory> */
  use HasFactory;

  /**
   * Get the attributes that should be cast.
   *
   * @return array<string, string>
   */
  protected function casts(): array {
    return [
      'visited_at' => 'datetime',
    ];
  }

  /**
   * The place that was visited.
   *
   * @return BelongsTo<Place, $this>
   */
  public function place(): BelongsTo {
    return $this->belongsTo(Place::class);
  }

  /**
   * The user who logged or picked the place.
   *
   * @return BelongsTo<User, $this>
   */
  public function user(): BelongsTo {
    return $this->belongsTo(User::class);
  }
}
