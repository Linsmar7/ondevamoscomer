<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\PriceRange;
use App\Models\RestaurantList;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder {
  /**
   * Seed the application's database.
   */
  public function run(): void {
    $user = User::factory()->create([
      'name' => 'Linsmar',
      'email' => 'teste@exemplo.com',
      'password' => bcrypt('password'),
    ]);

    $list = RestaurantList::factory()->create([
      'user_id' => $user->id,
      'name' => 'Restaurantes para o Fim de Semana 🍕',
      'description' => 'Lugares selecionados para sair e comer bem em Salvador.',
    ]);

    $samplePlaces = [
      [
        'name' => 'Bottino Ristorante Italiano',
        'address' => 'Praça Brg. Faria Rocha - Rio Vermelho, Salvador - BA',
        'price_range' => PriceRange::VeryExpensive,
        'description' => 'Massa artesanal e ambiente romântico.',
        'google_maps_url' => 'https://maps.google.com/?q=Bottino+Ristorante+Salvador',
        'visited' => false,
      ],
      [
        'name' => 'Pasárgada Kebab Grill & Bar',
        'address' => 'Praça Brg. Faria Rocha - Rio Vermelho, Salvador - BA',
        'price_range' => PriceRange::Pricey,
        'description' => 'Comida árabe deliciosa e vista agradável.',
        'google_maps_url' => 'https://maps.google.com/?q=Pasargada+Kebab+Grill+Salvador',
        'visited' => false,
      ],
      [
        'name' => 'Rancho do Pastel',
        'address' => 'Caminho de Areia ou Itapuã, Salvador - BA',
        'price_range' => PriceRange::Moderate,
        'description' => 'Pastel crocante e recheado.',
        'google_maps_url' => 'https://maps.google.com/?q=Rancho+do+Pastel+Salvador',
        'visited' => false,
      ],
      [
        'name' => 'Food Park Boca do Rio',
        'address' => 'Boca do Rio, Salvador - BA',
        'price_range' => PriceRange::Moderate,
        'description' => 'Opções variadas de comida de rua.',
        'google_maps_url' => 'https://maps.google.com/?q=Food+Park+Boca+do+Rio+Salvador',
        'visited' => false,
      ],
      [
        'name' => 'Vishnu Culinária Indiana',
        'address' => 'R. Itabuna, 286 a - Rio Vermelho, Salvador - BA',
        'price_range' => PriceRange::Moderate,
        'description' => 'Pedir o thali de carneiro!',
        'google_maps_url' => 'https://maps.google.com/?q=Vishnu+Culinaria+Indiana+Salvador',
        'visited' => false,
      ],
    ];

    foreach ($samplePlaces as $placeData) {
      $list->places()->create($placeData);
    }
  }
}
