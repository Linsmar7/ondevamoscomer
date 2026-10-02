<?php

use App\Enums\PriceRange;
use App\Models\Place;
use App\Models\RestaurantList;
use App\Models\VisitHistory;
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
  public string $rouletteStatusFilter = 'all';
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
    $rouletteCandidates = $rouletteQuery->orderBy('id')->get(['id', 'name', 'price_range', 'address', 'description'])->values();

    $pickedPlace = $this->pickedPlaceId ? Place::find($this->pickedPlaceId) : null;

    $lastWinnerHistory = VisitHistory::whereHas('place', fn($q) => $q->where('restaurant_list_id', $this->listId))
      ->with('place')
      ->latest('visited_at')
      ->first();
    $lastWinnerPlace = $lastWinnerHistory?->place;

    $mostPickedPlace = $list->places()
      ->whereHas('visitHistories')
      ->withCount('visitHistories')
      ->orderByDesc('visit_histories_count')
      ->first();

    return [
      'list' => $list,
      'places' => $places,
      'rouletteCandidates' => $rouletteCandidates,
      'pickedPlace' => $pickedPlace,
      'lastWinnerPlace' => $lastWinnerPlace,
      'lastWinnerHistory' => $lastWinnerHistory,
      'mostPickedPlace' => $mostPickedPlace,
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

    session()->flash('status', 'Restaurante removido com sucesso.');
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

<x-slot:title>
  {{ $list->name }} · {{ config('app.name', 'Onde Vamos Comer?') }}
</x-slot:title>

<div
  class="flex flex-col w-full"
  x-data="{
    candidates: {{ Js::from($rouletteCandidates) }},
    isSpinning: false,
    currentRotation: 0,
    spinsCount: 0,
    chosenWinner: null,
    copiedShare: false,

    init() {
      this.$watch('candidates', () => {
        this.renderSvgWheel();
      });
      this.$nextTick(() => {
        this.renderSvgWheel();
      });
    },

    colors: ['#141b2b', '#191f2f', '#232a3a', '#1e2433', '#2a3142'],
    textColors: ['#ffc174', '#dce2f7', '#ffb2ba', '#ffc174', '#dce2f7'],

    renderSvgWheel() {
      const container = document.getElementById('wheelSvgGroup');
      if (!container) return;

      container.innerHTML = '';
      const total = this.candidates.length;
      if (total === 0) return;

      const angleStep = 360 / total;
      const radius = 190;

      for (let i = 0; i < total; i++) {
        const startDeg = i * angleStep;
        const endDeg = (i + 1) * angleStep;
        const startRad = (startDeg * Math.PI) / 180;
        const endRad = (endDeg * Math.PI) / 180;

        const x1 = radius * Math.cos(startRad);
        const y1 = radius * Math.sin(startRad);
        const x2 = radius * Math.cos(endRad);
        const y2 = radius * Math.sin(endRad);

        const largeArc = angleStep > 180 ? 1 : 0;
        const pathData = total === 1
          ? `M 0 0 m -${radius}, 0 a ${radius},${radius} 0 1,0 ${radius * 2},0 a ${radius},${radius} 0 1,0 -${radius * 2},0`
          : `M 0 0 L ${x1} ${y1} A ${radius} ${radius} 0 ${largeArc} 1 ${x2} ${y2} Z`;

        const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
        path.setAttribute('d', pathData);
        path.setAttribute('fill', this.colors[i % this.colors.length]);
        container.appendChild(path);

        // Divider line
        if (total > 1) {
          const line = document.createElementNS('http://www.w3.org/2000/svg', 'line');
          line.setAttribute('x1', '0');
          line.setAttribute('y1', '0');
          line.setAttribute('x2', x1.toString());
          line.setAttribute('y2', y1.toString());
          line.setAttribute('stroke', '#2e3545');
          line.setAttribute('stroke-width', '2');
          container.appendChild(line);
        }

        // Segment label
        const midDeg = startDeg + angleStep / 2;
        const textG = document.createElementNS('http://www.w3.org/2000/svg', 'g');
        textG.setAttribute('transform', `rotate(${midDeg})`);

        const text = document.createElementNS('http://www.w3.org/2000/svg', 'text');
        text.setAttribute('x', (radius * 0.55).toString());
        text.setAttribute('y', '5');
        text.setAttribute('text-anchor', 'middle');
        text.setAttribute('fill', this.textColors[i % this.textColors.length]);
        text.setAttribute('font-family', 'Work Sans, sans-serif');
        text.setAttribute('font-size', total > 8 ? '9' : (total > 4 ? '10' : '11'));
        text.setAttribute('font-weight', '700');
        text.setAttribute('letter-spacing', '0.5');

        let name = this.candidates[i].name.toUpperCase();
        if (name.length > 14) name = name.substring(0, 13) + '...';
        text.textContent = name;

        textG.appendChild(text);
        container.appendChild(textG);
      }

      // Outer ring
      const ring = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
      ring.setAttribute('cx', '0');
      ring.setAttribute('cy', '0');
      ring.setAttribute('r', radius.toString());
      ring.setAttribute('fill', 'none');
      ring.setAttribute('stroke', '#2e3545');
      ring.setAttribute('stroke-width', '4');
      container.appendChild(ring);
    },

    spinWheel() {
      if (this.isSpinning || this.candidates.length === 0) return;
      this.isSpinning = true;

      const total = this.candidates.length;
      const angleStep = 360 / total;

      const selectedIndex = Math.floor(Math.random() * total);
      const chosen = this.candidates[selectedIndex];

      // Top pointer is at 270 degrees (12 o'clock)
      const extraSpins = (5 + Math.floor(Math.random() * 3)) * 360;
      const chosenMidAngle = (selectedIndex * angleStep) + (angleStep / 2);
      const targetOffset = (270 - chosenMidAngle + 3600) % 360;
      const currentMod = ((this.currentRotation % 360) + 360) % 360;
      const delta = (targetOffset - currentMod + 360) % 360;

      this.currentRotation += extraSpins + delta;

      setTimeout(() => {
        this.isSpinning = false;
        this.spinsCount += 1;
        this.chosenWinner = chosen;
        $wire.selectWinner(chosen.id);

        if (typeof window.confetti === 'function') {
          window.confetti({
            particleCount: 120,
            spread: 80,
            origin: { y: 0.6 }
          });
        }
      }, 4000);
    },

    copyShareText() {
      const winner = this.chosenWinner;
      let text = '';
      if (winner && winner.name) {
        text = `Galera, a roleta do 'Onde Vamos Comer?' decidiu: hoje o rango é no ${winner.name}! Partiu?`;
        if (winner.address) {
          text += ` 📍 ${winner.address}`;
        }
      } else {
        text = `Galera, bora decidir onde comer na lista '{{ $list->name }}'?`;
      }
      if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(() => {
          this.copiedShare = true;
          setTimeout(() => this.copiedShare = false, 2500);
        });
      }
    }
  }"
  x-effect="
    const attr = $el.getAttribute('data-candidates');
    if (attr) {
      try {
        const parsed = JSON.parse(attr);
        if (JSON.stringify(parsed) !== JSON.stringify(candidates)) {
          candidates = parsed;
          renderSvgWheel();
        }
      } catch (e) {}
    }
  "
  data-candidates="{{ json_encode($rouletteCandidates) }}"
>
  <div class="max-w-7xl mx-auto w-full px-margin-mobile lg:px-margin py-space-xl">
    <!-- Breadcrumb & Top Bar -->
    <div class="flex items-center justify-between pb-space-md">
      <a
        href="{{ route('dashboard') }}"
        wire:navigate
        class="inline-flex items-center gap-1.5 font-label-md text-label-md text-on-surface-variant hover:text-on-surface transition-colors"
      >
        <span class="material-symbols-outlined text-sm">arrow_back</span>
        <span>Voltar para Minhas Listas</span>
      </a>

      <!-- Share List Invite Link -->
      <div x-data="{ copied: false }" class="flex items-center gap-space-xs">
        <button
          type="button"
          @click="
            navigator.clipboard.writeText('{{ url('/lists/join/' . $list->invite_code) }}');
            copied = true;
            setTimeout(() => copied = false, 2000);
          "
          class="px-space-md py-1.5 bg-surface-container-high hover:bg-surface-bright text-on-surface font-label-md text-label-md transition-colors border border-surface-variant flex items-center gap-1.5 cursor-pointer"
        >
          <span class="material-symbols-outlined text-sm text-primary">share</span>
          <span x-text="copied ? 'Link Copiado! 🎉' : 'Convidar Amigos'"></span>
        </button>
      </div>
    </div>

    <!-- Top Headline Section -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-space-lg pb-space-xl">
      <div class="flex flex-col gap-space-xs max-w-3xl">
        <h1 class="font-display-lg text-display-lg-mobile md:text-display-lg tracking-tight text-on-surface font-bold">
          {{ $list->name }}
        </h1>
        @if ($list->description)
          <p class="font-body-lg text-body-lg text-on-surface-variant mt-1">
            {{ $list->description }}
          </p>
        @endif
      </div>
    </div>

    <!-- Feedback Message -->
    @if (session('status'))
      <div class="mb-space-lg p-space-md bg-surface-container-low border border-primary/40 text-on-surface flex items-center justify-between">
        <div class="flex items-center gap-space-xs text-primary font-body-md text-body-md">
          <span class="material-symbols-outlined text-lg">check_circle</span>
          <span>{{ session('status') }}</span>
        </div>
        <button type="button" @click="$el.parentElement.remove()" class="text-outline hover:text-on-surface">&times;</button>
      </div>
    @endif

    <!-- Main Grid: 7 cols (Roulette) + 5 cols (Places List) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-gutter items-start">
      <!-- Left Column: The Interactive Roulette -->
      <div class="lg:col-span-7 flex flex-col gap-space-md">
        <div class="bg-surface-container-lowest p-space-lg relative flex flex-col items-center border border-surface-variant/40 shadow-xl">
          <!-- Status Bar -->
          <div class="w-full flex flex-wrap items-center justify-between gap-space-sm pb-space-lg">
            <span class="font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant flex items-center gap-1.5">
              <span class="w-2 h-2 bg-secondary-container inline-block animate-pulse"></span>
              RODA ATIVA: <strong class="text-on-surface ml-1" x-text="`${candidates.length} OPÇÕES`"></strong>
            </span>
            <div class="flex items-center gap-space-xs">
              <button
                type="button"
                wire:click="$set('roulettePriceFilter', 'all'); $set('rouletteStatusFilter', 'all');"
                class="px-space-sm py-1 bg-surface-container text-on-surface-variant hover:text-on-surface font-label-sm text-label-sm uppercase transition-colors cursor-pointer"
              >
                Resetar Filtros
              </button>
            </div>
          </div>

          <!-- The Roulette Disk -->
          <div class="relative w-72 h-72 sm:w-80 sm:h-80 md:w-96 md:h-96 my-space-md flex items-center justify-center">
            <!-- Downward Pointer Arrow -->
            <div class="absolute -top-3 left-1/2 -translate-x-1/2 z-30 flex flex-col items-center pointer-events-none drop-shadow-md">
              <div class="w-0 h-0 border-l-[14px] border-l-transparent border-r-[14px] border-r-transparent border-t-[22px] border-t-primary-container filter drop-shadow"></div>
            </div>

            <!-- Rotating Disk Container -->
            <div
              class="w-full h-full relative"
              id="roulette-container"
              wire:ignore
              style="transform-origin: 50% 50%; transition: transform 4s cubic-bezier(0.15, 0.9, 0.25, 1.0);"
              :style="{ transform: `rotate(${currentRotation}deg)` }"
            >
              <svg class="w-full h-full drop-shadow-2xl" viewBox="0 0 400 400">
                <g id="wheelSvgGroup" transform="translate(200, 200)"></g>
                <!-- Native SVG Center Hub Pin (cannot be square) -->
                <circle cx="200" cy="200" r="22" fill="#141b2b" stroke="#2e3545" stroke-width="4"/>
                <circle cx="200" cy="200" r="14" fill="#232a3a" stroke="#f59e0b" stroke-width="2"/>
                <circle cx="200" cy="200" r="5" fill="#f43f5e"/>
              </svg>
            </div>
          </div>

          <!-- Quick Filter Chips -->
          <div class="w-full flex items-center justify-center flex-wrap gap-space-xs pt-space-md border-t border-surface-variant/30">
            <span class="font-label-sm text-label-sm uppercase tracking-wider text-outline mr-1">Filtros:</span>

            <button
              type="button"
              wire:click="$set('roulettePriceFilter', 'all')"
              class="px-space-sm py-1 font-label-md text-label-md font-semibold transition-colors cursor-pointer {{ $roulettePriceFilter === 'all' ? 'bg-primary text-on-primary' : 'bg-surface-container text-on-surface-variant hover:text-on-surface' }}"
            >
              Todos os preços
            </button>

            @foreach ($priceRanges as $range)
              <button
                type="button"
                wire:click="$set('roulettePriceFilter', '{{ $range->value }}')"
                class="px-space-sm py-1 font-label-md text-label-md font-semibold transition-colors cursor-pointer {{ $roulettePriceFilter === $range->value ? 'bg-primary text-on-primary' : 'bg-surface-container text-on-surface-variant hover:text-on-surface' }}"
              >
                {{ $range->value }}
              </button>
            @endforeach

            <button
              type="button"
              wire:click="$set('rouletteStatusFilter', '{{ $rouletteStatusFilter === 'not_visited' ? 'all' : 'not_visited' }}')"
              class="px-space-sm py-1 font-label-md text-label-md font-semibold transition-colors cursor-pointer {{ $rouletteStatusFilter === 'not_visited' ? 'bg-secondary-container text-on-secondary-container' : 'bg-surface-container text-on-surface-variant hover:text-on-surface' }}"
            >
              {{ $rouletteStatusFilter === 'not_visited' ? '✓ Apenas não visitados' : 'Todos os status' }}
            </button>
          </div>

          <template x-if="candidates.length === 0">
            <p class="mt-3 text-xs text-error font-medium">
              Nenhum restaurante encontrado com os filtros selecionados! Adicione novos locais ou ajuste os filtros.
            </p>
          </template>
        </div>

        <!-- Spin Action Button (Full Width) -->
        <div class="w-full flex flex-col gap-space-md">
          <button
            type="button"
            id="spin-main-btn"
            @click="spinWheel()"
            :disabled="isSpinning || candidates.length === 0"
            class="w-full py-space-md px-space-xl bg-secondary-container hover:bg-secondary-container/90 text-on-secondary-container font-headline-sm text-headline-sm uppercase tracking-wider font-bold transition-all flex items-center justify-center gap-space-xs shadow-lg cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span class="material-symbols-outlined text-2xl" :class="{ 'animate-spin': isSpinning }">restart_alt</span>
            <span x-text="isSpinning ? 'Girando a sorte...' : 'Girar a Roleta Agora'"></span>
          </button>

          <!-- Winner Announcement Card (Appears after spin or when a place was picked) -->
          <div
            x-show="!isSpinning && chosenWinner"
            x-cloak
            class="bg-surface-container-low p-space-md border border-primary/40 shadow-lg flex flex-col sm:flex-row sm:items-center justify-between gap-space-md w-full"
          >
            <div class="flex flex-col gap-0.5 min-w-0">
              <span class="font-label-sm text-label-sm uppercase tracking-wider text-primary font-bold flex items-center gap-1">
                <span class="material-symbols-outlined text-base">celebration</span>
                Restaurante Sorteado!
              </span>
              <div class="font-headline-sm text-headline-sm font-bold text-on-surface truncate" x-text="chosenWinner ? chosenWinner.name : ''"></div>
              <p class="font-body-sm text-body-sm text-on-surface-variant truncate" x-text="chosenWinner && chosenWinner.address ? `📍 ${chosenWinner.address}` : ''"></p>
            </div>

            <!-- Winner Actions -->
            <div class="flex flex-wrap items-center gap-space-xs shrink-0">
              @if ($pickedPlace)
                <a
                  href="{{ $pickedPlace->googleCalendarUrl() }}"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="px-space-md py-1.5 bg-surface-container-high hover:bg-surface-bright text-on-surface text-xs font-semibold flex items-center gap-1.5 transition-colors border border-surface-variant"
                >
                  <span class="material-symbols-outlined text-sm text-primary">calendar_today</span>
                  Google Agenda
                </a>

                @if ($pickedPlace->google_maps_url)
                  <a
                    href="{{ $pickedPlace->google_maps_url }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="px-space-md py-1.5 bg-surface-container-high hover:bg-surface-bright text-on-surface text-xs font-semibold flex items-center gap-1.5 transition-colors border border-surface-variant"
                  >
                    <span class="material-symbols-outlined text-sm text-secondary">map</span>
                    Maps
                  </a>
                @endif

                <button
                  type="button"
                  wire:click="toggleVisited({{ $pickedPlace->id }})"
                  class="px-space-md py-1.5 bg-surface-container-high hover:bg-surface-bright text-xs font-semibold transition-colors border border-surface-variant {{ $pickedPlace->visited ? 'text-primary' : 'text-on-surface-variant' }}"
                >
                  {{ $pickedPlace->visited ? '✓ Já Marcado como Fui' : 'Marcar como Fui' }}
                </button>
              @endif
            </div>
          </div>
        </div>
      </div>

      <!-- Right Column: Lugares (List & Configuration) -->
      <div class="lg:col-span-5 flex flex-col gap-space-md">
        <div class="bg-surface-container-lowest p-space-lg flex flex-col gap-space-md border border-surface-variant/40 shadow-xl">
          <div class="flex items-center justify-between gap-space-sm pb-space-sm border-b border-surface-variant/30">
            <div>
              <h2 class="font-headline-md text-headline-md text-on-surface font-bold">Lugares</h2>
              <span class="font-body-sm text-body-sm text-on-surface-variant">Restaurantes cadastrados na lista</span>
            </div>
            <button
              type="button"
              wire:click="openCreatePlaceModal"
              class="px-space-sm py-1.5 bg-surface-container hover:bg-surface-container-high text-primary font-label-md text-label-md uppercase tracking-wider flex items-center gap-1 transition-colors cursor-pointer"
            >
              <span class="material-symbols-outlined text-base">add</span>
              <span>+ Adicionar local</span>
            </button>
          </div>

          <!-- Places Search inside List -->
          <div class="relative">
            <input
              type="text"
              wire:model.live.debounce.250ms="search"
              placeholder="Filtrar por nome, endereço..."
              class="w-full bg-surface-container border border-surface-variant text-on-surface placeholder:text-outline px-space-md py-1.5 text-xs focus:border-primary focus:outline-none"
            />
            @if ($search)
              <button
                type="button"
                wire:click="$set('search', '')"
                class="absolute right-2 top-2 text-outline hover:text-on-surface text-xs"
              >
                &times;
              </button>
            @endif
          </div>

          <!-- Places List -->
          <div class="flex flex-col gap-1.5 max-h-[460px] overflow-y-auto pr-1" id="places-list">
            @forelse ($places as $place)
              <div
                wire:key="place-item-{{ $place->id }}"
                class="flex items-center justify-between p-space-sm bg-surface-container hover:bg-surface-container-high transition-colors"
              >
                <div class="flex items-center gap-space-sm min-w-0 flex-1">
                  <div class="w-8 h-8 bg-surface-container-highest flex items-center justify-center text-primary shrink-0">
                    <span class="material-symbols-outlined text-[18px]">restaurant</span>
                  </div>
                  <div class="flex flex-col min-w-0">
                    <span class="font-body-md text-body-md text-on-surface font-medium truncate">
                      {{ $place->name }}
                    </span>
                    @if ($place->address)
                      <span class="font-body-sm text-body-sm text-outline truncate text-[11px]">
                        {{ $place->address }}
                      </span>
                    @endif
                  </div>
                </div>

                <div class="flex items-center gap-space-xs shrink-0 ml-2">
                  @if ($place->price_range)
                    <span class="px-2 py-0.5 bg-surface-container-lowest text-primary font-label-sm text-label-sm font-bold">
                      {{ $place->price_range->value }}
                    </span>
                  @endif

                  <!-- Google Maps Button -->
                  @if ($place->google_maps_url)
                    <a
                      href="{{ $place->google_maps_url }}"
                      target="_blank"
                      rel="noopener noreferrer"
                      class="text-outline hover:text-primary transition-colors flex items-center p-1"
                      title="Abrir no Google Maps"
                    >
                      <span class="material-symbols-outlined text-base">map</span>
                    </a>
                  @endif

                  <!-- Google Calendar Intent Button -->
                  <a
                    href="{{ $place->googleCalendarUrl() }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="text-outline hover:text-primary transition-colors flex items-center p-1"
                    title="Adicionar ao Google Agenda"
                  >
                    <span class="material-symbols-outlined text-base">calendar_add_on</span>
                  </a>

                  <!-- Edit Button -->
                  <button
                    type="button"
                    wire:click="openEditPlaceModal({{ $place->id }})"
                    class="text-outline hover:text-on-surface transition-colors flex items-center p-1 cursor-pointer"
                    title="Editar"
                  >
                    <span class="material-symbols-outlined text-base">edit</span>
                  </button>

                  <!-- Delete Button -->
                  <button
                    type="button"
                    wire:click="deletePlace({{ $place->id }})"
                    wire:confirm="Tem certeza que deseja excluir '{{ $place->name }}'?"
                    class="text-outline hover:text-error transition-colors flex items-center p-1 cursor-pointer"
                    title="Remover"
                  >
                    <span class="material-symbols-outlined text-base">delete</span>
                  </button>
                </div>
              </div>
            @empty
              <div class="p-space-lg text-center text-outline font-body-sm text-body-sm">
                Nenhum restaurante cadastrado nesta lista ainda. Clique acima para adicionar!
              </div>
            @endforelse
          </div>

          <!-- Share Action -->
          <div class="pt-space-xs border-t border-surface-variant/30">
            <button
              type="button"
              @click="copyShareText()"
              class="w-full py-space-sm px-space-md bg-surface-container-high hover:bg-surface-variant text-on-surface font-label-lg text-label-lg font-semibold flex items-center justify-center gap-space-xs transition-colors cursor-pointer"
            >
              <span class="material-symbols-outlined text-primary text-xl" x-text="copiedShare ? 'check' : 'share'"></span>
              <span x-text="copiedShare ? 'Texto copiado!' : 'Compartilhar'"></span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Create / Edit Place Modal -->
  @if ($showPlaceModal)
    <div class="fixed inset-0 z-50 overflow-y-auto bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
      <div
        class="bg-surface-container border border-surface-variant p-space-lg shadow-2xl max-w-lg w-full relative"
        @click.away="$wire.set('showPlaceModal', false)"
      >
        <div class="flex items-center justify-between pb-space-md border-b border-surface-variant/50 mb-space-md">
          <div class="flex items-center gap-space-xs">
            <span class="material-symbols-outlined text-primary text-xl">restaurant</span>
            <h3 class="font-headline-md text-headline-md text-on-surface font-bold">
              {{ $editingPlaceId ? 'Editar Restaurante' : 'Novo Restaurante' }}
            </h3>
          </div>
          <button
            type="button"
            wire:click="$set('showPlaceModal', false)"
            class="text-outline hover:text-on-surface transition-colors"
          >
            <span class="material-symbols-outlined text-xl">close</span>
          </button>
        </div>

        <form wire:submit="savePlace" class="flex flex-col gap-space-md">
          <!-- Nome -->
          <div>
            <label class="block font-label-sm text-label-sm uppercase tracking-wider text-outline mb-1">
              Nome do Restaurante / Lugar *
            </label>
            <input
              type="text"
              wire:model="placeName"
              placeholder="Ex: Bottino Ristorante, Pastel da Feira, Bar do Zé..."
              class="w-full bg-surface-container-high border border-surface-variant text-on-surface placeholder:text-outline px-space-md py-2.5 font-body-md focus:border-primary focus:outline-none"
              required
              autofocus
            />
            @error('placeName')
              <p class="mt-1 text-xs text-error font-medium">{{ $message }}</p>
            @enderror
          </div>

          <!-- Endereço -->
          <div>
            <label class="block font-label-sm text-label-sm uppercase tracking-wider text-outline mb-1">
              Endereço / Bairro (opcional)
            </label>
            <input
              type="text"
              wire:model="placeAddress"
              placeholder="Ex: Rio Vermelho, Pinheiros, Rua Augusta..."
              class="w-full bg-surface-container-high border border-surface-variant text-on-surface placeholder:text-outline px-space-md py-2.5 font-body-md focus:border-primary focus:outline-none"
            />
            @error('placeAddress')
              <p class="mt-1 text-xs text-error font-medium">{{ $message }}</p>
            @enderror
          </div>

          <!-- Faixa de Preço -->
          <div>
            <label class="block font-label-sm text-label-sm uppercase tracking-wider text-outline mb-1">
              Faixa de Preço
            </label>
            <select
              wire:model="placePriceRange"
              class="w-full bg-surface-container-high border border-surface-variant text-on-surface px-space-md py-2.5 font-body-md focus:border-primary focus:outline-none"
            >
              @foreach ($priceRanges as $range)
                <option value="{{ $range->value }}">{{ $range->label() }}</option>
              @endforeach
            </select>
            @error('placePriceRange')
              <p class="mt-1 text-xs text-error font-medium">{{ $message }}</p>
            @enderror
          </div>

          <!-- Dicas / Notas -->
          <div>
            <label class="block font-label-sm text-label-sm uppercase tracking-wider text-outline mb-1">
              Dicas / O que pedir (opcional)
            </label>
            <textarea
              wire:model="placeDescription"
              rows="3"
              placeholder="Ex: Pedir o pastel de camarão com catupiry e caldo de cana..."
              class="w-full bg-surface-container-high border border-surface-variant text-on-surface placeholder:text-outline px-space-md py-2.5 font-body-md focus:border-primary focus:outline-none"
            ></textarea>
            @error('placeDescription')
              <p class="mt-1 text-xs text-error font-medium">{{ $message }}</p>
            @enderror
          </div>

          <!-- Google Maps Link -->
          <div>
            <label class="block font-label-sm text-label-sm uppercase tracking-wider text-outline mb-1">
              Link do Google Maps (opcional)
            </label>
            <input
              type="text"
              wire:model="placeGoogleMapsUrl"
              placeholder="Ex: https://maps.app.goo.gl/..."
              class="w-full bg-surface-container-high border border-surface-variant text-on-surface placeholder:text-outline px-space-md py-2.5 font-body-md focus:border-primary focus:outline-none"
            />
            @error('placeGoogleMapsUrl')
              <p class="mt-1 text-xs text-error font-medium">{{ $message }}</p>
            @enderror
          </div>

          <!-- Link Externo / Instagram -->
          <div>
            <label class="block font-label-sm text-label-sm uppercase tracking-wider text-outline mb-1">
              Link Externo / Instagram (opcional)
            </label>
            <input
              type="text"
              wire:model="placeExternalLink"
              placeholder="Ex: https://instagram.com/restaurante"
              class="w-full bg-surface-container-high border border-surface-variant text-on-surface placeholder:text-outline px-space-md py-2.5 font-body-md focus:border-primary focus:outline-none"
            />
            @error('placeExternalLink')
              <p class="mt-1 text-xs text-error font-medium">{{ $message }}</p>
            @enderror
          </div>

          <!-- Checkbox Já Fui -->
          <div class="flex items-center gap-space-xs pt-1">
            <input
              type="checkbox"
              id="placeVisitedCheckbox"
              wire:model="placeVisited"
              class="w-4 h-4 rounded-none accent-primary-container bg-surface-container-highest cursor-pointer border-surface-variant"
            />
            <label for="placeVisitedCheckbox" class="font-body-sm text-body-sm text-on-surface cursor-pointer select-none">
              Já fui neste restaurante antes
            </label>
          </div>

          <!-- Actions -->
          <div class="pt-space-sm flex items-center justify-end gap-space-sm border-t border-surface-variant/30">
            <button
              type="button"
              wire:click="$set('showPlaceModal', false)"
              class="px-space-md py-2 bg-surface-container-high hover:bg-surface-bright text-on-surface font-label-md text-label-md transition-colors cursor-pointer"
            >
              Cancelar
            </button>
            <button
              type="submit"
              class="px-space-lg py-2 bg-primary hover:bg-primary/90 text-on-primary font-label-md text-label-md font-semibold transition-colors shadow-md cursor-pointer"
            >
              {{ $editingPlaceId ? 'Salvar Alterações' : 'Adicionar Local' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  @endif
</div>
