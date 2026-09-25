<?php

declare(strict_types=1);

namespace App\Enums;

enum PriceRange: string {
  case Inexpensive = '$';
  case Moderate = '$$';
  case Pricey = '$$$';
  case VeryExpensive = '$$$$';
  case Luxury = '$$$$$';
  case Unknown = '??';

  /**
   * Get a human-readable label for the price range.
   */
  public function label(): string {
    return match ($this) {
      self::Inexpensive => '$ (Econômico)',
      self::Moderate => '$$ (Moderado)',
      self::Pricey => '$$$ (Sofisticado)',
      self::VeryExpensive => '$$$$ (Muito Caro)',
      self::Luxury => '$$$$$ (Luxo)',
      self::Unknown => '?? (Não informado)',
    };
  }
}
