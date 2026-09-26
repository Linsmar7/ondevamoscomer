<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component {
  /**
   * Log the current user out of the application.
   */
  public function logout(Logout $logout): void {
    $logout();

    $this->redirect('/', navigate: true);
  }
}; ?>

<header class="fixed top-0 left-0 w-full z-50 bg-surface/90 backdrop-blur-md border-b border-surface-variant/40" x-data="{ open: false, userMenu: false }">
  <div class="h-16 max-w-7xl mx-auto px-margin-mobile lg:px-margin flex items-center justify-between gap-space-md">
    <!-- Brand Logo and Title -->
    <a href="{{ route('dashboard') }}" wire:navigate class="flex items-center gap-space-sm group">
      <x-application-logo class="h-8 w-auto object-contain" />
      <span class="font-headline-sm text-headline-sm tracking-tight text-on-surface lowercase font-bold">onde vamos comer?</span>
    </a>

    <!-- Nav Links (Desktop) -->
    <nav class="hidden md:flex items-center gap-space-xs border border-surface-variant/50 p-1 bg-surface-container-lowest">
      <a
        href="{{ url('/') }}"
        wire:navigate
        class="px-space-md py-1.5 font-label-md text-label-md transition-colors rounded-none {{ request()->is('/') ? 'bg-surface-container-high text-primary font-semibold border border-outline/40' : 'text-on-surface-variant hover:text-on-surface hover:bg-surface-container' }}"
      >
        Início
      </a>
      <a
        href="{{ route('dashboard') }}"
        wire:navigate
        class="px-space-md py-1.5 font-label-md text-label-md transition-colors rounded-none {{ request()->routeIs('dashboard') || request()->routeIs('lists.*') ? 'bg-surface-container-high text-primary font-semibold border border-outline/40' : 'text-on-surface-variant hover:text-on-surface hover:bg-surface-container' }}"
      >
        Minhas Listas
      </a>
    </nav>

    <!-- User Profile Dropdown / Actions -->
    <div class="hidden md:flex items-center gap-space-sm relative">
      <div class="relative">
        <button
          type="button"
          @click="userMenu = !userMenu"
          @click.away="userMenu = false"
          class="flex items-center gap-space-xs px-2.5 py-1.5 border border-surface-variant bg-surface-container-low hover:bg-surface-container transition-colors text-on-surface font-label-md text-label-md"
        >
          <span class="w-6 h-6 flex items-center justify-center bg-surface-container-high text-primary font-bold text-xs uppercase">
            {{ substr(auth()->user()->name, 0, 1) }}
          </span>
          <span class="max-w-[140px] truncate" x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name" x-on:profile-updated.window="name = $event.detail.name"></span>
          <span class="material-symbols-outlined text-sm text-outline">expand_more</span>
        </button>

        <!-- Dropdown Box -->
        <div
          x-show="userMenu"
          x-transition
          class="absolute right-0 mt-2 w-48 bg-surface-container-high border border-surface-variant shadow-2xl py-1 z-50"
          style="display: none;"
        >
          <a
            href="{{ route('profile') }}"
            wire:navigate
            class="block px-4 py-2 font-label-md text-label-md text-on-surface hover:bg-surface-bright transition-colors"
          >
            {{ __('Profile') }}
          </a>

          <button
            wire:click="logout"
            type="button"
            class="w-full text-left px-4 py-2 font-label-md text-label-md text-error hover:bg-surface-bright transition-colors flex items-center gap-1.5"
          >
            <span class="material-symbols-outlined text-base">logout</span>
            {{ __('Log Out') }}
          </button>
        </div>
      </div>
    </div>

    <!-- Mobile Menu Toggle Button -->
    <div class="flex items-center md:hidden">
      <button
        @click="open = !open"
        type="button"
        class="p-2 text-on-surface-variant hover:text-on-surface hover:bg-surface-container transition-colors border border-surface-variant"
        aria-label="Toggle menu"
      >
        <span class="material-symbols-outlined text-xl" x-show="!open">menu</span>
        <span class="material-symbols-outlined text-xl" x-show="open" style="display: none;">close</span>
      </button>
    </div>
  </div>

  <!-- Mobile Drawer Menu -->
  <div
    x-show="open"
    x-transition
    class="md:hidden border-b border-surface-variant bg-surface-container-lowest px-margin-mobile py-space-md flex flex-col gap-space-xs"
    style="display: none;"
  >
    <a
      href="{{ url('/') }}"
      wire:navigate
      class="px-space-md py-2 font-label-md text-label-md {{ request()->is('/') ? 'bg-surface-container-high text-primary font-semibold' : 'text-on-surface-variant hover:text-on-surface hover:bg-surface-container' }}"
    >
      Início
    </a>
    <a
      href="{{ route('dashboard') }}"
      wire:navigate
      class="px-space-md py-2 font-label-md text-label-md {{ request()->routeIs('dashboard') || request()->routeIs('lists.*') ? 'bg-surface-container-high text-primary font-semibold' : 'text-on-surface-variant hover:text-on-surface hover:bg-surface-container' }}"
    >
      Minhas Listas
    </a>
    <div class="border-t border-surface-variant/50 pt-space-xs mt-space-xs flex flex-col gap-space-xs">
      <div class="px-space-md py-1 text-xs text-outline">
        Conectado como <strong class="text-on-surface" x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name" x-on:profile-updated.window="name = $event.detail.name"></strong>
      </div>
      <a
        href="{{ route('profile') }}"
        wire:navigate
        class="px-space-md py-2 font-label-md text-label-md text-on-surface-variant hover:text-on-surface hover:bg-surface-container"
      >
        {{ __('Profile') }}
      </a>
      <button
        wire:click="logout"
        type="button"
        class="w-full text-left px-space-md py-2 font-label-md text-label-md text-error hover:bg-surface-container flex items-center gap-1.5"
      >
        <span class="material-symbols-outlined text-base">logout</span>
        {{ __('Log Out') }}
      </button>
    </div>
  </div>
</header>
