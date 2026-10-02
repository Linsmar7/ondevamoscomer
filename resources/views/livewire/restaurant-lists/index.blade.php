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
   * Get the lists owned by the authenticated user and shared lists.
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

    $coverPhotos = [
      'https://lh3.googleusercontent.com/aida-public/AB6AXuCbTf8IXaT2RW5DtrNQDxS9-H35oTp4DYX0jux_DZU4Ftn86-Bu0IdlCw-OePl3vgjjHY3HNB0se0DEPWZ6v6DBnp06U9Iti0TIEAwL2t21iHEdIaeTZwg7R0ByY7UNBNBmLo6JtTgr7hZ9i7OMx0RaXjx-TPoFplRigK8YMnakT_l5TdGGSCxjwLhPmuv1P0u8ZkG0do_ZJLbsD0mk2l3bYC6t-eQUO-2cg51eu2eoLaVOyM-gGce1XQ',
      'https://lh3.googleusercontent.com/aida-public/AB6AXuAVVvUQtlzistbS_pIDgCcyHCHkPuNoOUZnCE5pl_96jjbmoMNb7A4wgWxBUlGn6Wp2GZuhOxtIuyjQk1ETx3Wvuou4TnTwdHex6KJUbVzdDasy7QAOhg9815o6YZ-d1fZHNHJx4wUyUahYdqNjK4TgDkItkchu2DxE9Abfer3zqGyfmByFjr8Ng-rQy_Xg_JunU6L8ULANtQOMvAllQ16fu_YDlwkPP4OqiZZ7G4zoN0hC7L2wGMUmfQ',
      'https://lh3.googleusercontent.com/aida-public/AB6AXuCXu3KVIqEwKMZfd1gcX2ldbYHqPtsxHoDtmL4ozBcTRwvkMQrlPLMFRaJKlxHsRrCLmaJe1D1P5raLXV6WSEtDPHZiigdxv8mDjtDm-y4fgLY_rvX7RUMA5Y5ql0XFYby_9CW0tdFHtYxjpn-2fxr2dkHX7lyR_oQFm-rLnGM7XYxcLj164OP5V_FHIcfQ0zuzhgHHAGdPi0MUpfiLACplYvVk05X93k45GcuyGyjkuOVfk-3VNnOkcQ',
      'https://lh3.googleusercontent.com/aida-public/AB6AXuDlqN7v0TilPiJkz6CCIB7OjPnsSZxAN5R7QVW31Eh_HBRN12L5TfCjUmaZHfFTKtzKCPFT_PZX-UJXaHcYhUIALlkt5XDBT8NhQ3kMP0UqGCstm9y0J4D5FKJSaOwJza6sSmn6jQrsKprJm7eK5L0mBW3DNyeE18buFuaVp9oa-BVoXudNR4DpJBEu9uXyO24e4cosPooMbbfRI7KiTX56s_96qwdiYRtYQhDH8_OrVbyns1OmkNhRgw',
    ];

    return [
      'ownedLists' => $ownedLists,
      'sharedLists' => $sharedLists,
      'coverPhotos' => $coverPhotos,
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

<div class="flex flex-col w-full">
  <!-- Sub-header ambient bar -->
  <section class="w-full bg-surface-container-lowest py-space-xl border-b border-surface-variant/40">
    <div class="max-w-7xl mx-auto px-margin-mobile lg:px-margin">
      <!-- Main Headline & Actions -->
      <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-space-md">
        <div>
          <h1 class="font-display-lg text-display-lg-mobile md:text-display-lg text-on-surface tracking-tight font-bold">
            Minhas Listas
          </h1>
          <p class="font-label-sm text-label-sm text-on-surface-variant tracking-wide mt-1">
            {{ $ownedLists->count() + $sharedLists->count() }} {{ Str::plural('lista ativa', $ownedLists->count() + $sharedLists->count()) }}
          </p>
        </div>

        <!-- Action Bar: Create & Join Input -->
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-space-sm w-full lg:w-auto">
          <!-- Quick Code Input Group -->
          <form wire:submit="joinWithCode" class="flex items-center bg-surface-container-high shadow-md border border-surface-variant/60">
            <span class="material-symbols-outlined text-outline pl-space-sm pr-space-xs text-[18px]">key</span>
            <input
              wire:model="joinCode"
              class="bg-transparent font-body-md text-body-md text-on-surface placeholder:text-outline py-2 px-space-xs focus:outline-none w-full sm:w-56"
              placeholder="Código de um amigo..."
              type="text"
            >
            <button
              type="submit"
              class="bg-surface-variant hover:bg-surface-bright text-on-surface font-label-md text-label-md px-space-md py-2.5 transition-colors cursor-pointer"
            >
              Entrar
            </button>
          </form>

          <!-- Create New List Button -->
          <button
            type="button"
            wire:click="$set('showCreateModal', true)"
            class="flex items-center justify-center gap-space-xs bg-primary-container hover:bg-primary-fixed-dim text-on-primary-container font-label-lg text-label-lg px-space-lg py-2.5 shadow-md transition-all active:scale-[0.99] font-semibold cursor-pointer"
          >
            <span class="material-symbols-outlined text-[18px]">add</span>
            + Criar nova lista
          </button>
        </div>
      </div>
      @error('joinCode')
        <p class="mt-2 text-xs text-error font-medium">{{ $message }}</p>
      @enderror
    </div>
  </section>

  <!-- Status / Flash Message -->
  @if (session('status'))
    <div class="max-w-7xl mx-auto px-margin-mobile lg:px-margin pt-space-md w-full">
      <div class="p-space-md bg-surface-container-low border border-primary/40 text-on-surface flex items-center justify-between">
        <div class="flex items-center gap-space-xs text-primary font-body-md text-body-md">
          <span class="material-symbols-outlined text-lg">check_circle</span>
          <span>{{ session('status') }}</span>
        </div>
        <button type="button" @click="$el.parentElement.remove()" class="text-outline hover:text-on-surface">&times;</button>
      </div>
    </div>
  @endif

  <!-- Active Lists Grid -->
  <section class="max-w-7xl mx-auto px-margin-mobile lg:px-margin py-space-xl w-full">
    @if ($ownedLists->isEmpty() && $sharedLists->isEmpty())
      <div class="bg-surface-container-lowest p-space-xl border border-surface-variant/60 text-center flex flex-col items-center justify-center">
        <span class="material-symbols-outlined text-5xl text-outline mb-space-sm">restaurant_menu</span>
        <h3 class="font-headline-md text-headline-md text-on-surface font-semibold mb-space-xs">Nenhuma lista criada ainda</h3>
        <p class="font-body-md text-body-md text-on-surface-variant max-w-md mb-space-lg">
          Crie sua primeira lista de restaurantes, convide os amigos ou use o código de uma lista já existente!
        </p>
        <button
          type="button"
          wire:click="$set('showCreateModal', true)"
          class="flex items-center gap-space-xs bg-primary hover:bg-primary-fixed-dim text-on-primary font-label-lg text-label-lg px-space-lg py-2.5 shadow-md font-semibold cursor-pointer"
        >
          <span class="material-symbols-outlined text-lg">add</span>
          Criar minha primeira lista
        </button>
      </div>
    @else
      <div class="grid grid-cols-1 md:grid-cols-2 gap-space-lg">
        <!-- Owned Lists -->
        @foreach ($ownedLists as $list)
          @php
            $photo = $coverPhotos[$loop->index % count($coverPhotos)];
          @endphp
          <article
            wire:key="owned-list-{{ $list->id }}"
            class="bg-surface-container flex flex-col justify-between shadow-lg relative overflow-hidden group border border-surface-variant/40"
          >
            <div class="relative w-full h-44 overflow-hidden bg-surface-container-high">
              <img class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" alt="{{ $list->name }}" src="{{ $photo }}">
              <div class="absolute inset-0 bg-gradient-to-t from-surface-container via-surface-container/40 to-transparent"></div>
              <div class="absolute top-space-sm left-space-sm">
                <span class="bg-surface-container-lowest/90 backdrop-blur-sm text-secondary font-label-sm text-label-sm uppercase tracking-wider px-2.5 py-1">
                  #dono
                </span>
              </div>
              <div class="absolute bottom-space-xs right-space-sm">
                <span class="font-label-sm text-label-sm text-on-surface-variant bg-surface-container-lowest/80 px-2 py-0.5 uppercase">
                  ATUALIZADA {{ $list->updated_at->diffForHumans() }}
                </span>
              </div>
            </div>

            <div class="p-space-lg flex-1 flex flex-col justify-between">
              <div>
                <div class="flex items-start justify-between gap-space-xs mb-space-xs">
                  <h2 class="font-headline-md text-headline-md text-on-surface font-semibold tracking-tight">
                    <a href="{{ route('lists.show', $list) }}" wire:navigate class="hover:text-primary transition-colors">
                      {{ $list->name }}
                    </a>
                  </h2>

                  <!-- Options Dropdown Menu -->
                  <div class="relative" x-data="{ menuOpen: false }">
                    <button
                      @click="menuOpen = !menuOpen"
                      @click.away="menuOpen = false"
                      class="text-on-surface-variant hover:text-primary transition-colors p-1"
                      title="Opções da lista"
                    >
                      <span class="material-symbols-outlined text-[20px]">more_vert</span>
                    </button>

                    <div
                      x-show="menuOpen"
                      x-transition
                      class="absolute right-0 mt-1 w-44 bg-surface-container-high border border-surface-variant shadow-2xl py-1 z-30"
                      style="display: none;"
                    >
                      <button
                        type="button"
                        @click="
                          navigator.clipboard.writeText('{{ url('/lists/join/' . $list->invite_code) }}');
                          menuOpen = false;
                        "
                        class="w-full text-left px-3 py-2 font-label-md text-label-md text-on-surface hover:bg-surface-bright flex items-center gap-1.5"
                      >
                        <span class="material-symbols-outlined text-sm text-primary">share</span>
                        Copiar Convite
                      </button>

                      <button
                        type="button"
                        wire:click="deleteList({{ $list->id }})"
                        wire:confirm="Tem certeza que deseja excluir esta lista? Todos os restaurantes dela serão removidos."
                        class="w-full text-left px-3 py-2 font-label-md text-label-md text-error hover:bg-surface-bright flex items-center gap-1.5"
                      >
                        <span class="material-symbols-outlined text-sm">delete</span>
                        Excluir Lista
                      </button>
                    </div>
                  </div>
                </div>

                @if ($list->description)
                  <p class="font-body-md text-body-md text-on-surface-variant mb-space-md line-clamp-2">
                    {{ $list->description }}
                  </p>
                @endif
              </div>

              <div>
                <!-- Stats -->
                <div class="bg-surface-container-low px-space-md py-space-sm mb-space-md flex items-center justify-between text-on-surface-variant font-label-sm text-label-sm">
                  <div class="flex items-center gap-space-xs">
                    <span class="material-symbols-outlined text-primary text-[16px]">restaurant</span>
                    <span class="text-on-surface font-medium">{{ $list->places_count }} {{ Str::plural('lugar', $list->places_count) }}</span>
                    <span>•</span>
                    <span>{{ $list->visited_places_count }} visitados</span>
                  </div>
                  @if ($list->members->isNotEmpty())
                    <span class="text-outline uppercase tracking-wider">
                      {{ $list->members->count() }} {{ Str::plural('membro', $list->members->count()) }}
                    </span>
                  @else
                    <span class="text-outline uppercase tracking-wider">Pessoal</span>
                  @endif
                </div>

                <!-- Action buttons -->
                <div class="grid grid-cols-2 gap-space-sm">
                  <a
                    href="{{ route('lists.show', $list) }}"
                    wire:navigate
                    class="bg-primary hover:bg-primary-fixed-dim text-on-primary font-label-md text-label-md py-2.5 px-space-sm flex items-center justify-center gap-1.5 transition-colors shadow-sm font-semibold text-center"
                  >
                    <span class="material-symbols-outlined text-[16px]">casino</span>
                    Girar roleta
                  </a>
                  <a
                    href="{{ route('lists.show', $list) }}"
                    wire:navigate
                    class="bg-surface-container-high hover:bg-surface-bright text-on-surface font-label-md text-label-md py-2.5 px-space-sm flex items-center justify-center gap-1.5 transition-colors text-center"
                  >
                    <span class="material-symbols-outlined text-[16px]">visibility</span>
                    Ver lugares
                  </a>
                </div>
              </div>
            </div>
          </article>
        @endforeach

        <!-- Shared Lists -->
        @foreach ($sharedLists as $list)
          @php
            $photo = $coverPhotos[($loop->index + 2) % count($coverPhotos)];
          @endphp
          <article
            wire:key="shared-list-{{ $list->id }}"
            class="bg-surface-container flex flex-col justify-between shadow-lg relative overflow-hidden group border border-surface-variant/40"
          >
            <div class="relative w-full h-44 overflow-hidden bg-surface-container-high">
              <img class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" alt="{{ $list->name }}" src="{{ $photo }}">
              <div class="absolute inset-0 bg-gradient-to-t from-surface-container via-surface-container/40 to-transparent"></div>
              <div class="absolute top-space-sm left-space-sm">
                <span class="bg-surface-container-lowest/90 backdrop-blur-sm text-tertiary font-label-sm text-label-sm uppercase tracking-wider px-2.5 py-1">
                  #compartilhada
                </span>
              </div>
              <div class="absolute bottom-space-xs right-space-sm">
                <span class="font-label-sm text-label-sm text-on-surface-variant bg-surface-container-lowest/80 px-2 py-0.5 uppercase">
                  POR {{ $list->owner->name }}
                </span>
              </div>
            </div>

            <div class="p-space-lg flex-1 flex flex-col justify-between">
              <div>
                <div class="flex items-start justify-between gap-space-xs mb-space-xs">
                  <h2 class="font-headline-md text-headline-md text-on-surface font-semibold tracking-tight">
                    <a href="{{ route('lists.show', $list) }}" wire:navigate class="hover:text-primary transition-colors">
                      {{ $list->name }}
                    </a>
                  </h2>

                  <!-- Options Dropdown Menu -->
                  <div class="relative" x-data="{ menuOpen: false }">
                    <button
                      @click="menuOpen = !menuOpen"
                      @click.away="menuOpen = false"
                      class="text-on-surface-variant hover:text-primary transition-colors p-1"
                      title="Opções da lista"
                    >
                      <span class="material-symbols-outlined text-[20px]">more_vert</span>
                    </button>

                    <div
                      x-show="menuOpen"
                      x-transition
                      class="absolute right-0 mt-1 w-44 bg-surface-container-high border border-surface-variant shadow-2xl py-1 z-30"
                      style="display: none;"
                    >
                      <button
                        type="button"
                        @click="
                          navigator.clipboard.writeText('{{ url('/lists/join/' . $list->invite_code) }}');
                          menuOpen = false;
                        "
                        class="w-full text-left px-3 py-2 font-label-md text-label-md text-on-surface hover:bg-surface-bright flex items-center gap-1.5"
                      >
                        <span class="material-symbols-outlined text-sm text-primary">share</span>
                        Copiar Convite
                      </button>
                    </div>
                  </div>
                </div>

                <p class="font-body-sm text-body-sm text-outline mb-space-xs">
                  Criada por <strong class="text-on-surface">{{ $list->owner->name }}</strong>
                </p>

                @if ($list->description)
                  <p class="font-body-md text-body-md text-on-surface-variant mb-space-md line-clamp-2">
                    {{ $list->description }}
                  </p>
                @endif
              </div>

              <div>
                <!-- Stats -->
                <div class="bg-surface-container-low px-space-md py-space-sm mb-space-md flex items-center justify-between text-on-surface-variant font-label-sm text-label-sm">
                  <div class="flex items-center gap-space-xs">
                    <span class="material-symbols-outlined text-primary text-[16px]">restaurant</span>
                    <span class="text-on-surface font-medium">{{ $list->places_count }} {{ Str::plural('lugar', $list->places_count) }}</span>
                    <span>•</span>
                    <span>{{ $list->visited_places_count }} visitados</span>
                  </div>
                  <span class="text-outline uppercase tracking-wider">Membro</span>
                </div>

                <!-- Action buttons -->
                <div class="grid grid-cols-2 gap-space-sm">
                  <a
                    href="{{ route('lists.show', $list) }}"
                    wire:navigate
                    class="bg-primary hover:bg-primary-fixed-dim text-on-primary font-label-md text-label-md py-2.5 px-space-sm flex items-center justify-center gap-1.5 transition-colors shadow-sm font-semibold text-center"
                  >
                    <span class="material-symbols-outlined text-[16px]">casino</span>
                    Girar roleta
                  </a>
                  <a
                    href="{{ route('lists.show', $list) }}"
                    wire:navigate
                    class="bg-surface-container-high hover:bg-surface-bright text-on-surface font-label-md text-label-md py-2.5 px-space-sm flex items-center justify-center gap-1.5 transition-colors text-center"
                  >
                    <span class="material-symbols-outlined text-[16px]">visibility</span>
                    Ver lugares
                  </a>
                </div>
              </div>
            </div>
          </article>
        @endforeach
      </div>
    @endif
  </section>

  <!-- Create List Modal -->
  @if ($showCreateModal)
    <div class="fixed inset-0 z-50 overflow-y-auto bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
      <div
        class="bg-surface-container border border-surface-variant p-space-lg shadow-2xl max-w-md w-full relative"
        @click.away="$wire.set('showCreateModal', false)"
      >
        <div class="flex items-center justify-between pb-space-md border-b border-surface-variant/50 mb-space-md">
          <div class="flex items-center gap-space-xs">
            <span class="material-symbols-outlined text-primary text-xl">post_add</span>
            <h3 class="font-headline-md text-headline-md text-on-surface font-bold">
              Nova Lista
            </h3>
          </div>
          <button
            type="button"
            wire:click="$set('showCreateModal', false)"
            class="text-outline hover:text-on-surface transition-colors"
          >
            <span class="material-symbols-outlined text-xl">close</span>
          </button>
        </div>

        <form wire:submit="createList" class="flex flex-col gap-space-md">
          <div>
            <label class="block font-label-sm text-label-sm uppercase tracking-wider text-outline mb-1">
              Nome da Lista *
            </label>
            <input
              type="text"
              wire:model="name"
              placeholder="Ex: Sexta-feira com o Mozão, PFs da Firma..."
              class="w-full bg-surface-container-high border border-surface-variant text-on-surface placeholder:text-outline px-space-md py-2.5 font-body-md focus:border-primary focus:outline-none"
              required
              autofocus
            />
            @error('name')
              <p class="mt-1 text-xs text-error font-medium">{{ $message }}</p>
            @enderror
          </div>

          <div>
            <label class="block font-label-sm text-label-sm uppercase tracking-wider text-outline mb-1">
              Descrição (opcional)
            </label>
            <textarea
              wire:model="description"
              rows="3"
              placeholder="Ex: Lugares românticos com comida farta e preço bom pra ir em casal."
              class="w-full bg-surface-container-high border border-surface-variant text-on-surface placeholder:text-outline px-space-md py-2.5 font-body-md focus:border-primary focus:outline-none"
            ></textarea>
            @error('description')
              <p class="mt-1 text-xs text-error font-medium">{{ $message }}</p>
            @enderror
          </div>

          <div class="pt-space-sm flex items-center justify-end gap-space-sm">
            <button
              type="button"
              wire:click="$set('showCreateModal', false)"
              class="px-space-md py-2 bg-surface-container-high hover:bg-surface-bright text-on-surface font-label-md text-label-md transition-colors cursor-pointer"
            >
              Cancelar
            </button>
            <button
              type="submit"
              class="px-space-lg py-2 bg-primary hover:bg-primary/90 text-on-primary font-label-md text-label-md font-semibold transition-colors shadow-md cursor-pointer"
            >
              Salvar Lista
            </button>
          </div>
        </form>
      </div>
    </div>
  @endif
</div>

