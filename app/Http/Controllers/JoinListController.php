<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\RestaurantList;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class JoinListController extends Controller {
  /**
   * Handle joining a list through an invite code.
   */
  public function __invoke(Request $request, string $code): RedirectResponse {
    $user = $request->user();

    $list = RestaurantList::where('invite_code', $code)->firstOrFail();

    if ($list->user_id === $user->id) {
      return redirect()->route('lists.show', $list)
        ->with('status', 'Você é o dono desta lista!');
    }

    if (! $list->members()->where('users.id', $user->id)->exists()) {
      $list->members()->attach($user->id, ['role' => 'member']);
    }

    return redirect()->route('lists.show', $list)
      ->with('status', "Você agora faz parte da lista \"{$list->name}\"!");
  }
}
