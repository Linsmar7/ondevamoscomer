<!DOCTYPE html>
<html class="dark" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Onde Vamos Comer?') }}</title>

    <!-- Google Fonts & Material Symbols -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@600;700&family=Work+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
  </head>
  <body class="bg-surface text-on-surface font-body-md text-body-md min-h-screen flex flex-col justify-between antialiased selection:bg-primary-container selection:text-on-primary-container">
    <header class="w-full border-b border-surface-variant/40 bg-surface-container-lowest/80 backdrop-blur-md">
      <div class="h-16 max-w-7xl mx-auto px-margin-mobile lg:px-margin flex items-center justify-between">
        <a href="/" wire:navigate class="flex items-center gap-space-sm group">
          <x-application-logo class="h-8 w-auto object-contain" />
          <span class="font-headline-sm text-headline-sm tracking-tight text-on-surface lowercase font-bold">onde vamos comer?</span>
        </a>

        <a href="/" wire:navigate class="px-space-md py-1.5 font-label-md text-label-md text-on-surface-variant hover:text-on-surface transition-colors flex items-center gap-1">
          <span class="material-symbols-outlined text-sm">arrow_back</span>
          <span>Voltar ao início</span>
        </a>
      </div>
    </header>

    <main class="flex-1 flex flex-col sm:justify-center items-center py-space-xl px-margin-mobile">
      <div class="w-full sm:max-w-md bg-surface-container-lowest border border-surface-variant p-space-lg shadow-2xl">
        {{ $slot }}
      </div>
    </main>

    <footer class="w-full bg-surface-container-lowest border-t border-surface-variant/40 py-6">
      <div class="max-w-7xl mx-auto px-margin-mobile lg:px-margin text-center">
        <p class="font-body-sm text-body-sm text-on-surface-variant">
          Feito com 💜 por Linsmar
        </p>
      </div>
    </footer>
  </body>
</html>

