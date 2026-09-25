<?php

use App\Enums\PriceRange;
use App\Models\Place;
use App\Models\RestaurantList;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component {
  #[Locked]
  public int $listId;

  public string $search = '';
  public string $filterVisited = 'all'; // all | not_visited | visited
  public string $filterPrice = 'all';

  // Roulette config & state
  public string $rouletteStatusFilter = 'not_visited';
  public string $roulettePriceFilter = 'all';
  public ?int $pickedPlaceId = null;

  // Place Modal state
  public bool $showPlaceModal = false;
  public ?int $editingPlaceId = null;
  public string $placeName = '';
  public string $placeAddress = '';
  public string $placeGoogleMapsUrl = '';
  public string $placePriceRange = '??';
  public string $placeDescription = '';
  public string $placeExternalLink = '';
  public bool $placeVisited = false;

  /**
   * Mount the component with the restaurant list.
   */
  public function mount(RestaurantList $restaurantList): void {
    $this->authorize('view', $restaurantList);
    $this->listId = $restaurantList->id;
  }

  /**
   * Provide data for the view.
   */
  public function with(): array {
    $list = RestaurantList::with(['owner', 'members'])->findOrFail($this->listId);

    $placesQuery = $list->places()->latest();

    if (!empty(trim($this->search))) {
      $term = '%' . trim($this->search) . '%';
      $placesQuery->where(function ($q) use ($term): void {
        $q->where('name', 'ilike', $term)
          ->orWhere('address', 'ilike', $term)
          ->orWhere('description', 'ilike', $term);
      });
    }

    if ($this->filterVisited === 'not_visited') {
      $placesQuery->where('visited', false);
    } elseif ($this->filterVisited === 'visited') {
      $placesQuery->where('visited', true);
    }

    if ($this->filterPrice !== 'all') {
      $placesQuery->where('price_range', $this->filterPrice);
    }

    $places = $placesQuery->get();

    // Candidates for the roulette wheel based on roulette filters
    $rouletteQuery = $list->places();
    if ($this->rouletteStatusFilter === 'not_visited') {
      $rouletteQuery->where('visited', false);
    }
    if ($this->roulettePriceFilter !== 'all') {
      $rouletteQuery->where('price_range', $this->roulettePriceFilter);
    }
    $rouletteCandidates = $rouletteQuery->get(['id', 'name', 'price_range', 'address']);

    $pickedPlace = $this->pickedPlaceId ? Place::find($this->pickedPlaceId) : null;

    return [
      'list' => $list,
      'places' => $places,
      'rouletteCandidates' => $rouletteCandidates,
      'pickedPlace' => $pickedPlace,
      'priceRanges' => PriceRange::cases(),
    ];
  }

  /**
   * Toggle the visited status of a place.
   */
  public function toggleVisited(int $placeId): void {
    $place = Place::where('restaurant_list_id', $this->listId)->findOrFail($placeId);
    $this->authorize('update', $place);

    $place->visited = !$place->visited;
    $place->save();
  }

  /**
   * Open the place creation modal.
   */
  public function openCreatePlaceModal(): void {
    $this->reset([
      'editingPlaceId',
      'placeName',
      'placeAddress',
      'placeGoogleMapsUrl',
      'placePriceRange',
      'placeDescription',
      'placeExternalLink',
      'placeVisited',
    ]);
    $this->placePriceRange = '??';
    $this->showPlaceModal = true;
  }

  /**
   * Open the place edit modal.
   */
  public function openEditPlaceModal(int $placeId): void {
    $place = Place::where('restaurant_list_id', $this->listId)->findOrFail($placeId);
    $this->authorize('update', $place);

    $this->editingPlaceId = $place->id;
    $this->placeName = $place->name;
    $this->placeAddress = $place->address ?? '';
    $this->placeGoogleMapsUrl = $place->google_maps_url ?? '';
    $this->placePriceRange = $place->price_range?->value ?? '??';
    $this->placeDescription = $place->description ?? '';
    $this->placeExternalLink = $place->external_link ?? '';
    $this->placeVisited = (bool) $place->visited;

    $this->showPlaceModal = true;
  }

  /**
   * Save a new or existing place.
   */
  public function savePlace(): void {
    $this->validate([
      'placeName' => ['required', 'string', 'max:255'],
      'placeAddress' => ['nullable', 'string', 'max:500'],
      'placeGoogleMapsUrl' => ['nullable', 'string', 'max:1000'],
      'placePriceRange' => ['required', 'string'],
      'placeDescription' => ['nullable', 'string', 'max:2000'],
      'placeExternalLink' => ['nullable', 'string', 'max:1000'],
    ]);

    $data = [
      'name' => $this->placeName,
      'address' => $this->placeAddress ?: null,
      'google_maps_url' => $this->placeGoogleMapsUrl ?: null,
      'price_range' => $this->placePriceRange,
      'description' => $this->placeDescription ?: null,
      'external_link' => $this->placeExternalLink ?: null,
      'visited' => $this->placeVisited,
    ];

    if ($this->editingPlaceId) {
      $place = Place::where('restaurant_list_id', $this->listId)->findOrFail($this->editingPlaceId);
      $this->authorize('update', $place);
      $place->update($data);
      session()->flash('status', "Restaurante \"{$place->name}\" atualizado com sucesso!");
    } else {
      $list = RestaurantList::findOrFail($this->listId);
      $this->authorize('update', $list);
      $list->places()->create($data);
      session()->flash('status', "Restaurante \"{$data['name']}\" adicionado com sucesso!");
    }

    $this->showPlaceModal = false;
  }

  /**
   * Delete a place.
   */
  public function deletePlace(int $placeId): void {
    $place = Place::where('restaurant_list_id', $this->listId)->findOrFail($placeId);
    $this->authorize('delete', $place);

    $place->delete();
    if ($this->pickedPlaceId === $placeId) {
      $this->pickedPlaceId = null;
    }

    session()->flash('status', 'Restaurante removido.');
  }

  /**
   * Set picked place from roulette result.
   */
  public function selectWinner(int $placeId): void {
    $this->pickedPlaceId = $placeId;

    $place = Place::find($placeId);
    if ($place) {
      $place->visitHistories()->create([
        'user_id' => Auth::id(),
        'visited_at' => now(),
      ]);
    }
  }
}; ?>

<div
  class="py-8"
  x-data="{
    showRouletteModal: false,
    candidates: {{ Js::from($rouletteCandidates) }},
    isSpinning: false,
    currentAngle: 0,
    selectedWinner: null,
    showWinnerModal: false,

    init() {
      this.$watch('candidates', () => {
        this.drawWheel();
      });
      this.$nextTick(() => this.drawWheel());
    },

    colors: [
      '#EF4444', '#F59E0B', '#10B981', '#3B82F6', '#8B5CF6',
      '#EC4899', '#06B6D4', '#F97316', '#6366F1', '#84CC16'
    ],

    drawWheel() {
      const canvas = document.getElementById('roulette-canvas');
      if (!canvas || !this.candidates.length) return;
      const ctx = canvas.getContext('2d');
      const centerX = canvas.width / 2;
      const centerY = canvas.height / 2;
      const radius = canvas.width / 2 - 10;
      const total = this.candidates.length;
      const arcSize = (2 * Math.PI) / total;

      ctx.clearRect(0, 0, canvas.width, canvas.height);

      this.candidates.forEach((cand, i) => {
        const startAngle = this.currentAngle + (i * arcSize);
        const endAngle = startAngle + arcSize;

        ctx.beginPath();
        ctx.moveTo(centerX, centerY);
        ctx.arc(centerX, centerY, radius, startAngle, endAngle);
        ctx.fillStyle = this.colors[i % this.colors.length];
        ctx.fill();
        ctx.lineWidth = 2;
        ctx.strokeStyle = '#ffffff';
        ctx.stroke();

        // Text
        ctx.save();
        ctx.translate(centerX, centerY);
        ctx.rotate(startAngle + arcSize / 2);
        ctx.textAlign = 'right';
        ctx.fillStyle = '#ffffff';
        ctx.font = 'bold 12px sans-serif';
        const label = cand.name.length > 15 ? cand.name.substring(0, 15) + '...' : cand.name;
        ctx.fillText(label, radius - 20, 4);
        ctx.restore();
      });

      // Center Pin
      ctx.beginPath();
      ctx.arc(centerX, centerY, 18, 0, 2 * Math.PI);
      ctx.fillStyle = '#111827';
      ctx.fill();
      ctx.lineWidth = 3;
      ctx.strokeStyle = '#ffffff';
      ctx.stroke();

      ctx.fillStyle = '#ffffff';
      ctx.font = 'bold 14px sans-serif';
      ctx.textAlign = 'center';
      ctx.fillText('🍽️', centerX, centerY + 5);
    },

    spinWheel() {
      if (this.isSpinning || this.candidates.length === 0) return;
      this.isSpinning = true;
      this.showWinnerModal = false;

      const total = this.candidates.length;
      const arcSize = (2 * Math.PI) / total;

      // Choose winner randomly
      const winnerIndex = Math.floor(Math.random() * total);
      const winner = this.candidates[winnerIndex];

      // Calculate target angle so pointer at top (3 * PI / 2) points to winner segment
      // Top pointer corresponds to angle 1.5 * Math.PI (270 deg)
      const extraSpins = 6 + Math.floor(Math.random() * 4); // 6 to 9 full rotations
      const targetAngleWithinArc = arcSize * 0.5; // center of slice
      const targetSliceOffset = (total - winnerIndex) * arcSize - targetAngleWithinArc;
      const targetAngle = this.currentAngle + (extraSpins * 2 * Math.PI) + targetSliceOffset + (1.5 * Math.PI);

      const startAngle = this.currentAngle;
      const diffAngle = targetAngle - startAngle;
      const duration = 5000;
      const startTime = performance.now();

      const animate = (currentTime) => {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);

        // Cubic ease-out function
        const easeOut = 1 - Math.pow(1 - progress, 3);
        this.currentAngle = startAngle + (diffAngle * easeOut);
        this.drawWheel();

        if (progress < 1) {
          requestAnimationFrame(animate);
        } else {
          this.isSpinning = false;
          this.selectedWinner = winner;
          $wire.selectWinner(winner.id);
          this.showWinnerModal = true;

          // Trigger Confetti!
          if (window.confetti) {
            window.confetti({
              particleCount: 100,
              spread: 70,
              origin: { y: 0.6 }
            });
          }
        }
      };

      requestAnimationFrame(animate);
    }
  }"
>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Back to Lists & Breadcrumb -->
    <div class="mb-6 flex items-center justify-between">
      <a
        href="{{ route('dashboard') }}"
        wire:navigate
        class="inline-flex items-center gap-2 text-sm font-semibold text-gray-500 hover:text-rose-600 dark:text-gray-400 dark:hover:text-rose-400 transition"
      >
        <span>←</span> Voltar para Minhas Listas
      </a>

      <!-- Quick Share Invite Link Button -->
      <div x-data="{ copied: false }" class="flex items-center gap-2">
        <button
          type="button"
          @click="
            navigator.clipboard.writeText('{{ url('/lists/join/' . $list->invite_code) }}');
            copied = true;
            setTimeout(() => copied = false, 2000);
          "
          class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 hover:bg-indigo-100 transition"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
          </svg>
          <span x-text="copied ? 'Link de Convite Copiado! 🎉' : 'Convidar Amigo(a)'"></span>
        </button>
      </div>
    </div>

    <!-- Header Banner -->
    <div class="bg-gradient-to-r from-rose-500 via-amber-500 to-orange-500 rounded-3xl p-6 sm:p-8 text-white shadow-xl mb-8 relative overflow-hidden">
      <div class="absolute -right-10 -bottom-10 opacity-15 text-9xl select-none">🎲</div>

      <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
          <span class="inline-block px-3 py-1 rounded-full bg-white/20 backdrop-blur-sm text-xs font-bold uppercase tracking-wider mb-2">
            {{ $list->places()->count() }} {{ Str::plural('restaurante', $list->places()->count()) }} cadastrados
          </span>
          <h1 class="text-3xl sm:text-4xl font-black tracking-tight mb-2">
            {{ $list->name }}
          </h1>
          @if ($list->description)
            <p class="text-white/90 text-sm sm:text-base max-w-2xl">
              {{ $list->description }}
            </p>
          @endif

          <div class="mt-4 flex items-center gap-3 text-xs text-white/80">
            <span>Dono: <strong>{{ $list->owner->name }}</strong></span>
            @if ($list->members->isNotEmpty())
              <span>•</span>
              <span>Membros: {{ $list->members->pluck('name')->join(', ') }}</span>
            @endif
          </div>
        </div>

        <!-- BIG ACTION: SPIN THE WHEEL -->
        <div class="shrink-0 flex flex-col sm:flex-row items-center gap-3">
          <button
            type="button"
            @click="showRouletteModal = true; $nextTick(() => drawWheel())"
            class="w-full sm:w-auto px-7 py-4 rounded-2xl font-black text-lg bg-white text-rose-600 hover:bg-rose-50 shadow-2xl shadow-black/20 hover:scale-105 active:scale-95 transition cursor-pointer flex items-center justify-center gap-3"
          >
            <span class="text-2xl animate-bounce">🎡</span>
            <span>Girar a Roleta!</span>
          </button>

          <button
            type="button"
            wire:click="openCreatePlaceModal"
            class="w-full sm:w-auto px-5 py-3.5 rounded-2xl font-semibold text-sm bg-black/20 hover:bg-black/30 backdrop-blur-sm border border-white/30 text-white transition flex items-center justify-center gap-2 cursor-pointer"
          >
            <span>+</span> Adicionar Lugar
          </button>
        </div>
      </div>
    </div>

    <!-- Status Messages -->
    @if (session('status'))
      <div class="mb-6 p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 text-sm font-medium flex items-center justify-between">
        <div class="flex items-center gap-2">
          <span>✨</span>
          <span>{{ session('status') }}</span>
        </div>
        <button type="button" @click="$el.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">&times;</button>
      </div>
    @endif

    <!-- Search & Filters Toolbar -->
    <div class="mb-6 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-4 shadow-sm flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
      <div class="relative flex-1">
        <input
          type="text"
          wire:model.live.debounce.250ms="search"
          placeholder="Buscar por nome, bairro, recomendação..."
          class="w-full text-sm pl-10 pr-4 py-2.5 rounded-xl bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-rose-500 focus:outline-none"
        />
        <svg class="w-4 h-4 text-gray-400 absolute left-3.5 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
      </div>

      <div class="flex flex-wrap items-center gap-3">
        <!-- Status Filter -->
        <select
          wire:model.live="filterVisited"
          class="text-xs font-semibold px-3 py-2.5 rounded-xl bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-rose-500 focus:outline-none"
        >
          <option value="all">Status: Todos</option>
          <option value="not_visited">Apenas Não Visitados</option>
          <option value="visited">Já Visitados</option>
        </select>

        <!-- Price Filter -->
        <select
          wire:model.live="filterPrice"
          class="text-xs font-semibold px-3 py-2.5 rounded-xl bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-rose-500 focus:outline-none"
        >
          <option value="all">Preço: Todos</option>
          @foreach ($priceRanges as $range)
            <option value="{{ $range->value }}">{{ $range->label() }}</option>
          @endforeach
        </select>
      </div>
    </div>

    <!-- Places Grid -->
    @if ($places->isEmpty())
      <div class="text-center py-16 px-4 rounded-3xl bg-white dark:bg-gray-800 border-2 border-dashed border-gray-200 dark:border-gray-700">
        <div class="text-5xl mb-3">🍕</div>
        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Nenhum lugar encontrado</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 max-w-md mx-auto">
          Adicione seu primeiro restaurante, pastelaria, pizzaria ou burger para poder sortear!
        </p>
        <button
          wire:click="openCreatePlaceModal"
          class="mt-5 px-5 py-2.5 rounded-xl font-semibold text-sm text-white bg-rose-600 hover:bg-rose-700 shadow-md transition cursor-pointer"
        >
          + Adicionar Lugar Agora
        </button>
      </div>
    @else
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach ($places as $place)
          <div
            wire:key="place-card-{{ $place->id }}"
            class="group bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5 shadow-sm hover:shadow-md transition-all flex flex-col justify-between relative {{ $place->visited ? 'opacity-75' : '' }}"
          >
            <div>
              <div class="flex items-start justify-between gap-3 mb-2">
                <div class="flex-1">
                  <h3 class="text-base font-bold text-gray-900 dark:text-white group-hover:text-rose-600 dark:group-hover:text-rose-400 transition flex items-center gap-2">
                    <span>{{ $place->name }}</span>
                    @if ($place->visited)
                      <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300">
                        Já fui!
                      </span>
                    @endif
                  </h3>

                  @if ($place->address)
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 flex items-center gap-1">
                      <span>📍</span>
                      <span>{{ $place->address }}</span>
                    </p>
                  @endif
                </div>

                <!-- Price Badge -->
                @if ($place->price_range)
                  <span class="shrink-0 px-2 py-1 rounded-lg text-xs font-black bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                    {{ $place->price_range->value }}
                  </span>
                @endif
              </div>

              <!-- Description / Notes -->
              @if ($place->description)
                <div class="my-3 p-3 rounded-xl bg-amber-50/70 dark:bg-amber-950/20 border border-amber-100 dark:border-amber-900/30 text-xs text-amber-900 dark:text-amber-200">
                  <p class="font-semibold text-[11px] uppercase tracking-wider text-amber-800 dark:text-amber-400 mb-0.5">Dica / Recomendação:</p>
                  <p class="italic">"{{ $place->description }}"</p>
                </div>
              @endif

              <!-- External Links -->
              <div class="flex flex-wrap items-center gap-2 text-xs my-2">
                @if ($place->google_maps_url)
                  <a
                    href="{{ $place->google_maps_url }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center gap-1 text-blue-600 dark:text-blue-400 hover:underline"
                  >
                    <span>🗺️ Ver no Maps</span>
                  </a>
                @endif

                @if ($place->external_link)
                  <a
                    href="{{ $place->external_link }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center gap-1 text-purple-600 dark:text-purple-400 hover:underline"
                  >
                    <span>🔗 Link/Instagram</span>
                  </a>
                @endif
              </div>
            </div>

            <!-- Card Bottom Bar: Quick Toggle Visited, Google Calendar & Edit/Delete -->
            <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700/60 flex items-center justify-between text-xs">
              <button
                type="button"
                wire:click="toggleVisited({{ $place->id }})"
                class="inline-flex items-center gap-1.5 font-medium transition cursor-pointer {{ $place->visited ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-500 hover:text-emerald-600' }}"
              >
                <span>{{ $place->visited ? '✅ Já Fui' : '⭕ Marcar como Já Fui' }}</span>
              </button>

              <div class="flex items-center gap-2">
                <!-- Free Google Calendar Web Intent Link -->
                <a
                  href="{{ $place->googleCalendarUrl() }}"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="p-1 text-gray-400 hover:text-amber-600 dark:hover:text-amber-400 transition"
                  title="Criar evento no Google Agenda (Grátis)"
                >
                  📅
                </a>

                <!-- Edit Button -->
                <button
                  type="button"
                  wire:click="openEditPlaceModal({{ $place->id }})"
                  class="p-1 text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition"
                  title="Editar"
                >
                  ✏️
                </button>

                <!-- Delete Button -->
                <button
                  type="button"
                  wire:click="deletePlace({{ $place->id }})"
                  wire:confirm="Tem certeza que deseja remover este lugar?"
                  class="p-1 text-gray-400 hover:text-rose-600 transition"
                  title="Excluir"
                >
                  🗑️
                </button>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    @endif
  </div>

  <!-- ROULETTE MODAL -->
  <div
    x-show="showRouletteModal"
    x-cloak
    class="fixed inset-0 z-50 overflow-y-auto bg-black/75 backdrop-blur-md flex items-center justify-center p-4"
  >
    <div
      class="bg-white dark:bg-gray-800 rounded-3xl max-w-xl w-full p-6 sm:p-8 shadow-2xl border border-gray-200 dark:border-gray-700 relative text-center"
      @click.away="if (!isSpinning) showRouletteModal = false"
    >
      <button
        type="button"
        @click="showRouletteModal = false"
        x-show="!isSpinning"
        class="absolute right-5 top-5 text-gray-400 hover:text-gray-600 dark:hover:text-white text-2xl font-bold"
      >
        &times;
      </button>

      <h2 class="text-2xl font-black text-gray-900 dark:text-white flex items-center justify-center gap-2 mb-2">
        <span>🎡</span> Roleta Gastronômica
      </h2>
      <p class="text-xs text-gray-500 dark:text-gray-400 mb-6">
        Deixe a sorte decidir onde vocês vão comer hoje!
      </p>

      <!-- Roulette Filters -->
      <div class="mb-6 flex flex-wrap items-center justify-center gap-3" x-show="!isSpinning">
        <select
          wire:model.live="rouletteStatusFilter"
          class="text-xs px-3 py-1.5 rounded-xl bg-gray-100 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 focus:outline-none"
        >
          <option value="not_visited">Apenas Não Visitados</option>
          <option value="all">Qualquer Status</option>
        </select>

        <select
          wire:model.live="roulettePriceFilter"
          class="text-xs px-3 py-1.5 rounded-xl bg-gray-100 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 focus:outline-none"
        >
          <option value="all">Qualquer Faixa de Preço</option>
          @foreach ($priceRanges as $range)
            <option value="{{ $range->value }}">{{ $range->label() }}</option>
          @endforeach
        </select>
      </div>

      <!-- Wheel Container & Top Pointer -->
      <div class="relative inline-block mx-auto mb-6">
        <!-- Top Pointer Triangle -->
        <div class="absolute -top-3 left-1/2 -translate-x-1/2 z-20 w-0 h-0 border-l-[14px] border-l-transparent border-r-[14px] border-r-transparent border-t-[24px] border-t-rose-600 drop-shadow-md"></div>

        <canvas
          id="roulette-canvas"
          width="360"
          height="360"
          class="rounded-full shadow-2xl mx-auto border-4 border-gray-900 dark:border-white/10"
        ></canvas>
      </div>

      <!-- Spin Button -->
      <div>
        <button
          type="button"
          @click="spinWheel()"
          :disabled="isSpinning || candidates.length === 0"
          class="px-8 py-3.5 rounded-2xl font-black text-lg text-white bg-gradient-to-r from-amber-500 to-rose-600 hover:from-amber-600 hover:to-rose-700 shadow-xl shadow-rose-500/30 transition transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
        >
          <span x-show="!isSpinning">Girar Roleta! 🎲</span>
          <span x-show="isSpinning" class="animate-pulse">Girando... 🌀</span>
        </button>
      </div>

      <template x-if="candidates.length === 0">
        <p class="mt-3 text-xs text-rose-500 font-semibold">
          Nenhum restaurante disponível com os filtros selecionados!
        </p>
      </template>
    </div>
  </div>

  <!-- WINNER MODAL -->
  @if ($pickedPlace)
    <div
      x-show="showWinnerModal"
      x-cloak
      class="fixed inset-0 z-50 overflow-y-auto bg-black/80 backdrop-blur-md flex items-center justify-center p-4 animate-fade-in"
    >
      <div
        class="bg-white dark:bg-gray-800 rounded-3xl max-w-md w-full p-6 sm:p-8 shadow-2xl border-2 border-rose-500 text-center relative transform transition-all"
        @click.away="showWinnerModal = false"
      >
        <div class="text-6xl mb-3">🎉🍽️</div>

        <span class="inline-block px-3 py-1 rounded-full bg-rose-100 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 font-bold text-xs uppercase tracking-wider mb-2">
          O vencedor é!
        </span>

        <h3 class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white mb-2">
          {{ $pickedPlace->name }}
        </h3>

        @if ($pickedPlace->price_range)
          <p class="text-sm font-semibold text-amber-600 dark:text-amber-400 mb-2">
            Faixa de Preço: {{ $pickedPlace->price_range->label() }}
          </p>
        @endif

        @if ($pickedPlace->address)
          <p class="text-xs text-gray-500 dark:text-gray-400 mb-3 flex items-center justify-center gap-1">
            <span>📍</span> {{ $pickedPlace->address }}
          </p>
        @endif

        @if ($pickedPlace->description)
          <div class="my-4 p-3 rounded-xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800 text-xs text-amber-900 dark:text-amber-200 text-left">
            <p class="font-bold text-[10px] uppercase tracking-wider text-amber-800 dark:text-amber-400">Recomendação:</p>
            <p class="italic">"{{ $pickedPlace->description }}"</p>
          </div>
        @endif

        <!-- Call to Actions -->
        <div class="mt-6 flex flex-col gap-2.5">
          <!-- 100% Free Google Calendar Web Intent -->
          <a
            href="{{ $pickedPlace->googleCalendarUrl() }}"
            target="_blank"
            rel="noopener noreferrer"
            class="w-full py-3 rounded-xl font-bold text-sm text-white bg-blue-600 hover:bg-blue-700 shadow-md shadow-blue-500/20 transition flex items-center justify-center gap-2"
          >
            <span>📅</span> Adicionar ao Google Agenda (Grátis)
          </a>

          @if ($pickedPlace->google_maps_url)
            <a
              href="{{ $pickedPlace->google_maps_url }}"
              target="_blank"
              rel="noopener noreferrer"
              class="w-full py-2.5 rounded-xl font-semibold text-sm text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition flex items-center justify-center gap-2"
            >
              <span>🗺️</span> Abrir Rota no Google Maps
            </a>
          @endif

          <button
            type="button"
            wire:click="toggleVisited({{ $pickedPlace->id }})"
            class="w-full py-2.5 rounded-xl font-semibold text-sm text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 hover:bg-emerald-100 transition cursor-pointer"
          >
            {{ $pickedPlace->visited ? '✅ Marcado como Já Fui' : '🍽️ Marcar como Visitado!' }}
          </button>

          <button
            type="button"
            @click="showWinnerModal = false; spinWheel()"
            class="text-xs text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 font-medium py-2 transition"
          >
            Não curtiu? Rodar de novo! 🔄
          </button>
        </div>
      </div>
    </div>
  @endif

  <!-- CREATE / EDIT PLACE MODAL -->
  @if ($showPlaceModal)
    <div class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
      <div
        class="bg-white dark:bg-gray-800 rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-gray-200 dark:border-gray-700 transform transition-all"
        @click.away="$wire.set('showPlaceModal', false)"
      >
        <div class="flex items-center justify-between mb-5">
          <h3 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
            <span>{{ $editingPlaceId ? '✏️ Editar Restaurante' : '🍽️ Novo Restaurante' }}</span>
          </h3>
          <button
            type="button"
            wire:click="$set('showPlaceModal', false)"
            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-2xl leading-none"
          >
            &times;
          </button>
        </div>

        <form wire:submit="savePlace" class="space-y-4 text-left">
          <!-- Nome -->
          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1">
              Nome do Restaurante / Lugar *
            </label>
            <input
              type="text"
              wire:model="placeName"
              placeholder="Ex: Bottino Ristorante, Pastel do Trevo..."
              class="w-full text-sm px-4 py-2.5 rounded-xl bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-rose-500 focus:outline-none"
              required
              autofocus
            />
            @error('placeName')
              <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>
            @enderror
          </div>

          <!-- Google Maps URL -->
          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1">
              Link do Google Maps (opcional)
            </label>
            <input
              type="text"
              wire:model="placeGoogleMapsUrl"
              placeholder="Ex: https://maps.app.goo.gl/..."
              class="w-full text-sm px-4 py-2.5 rounded-xl bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-rose-500 focus:outline-none"
            />
            @error('placeGoogleMapsUrl')
              <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>
            @enderror
          </div>

          <!-- Endereço Manual -->
          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1">
              Endereço / Bairro (opcional)
            </label>
            <input
              type="text"
              wire:model="placeAddress"
              placeholder="Ex: Rio Vermelho, Pituba, Barra..."
              class="w-full text-sm px-4 py-2.5 rounded-xl bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-rose-500 focus:outline-none"
            />
            @error('placeAddress')
              <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>
            @enderror
          </div>

          <!-- Faixa de Preço -->
          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1">
              Faixa de Preço
            </label>
            <select
              wire:model="placePriceRange"
              class="w-full text-sm px-4 py-2.5 rounded-xl bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-rose-500 focus:outline-none"
            >
              @foreach ($priceRanges as $range)
                <option value="{{ $range->value }}">{{ $range->label() }}</option>
              @endforeach
            </select>
            @error('placePriceRange')
              <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>
            @enderror
          </div>

          <!-- Dicas / Recomendações -->
          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1">
              Dicas / Recomendações / Notas
            </label>
            <textarea
              wire:model="placeDescription"
              rows="3"
              placeholder="Ex: Pedir o rodízio de pizza, pedir thali de carneiro, sobremesa de chocolate..."
              class="w-full text-sm px-4 py-2.5 rounded-xl bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-rose-500 focus:outline-none"
            ></textarea>
            @error('placeDescription')
              <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>
            @enderror
          </div>

          <!-- Link Externo / Instagram -->
          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1">
              Link Externo / Instagram (opcional)
            </label>
            <input
              type="text"
              wire:model="placeExternalLink"
              placeholder="Ex: https://instagram.com/restaurante"
              class="w-full text-sm px-4 py-2.5 rounded-xl bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-rose-500 focus:outline-none"
            />
            @error('placeExternalLink')
              <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>
            @enderror
          </div>

          <!-- Checkbox Já Fui -->
          <div class="flex items-center gap-2 pt-1">
            <input
              type="checkbox"
              id="placeVisitedCheckbox"
              wire:model="placeVisited"
              class="rounded border-gray-300 text-rose-600 focus:ring-rose-500"
            />
            <label for="placeVisitedCheckbox" class="text-xs font-medium text-gray-700 dark:text-gray-300 cursor-pointer">
              Já fui neste restaurante antes
            </label>
          </div>

          <!-- Actions -->
          <div class="pt-4 flex items-center justify-end gap-3">
            <button
              type="button"
              wire:click="$set('showPlaceModal', false)"
              class="px-4 py-2 rounded-xl text-sm font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition"
            >
              Cancelar
            </button>
            <button
              type="submit"
              class="px-5 py-2 rounded-xl text-sm font-semibold text-white bg-gradient-to-r from-amber-500 to-rose-600 hover:from-amber-600 hover:to-rose-700 shadow-md transition cursor-pointer"
            >
              {{ $editingPlaceId ? 'Salvar Alterações' : 'Adicionar Lugar' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  @endif
</div>
