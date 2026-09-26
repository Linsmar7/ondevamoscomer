<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component {
  public LoginForm $form;

  /**
   * Handle an incoming authentication request.
   */
  public function login(): void {
    $this->validate();

    $this->form->authenticate();

    Session::regenerate();

    $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
  }
}; ?>

<div>
  <!-- Session Status -->
  <x-auth-session-status class="mb-4" :status="session('status')" />

  <form wire:submit="login" class="space-y-4">
    <!-- Email Address -->
    <div>
      <x-input-label for="email" :value="__('Email')" />
      <x-text-input wire:model="form.email" id="email" class="block mt-1 w-full" type="email" name="email" required autofocus autocomplete="username" />
      <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
    </div>

    <!-- Password -->
    <div>
      <x-input-label for="password" :value="__('Password')" />
      <x-text-input wire:model="form.password" id="password" class="block mt-1 w-full"
              type="password"
              name="password"
              required autocomplete="current-password" />
      <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
    </div>

    <!-- Remember Me -->
    <div class="flex items-center justify-between">
      <label for="remember" class="inline-flex items-center cursor-pointer">
        <input wire:model="form.remember" id="remember" type="checkbox" class="rounded bg-surface-container border-surface-variant text-primary focus:ring-primary focus:ring-offset-background" name="remember">
        <span class="ms-2 text-sm text-on-surface-variant font-body">{{ __('Remember me') }}</span>
      </label>
    </div>

    <div class="flex items-center justify-between pt-2">
      <a class="underline text-sm text-on-surface-variant hover:text-primary transition font-body" href="{{ route('register') }}" wire:navigate>
        Criar conta
      </a>

      <x-primary-button>
        {{ __('Log in') }}
      </x-primary-button>
    </div>
  </form>
</div>
