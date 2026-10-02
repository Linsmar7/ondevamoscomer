<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PriceRange;
use Carbon\Carbon;
use Database\Factories\PlaceFactory;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
  'restaurant_list_id',
  'name',
  'address',
  'google_maps_url',
  'price_range',
  'description',
  'visited',
  'in_roulette',
  'external_link',
])]
class Place extends Model {
  /** @use HasFactory<PlaceFactory> */
  use HasFactory;

  /**
   * Get the attributes that should be cast.
   *
   * @return array<string, string>
   */
  protected function casts(): array {
    return [
      'visited' => 'boolean',
      'in_roulette' => 'boolean',
      'price_range' => PriceRange::class,
    ];
  }

  /**
   * List where this place belongs.
   *
   * @return BelongsTo<RestaurantList, $this>
   */
  public function restaurantList(): BelongsTo {
    return $this->belongsTo(RestaurantList::class);
  }

  /**
   * History of visits/picks for this place.
   *
   * @return HasMany<VisitHistory, $this>
   */
  public function visitHistories(): HasMany {
    return $this->hasMany(VisitHistory::class);
  }

  /**
   * Generate a 100% free Google Calendar Web Intent URL to create an event.
   */
  public function googleCalendarUrl(?DateTimeInterface $start = null): string {
    $startTime = $start ? Carbon::instance($start) : Carbon::now()->addHours(2);
    $endTime = (clone $startTime)->addHours(2);

    $dates = $startTime->format('Ymd\THis\Z').'/'.$endTime->format('Ymd\THis\Z');
    $title = urlencode("Comer em {$this->name} 🍽️");

    $detailsParts = [];
    $detailsParts[] = 'Restaurante sorteado pelo app Onde Vamos Comer!';
    if (! empty($this->price_range)) {
      $detailsParts[] = "Preço: {$this->price_range->value}";
    }
    if (! empty($this->description)) {
      $detailsParts[] = "Notas/Recomendações: {$this->description}";
    }
    if (! empty($this->google_maps_url)) {
      $detailsParts[] = "Google Maps: {$this->google_maps_url}";
    } elseif (! empty($this->address)) {
      $detailsParts[] = "Endereço: {$this->address}";
    }
    if (! empty($this->external_link)) {
      $detailsParts[] = "Link: {$this->external_link}";
    }

    $details = urlencode(implode("\n\n", $detailsParts));
    $location = urlencode($this->address ?: ($this->google_maps_url ?: $this->name));

    return "https://calendar.google.com/calendar/render?action=TEMPLATE&text={$title}&dates={$dates}&details={$details}&location={$location}";
  }
}
