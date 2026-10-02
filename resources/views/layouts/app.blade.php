<!DOCTYPE html>
<html class="dark" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? (config('app.name') ?: 'Onde Vamos Comer?') }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="alternate icon" href="{{ asset('favicon.ico') }}">

    <!-- Primary Meta Tags -->
    <meta name="title" content="{{ $title ?? 'Onde Vamos Comer? · Decida sem estresse' }}">
    <meta name="description" content="Decida onde comer com os amigos sem complicação. Crie listas colaborativas e gire a roleta gastronômica!">

    <!-- Open Graph / Facebook / WhatsApp -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="{{ $title ?? 'Onde Vamos Comer? · Decida sem estresse' }}">
    <meta property="og:description" content="Decida onde comer com os amigos sem complicação. Crie listas colaborativas e gire a roleta gastronômica!">
    <meta property="og:image" content="{{ asset('og-preview.png') }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="{{ url()->current() }}">
    <meta name="twitter:title" content="{{ $title ?? 'Onde Vamos Comer? · Decida sem estresse' }}">
    <meta name="twitter:description" content="Decida onde comer com os amigos sem complicação. Crie listas colaborativas e gire a roleta gastronômica!">
    <meta name="twitter:image" content="{{ asset('og-preview.png') }}">

    <!-- Google Fonts & Material Symbols -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@600;700&family=Work+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
  </head>
  <body class="bg-surface text-on-surface font-body-md text-body-md min-h-screen flex flex-col antialiased selection:bg-primary-container selection:text-on-primary-container">
    <livewire:layout.navigation />

    <!-- Page Heading (if provided) -->
    @if (isset($header))
      <div class="pt-20 bg-surface-container-lowest border-b border-surface-variant/40">
        <div class="max-w-7xl mx-auto py-4 px-margin-mobile lg:px-margin">
          {{ $header }}
        </div>
      </div>
    @endif

    <!-- Page Content -->
    <main class="w-full flex-1 {{ isset($header) ? '' : 'pt-16' }} bg-surface flex flex-col">
      {{ $slot }}
    </main>

    <!-- Footer -->
    <footer class="w-full bg-surface-container-lowest border-t border-surface-variant/40 py-6 mt-auto">
      <div class="max-w-7xl mx-auto px-margin-mobile lg:px-margin text-center">
        <p class="font-body-sm text-body-sm text-on-surface-variant">
          Feito com 💜 por Linsmar
        </p>
      </div>
    </footer>
  </body>
</html>

