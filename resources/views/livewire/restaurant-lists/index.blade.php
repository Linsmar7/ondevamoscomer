<?php

use App\Models\RestaurantList;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;

new #[Layout('layouts.app'), Title('Minhas Listas - Onde Vamos Comer')] class extends Component {
  public string $name = '';
  public string $description = '';
  public string $joinCode = '';
  public bool $showCreateModal = false;

  /**
   * Get the lists owned by the authenticated user.
   */
  public function with(): array {
    $user = Auth::user();

    $ownedLists = $user->ownedLists()
      ->withCount([
        'places',
        'places as visited_places_count' => fn($query) => $query->where('visited', true),
      ])
      ->with('members')
      ->latest()
      ->get();

    $sharedLists = $user->lists()
      ->withCount([
        'places',
        'places as visited_places_count' => fn($query) => $query->where('visited', true),
      ])
      ->with('owner')
      ->latest()
      ->get();

    return [
      'ownedLists' => $ownedLists,
      'sharedLists' => $sharedLists,
    ];
  }

  /**
   * Create a new restaurant list.
   */
  public function createList(): void {
    $this->validate([
      'name' => ['required', 'string', 'max:255'],
      'description' => ['nullable', 'string', 'max:1000'],
    ]);

    $list = Auth::user()->ownedLists()->create([
      'name' => $this->name,
      'description' => $this->description,
    ]);

    $this->reset(['name', 'description', 'showCreateModal']);

    session()->flash('status', "Lista \"{$list->name}\" criada com sucesso!");
    $this->redirectRoute('lists.show', $list, navigate: true);
  }

  /**
   * Join a list using an invite code.
   */
  public function joinWithCode(): void {
    $this->validate([
      'joinCode' => ['required', 'string'],
    ]);

    $list = RestaurantList::where('invite_code', trim($this->joinCode))->first();

    if (!$list) {
      $this->addError('joinCode', 'Código de convite inválido ou lista não encontrada.');
      return;
    }

    $user = Auth::user();
    if ($list->user_id !== $user->id && !$list->members()->where('users.id', $user->id)->exists()) {
      $list->members()->attach($user->id, ['role' => 'member']);
    }

    $this->reset('joinCode');
    session()->flash('status', "Você entrou na lista \"{$list->name}\"!");
    $this->redirectRoute('lists.show', $list, navigate: true);
  }

  /**
   * Delete a list.
   */
  public function deleteList(int $listId): void {
    $list = RestaurantList::findOrFail($listId);
    $this->authorize('delete', $list);

    $list->delete();
    session()->flash('status', 'Lista excluída com sucesso.');
  }
}; ?>

<div class="py-8">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Top Header & Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
      <div>
        <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight flex items-center gap-3">
          <span>🍽️</span> Onde Vamos Comer?
        </h1>
        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
          Crie listas, convide amigos ou o mozão e gire a roleta para decidir onde comer!
        </p>
      </div>

      <div class="flex items-center gap-3">
        <button
          wire:click="$set('showCreateModal', true)"
          class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl font-semibold text-sm text-white bg-gradient-to-r from-amber-500 to-rose-600 hover:from-amber-600 hover:to-rose-700 shadow-lg shadow-rose-500/25 transition-all duration-200 cursor-pointer active:scale-95"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Nova Lista
        </button>
      </div>
    </div>

    <!-- Feedback Flash Message -->
    @if (session('status'))
      <div class="mb-6 p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 text-sm font-medium flex items-center justify-between">
        <div class="flex items-center gap-2">
          <span>✨</span>
          <span>{{ session('status') }}</span>
        </div>
        <button type="button" @click="$el.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">&times;</button>
      </div>
    @endif

    <!-- Quick Join by Invite Code -->
    <div class="mb-10 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200/80 dark:border-gray-700/60 p-5 shadow-sm">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-lg">
            🎟️
          </div>
          <div>
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Recebeu um convite?</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400">Cole o código do convite para entrar na lista de alguém.</p>
          </div>
        </div>

        <form wire:submit="joinWithCode" class="flex items-center gap-2 w-full sm:w-auto">
          <input
            type="text"
            wire:model="joinCode"
            placeholder="Ex: aB3xK9..."
            class="w-full sm:w-48 text-sm px-3.5 py-2 rounded-xl bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-rose-500 dark:focus:ring-rose-600 focus:outline-none"
          />
          <button
            type="submit"
            class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium transition cursor-pointer shrink-0"
          >
            Entrar
          </button>
        </form>
      </div>
      @error('joinCode')
        <p class="mt-2 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>
      @enderror
    </div>

    <!-- Section: Minhas Listas -->
    <div class="mb-12">
      <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
        <span>⭐</span> Minhas Listas ({{ $ownedLists->count() }})
      </h2>

      @if ($ownedLists->isEmpty())
        <div class="text-center py-12 px-4 rounded-2xl bg-white dark:bg-gray-800 border-2 border-dashed border-gray-200 dark:border-gray-700">
          <div class="text-4xl mb-3">📍</div>
          <h3 class="text-base font-semibold text-gray-900 dark:text-white">Nenhuma lista criada ainda</h3>
          <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 max-w-sm mx-auto">
            Crie sua primeira lista para organizar seus restaurantes favoritos e girar a roleta!
          </p>
          <button
            wire:click="$set('showCreateModal', true)"
            class="mt-4 inline-flex items-center gap-2 px-4 py-2 rounded-xl font-medium text-sm text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition cursor-pointer"
          >
            + Criar lista agora
          </button>
        </div>
      @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          @foreach ($ownedLists as $list)
            <div
              wire:key="owned-list-{{ $list->id }}"
              class="group relative bg-white dark:bg-gray-800 rounded-2xl border border-gray-200/90 dark:border-gray-700/70 p-6 shadow-sm hover:shadow-md hover:border-rose-300 dark:hover:border-rose-900/60 transition-all flex flex-col justify-between"
            >
              <div>
                <div class="flex items-start justify-between gap-3 mb-2">
                  <h3 class="text-lg font-bold text-gray-900 dark:text-white group-hover:text-rose-600 dark:group-hover:text-rose-400 transition">
                    <a href="{{ route('lists.show', $list) }}" wire:navigate>
                      {{ $list->name }}
                    </a>
                  </h3>
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300">
                    Dono
                  </span>
                </div>

                @if ($list->description)
                  <p class="text-sm text-gray-600 dark:text-gray-400 line-clamp-2 mb-4">
                    {{ $list->description }}
                  </p>
                @endif

                <div class="flex items-center gap-4 text-xs text-gray-500 dark:text-gray-400 my-4">
                  <span class="flex items-center gap-1 font-medium">
                    <span class="text-rose-500">📍</span> {{ $list->places_count }} {{ Str::plural('lugar', $list->places_count) }}
                  </span>
                  <span>•</span>
                  <span>
                    ✅ {{ $list->visited_places_count }} visitados
                  </span>
                  @if ($list->members->isNotEmpty())
                    <span>•</span>
                    <span>👥 {{ $list->members->count() }} {{ Str::plural('membro', $list->members->count()) }}</span>
                  @endif
                </div>
              </div>

              <div class="pt-4 border-t border-gray-100 dark:border-gray-700/60 flex items-center justify-between gap-2">
                <!-- Copy Invite Link via Alpine.js -->
                <div x-data="{ copied: false }">
                  <button
                    type="button"
                    @click="
                      navigator.clipboard.writeText('{{ url('/lists/join/' . $list->invite_code) }}');
                      copied = true;
                      setTimeout(() => copied = false, 2000);
                    "
                    class="text-xs font-medium text-gray-600 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 inline-flex items-center gap-1 transition"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                    <span x-text="copied ? 'Link Copiado! 🎉' : 'Copiar Convite'"></span>
                  </button>
                </div>

                <div class="flex items-center gap-2">
                  <button
                    wire:click="deleteList({{ $list->id }})"
                    wire:confirm="Tem certeza que deseja excluir esta lista? Todos os restaurantes dela serão removidos."
                    class="text-xs text-gray-400 hover:text-rose-600 transition p-1"
                    title="Excluir Lista"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                  <a
                    href="{{ route('lists.show', $list) }}"
                    wire:navigate
                    class="px-3.5 py-1.5 rounded-lg bg-gray-900 dark:bg-gray-700 hover:bg-rose-600 dark:hover:bg-rose-600 text-white text-xs font-semibold transition"
                  >
                    Abrir ➔
                  </a>
                </div>
              </div>
            </div>
          @endforeach
        </div>
      @endif
    </div>

    <!-- Section: Listas Compartilhadas Comigo -->
    @if ($sharedLists->isNotEmpty())
      <div>
        <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
          <span>👥</span> Compartilhadas Comigo ({{ $sharedLists->count() }})
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          @foreach ($sharedLists as $list)
            <div
              wire:key="shared-list-{{ $list->id }}"
              class="group relative bg-white dark:bg-gray-800 rounded-2xl border border-gray-200/90 dark:border-gray-700/70 p-6 shadow-sm hover:shadow-md transition-all flex flex-col justify-between"
            >
              <div>
                <div class="flex items-start justify-between gap-3 mb-2">
                  <h3 class="text-lg font-bold text-gray-900 dark:text-white group-hover:text-rose-600 dark:group-hover:text-rose-400 transition">
                    <a href="{{ route('lists.show', $list) }}" wire:navigate>
                      {{ $list->name }}
                    </a>
                  </h3>
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-950/60 text-blue-800 dark:text-blue-300">
                    Membro
                  </span>
                </div>

                <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">
                  Criada por <strong class="text-gray-700 dark:text-gray-200">{{ $list->owner->name }}</strong>
                </p>

                @if ($list->description)
                  <p class="text-sm text-gray-600 dark:text-gray-400 line-clamp-2 mb-4">
                    {{ $list->description }}
                  </p>
                @endif

                <div class="flex items-center gap-4 text-xs text-gray-500 dark:text-gray-400 my-3">
                  <span class="flex items-center gap-1 font-medium">
                    <span class="text-rose-500">📍</span> {{ $list->places_count }} lugares
                  </span>
                  <span>•</span>
                  <span>✅ {{ $list->visited_places_count }} visitados</span>
                </div>
              </div>

              <div class="pt-4 border-t border-gray-100 dark:border-gray-700/60 flex items-center justify-end">
                <a
                  href="{{ route('lists.show', $list) }}"
                  wire:navigate
                  class="px-3.5 py-1.5 rounded-lg bg-gray-900 dark:bg-gray-700 hover:bg-rose-600 dark:hover:bg-rose-600 text-white text-xs font-semibold transition"
                >
                  Abrir ➔
                </a>
              </div>
            </div>
          @endforeach
        </div>
      </div>
    @endif
  </div>

  <!-- Create List Modal -->
  @if ($showCreateModal)
    <div class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
      <div
        class="bg-white dark:bg-gray-800 rounded-3xl max-w-md w-full p-6 shadow-2xl border border-gray-200 dark:border-gray-700 transform transition-all"
        @click.away="$wire.set('showCreateModal', false)"
      >
        <div class="flex items-center justify-between mb-5">
          <h3 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
            <span>✨</span> Criar Nova Lista
          </h3>
          <button
            type="button"
            wire:click="$set('showCreateModal', false)"
            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-2xl leading-none"
          >
            &times;
          </button>
        </div>

        <form wire:submit="createList" class="space-y-4">
          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1">
              Nome da Lista *
            </label>
            <input
              type="text"
              wire:model="name"
              placeholder="Ex: Rolês com o Mozão, Hamburguerias, Almoço de Sexta..."
              class="w-full text-sm px-4 py-2.5 rounded-xl bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-rose-500 focus:outline-none"
              required
              autofocus
            />
            @error('name')
              <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>
            @enderror
          </div>

          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1">
              Descrição (opcional)
            </label>
            <textarea
              wire:model="description"
              rows="3"
              placeholder="Ex: Lugares que queremos conhecer em Salvador para comemorar ocasiões especiais."
              class="w-full text-sm px-4 py-2.5 rounded-xl bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-rose-500 focus:outline-none"
            ></textarea>
            @error('description')
              <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>
            @enderror
          </div>

          <div class="pt-4 flex items-center justify-end gap-3">
            <button
              type="button"
              wire:click="$set('showCreateModal', false)"
              class="px-4 py-2 rounded-xl text-sm font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition"
            >
              Cancelar
            </button>
            <button
              type="submit"
              class="px-5 py-2 rounded-xl text-sm font-semibold text-white bg-gradient-to-r from-amber-500 to-rose-600 hover:from-amber-600 hover:to-rose-700 shadow-md shadow-rose-500/20 transition cursor-pointer"
            >
              Criar Lista
            </button>
          </div>
        </form>
      </div>
    </div>
  @endif
</div>
