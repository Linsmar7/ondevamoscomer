<!DOCTYPE html>
<html class="dark" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Onde Vamos Comer? • Decida sem complicação</title>

  <!-- Google Fonts & Material Symbols -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@600;700&family=Work+Sans:wght@400;500;600&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">

  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-surface text-on-surface font-body-md text-body-md min-h-screen flex flex-col antialiased selection:bg-primary-container selection:text-on-primary-container">
  <!-- Sticky Header -->
  <header class="fixed top-0 left-0 w-full z-50 bg-surface/90 backdrop-blur-md border-b border-surface-variant/40">
    <div class="h-16 max-w-7xl mx-auto px-margin-mobile lg:px-margin flex items-center justify-between gap-space-md">
      <!-- Brand Logo -->
      <a href="/" class="flex items-center gap-space-sm group">
        <x-application-logo class="h-8 w-auto object-contain" />
        <span class="font-headline-sm text-headline-sm tracking-tight text-on-surface lowercase font-bold">onde vamos comer?</span>
      </a>

      <!-- Navigation Links -->
      <nav class="hidden md:flex items-center gap-space-xs border border-surface-variant/50 p-1 bg-surface-container-lowest">
        <a class="px-space-md py-1.5 font-label-md text-label-md bg-surface-container-high text-primary font-semibold border border-outline/40 transition-colors rounded-none" href="/">Início</a>
        <a class="px-space-md py-1.5 font-label-md text-label-md text-on-surface-variant hover:text-on-surface hover:bg-surface-container transition-colors rounded-none" href="#como-funciona">Como Funciona</a>
        @auth
          <a class="px-space-md py-1.5 font-label-md text-label-md text-on-surface-variant hover:text-on-surface hover:bg-surface-container transition-colors rounded-none" href="{{ route('dashboard') }}">Minhas Listas</a>
        @endauth
      </nav>

      <!-- Auth Actions -->
      <div class="flex items-center gap-space-sm">
        @auth
          <a
            href="{{ route('dashboard') }}"
            class="px-space-md py-2 border border-surface-variant bg-surface-container-low hover:bg-surface-container font-label-md text-label-md text-primary font-semibold transition-colors flex items-center gap-1.5"
          >
            <span class="material-symbols-outlined text-sm">dashboard</span>
            <span>Minhas Listas</span>
          </a>
        @else
          <a
            href="{{ route('login') }}"
            class="hidden sm:inline-block px-space-md py-2 font-label-md text-label-md text-on-surface-variant hover:text-on-surface transition-colors"
          >
            Entrar
          </a>
          <a
            href="{{ route('register') }}"
            class="bg-primary hover:bg-primary/90 text-on-primary font-label-md text-label-md font-semibold px-space-md py-2 transition-colors flex items-center gap-1 shadow-md"
          >
            <span class="material-symbols-outlined text-sm">person_add</span>
            <span>Cadastrar</span>
          </a>
        @endauth
      </div>
    </div>
  </header>

  <!-- Main Content -->
  <main class="w-full flex-1 pt-16 bg-surface flex flex-col justify-between">
    <!-- Hero Section -->
    <section class="relative w-full flex-1 flex flex-col justify-center border-b border-surface-variant/40 bg-gradient-to-b from-surface-container-lowest via-surface to-surface py-space-xl lg:py-16">
      <div class="max-w-7xl mx-auto px-margin-mobile lg:px-margin w-full">
        <div class="flex flex-col items-start gap-space-md max-w-4xl">
          <!-- Top Badge -->
          <div class="inline-flex items-center gap-space-xs px-2.5 py-1 bg-surface-container-low text-primary text-label-sm font-label-sm tracking-widest uppercase border border-surface-variant/50">
            <span class="w-1.5 h-1.5 bg-primary"></span>
            ZERO INDECISÃO • DIRETO AO PONTO
          </div>

          <!-- Main Title -->
          <h1 class="font-headline-lg text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-on-surface leading-[1.12]">
            Acabe com o <span class="text-primary-container">“tanto faz, escolhe você”</span> na hora de comer.
          </h1>

          <!-- CTAs -->
          <div class="flex flex-wrap items-center gap-space-sm pt-space-xs">
            @auth
              <a
                href="{{ route('dashboard') }}"
                class="bg-primary hover:bg-primary/90 text-on-primary px-space-lg py-3 font-label-lg text-label-lg flex items-center gap-space-xs transition-colors shadow-md font-semibold"
              >
                <span class="material-symbols-outlined text-lg">restaurant_menu</span>
                Acessar Minhas Listas
              </a>
            @else
              <a
                href="{{ route('register') }}"
                class="bg-primary hover:bg-primary/90 text-on-primary px-space-lg py-3 font-label-lg text-label-lg flex items-center gap-space-xs transition-colors shadow-md font-semibold"
              >
                <span class="material-symbols-outlined text-lg">rocket_launch</span>
                Criar Conta Gratuita
              </a>
              <a
                href="{{ route('login') }}"
                class="bg-surface-container-high hover:bg-surface-bright text-on-surface px-space-lg py-3 font-label-lg text-label-lg flex items-center gap-space-xs transition-colors border border-surface-variant/60"
              >
                <span class="material-symbols-outlined text-lg">login</span>
                Já Tenho Conta
              </a>
            @endauth

            <a
              href="#como-funciona"
              class="px-space-md py-3 font-label-lg text-label-lg text-on-surface-variant hover:text-on-surface transition-colors flex items-center gap-1"
            >
              <span>Entenda como funciona</span>
              <span class="material-symbols-outlined text-sm">arrow_downward</span>
            </a>
          </div>
        </div>
      </div>
    </section>

    <!-- Como Funciona Section -->
    <section id="como-funciona" class="w-full py-space-lg lg:py-space-xl bg-surface">
      <div class="max-w-7xl mx-auto px-margin-mobile lg:px-margin">
        <div class="mb-space-md">
          <span class="font-label-sm text-label-sm uppercase tracking-widest text-primary block mb-1">
            PASSO A PASSO
          </span>
          <h2 class="font-headline-lg text-headline-lg text-on-surface font-bold">
            Três passos e zero dor de cabeça
          </h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-space-md">
          <!-- Step 1 -->
          <div class="bg-surface-container-low p-space-md lg:p-space-lg flex flex-col border border-surface-variant/40 shadow-sm">
            <span class="font-headline-lg text-headline-lg text-primary font-bold block mb-space-xs">01</span>
            <h3 class="font-headline-sm text-headline-sm text-on-surface font-semibold mb-space-xs">Monte suas listas</h3>
            <p class="font-body-md text-body-md text-on-surface-variant">
              Cadastre os botecos, restaurantes, pizzarias ou hamburguerias que vocês gostam. Compartilhe o código de convite para os amigos entrarem na lista também.
            </p>
          </div>

          <!-- Step 2 -->
          <div class="bg-surface-container-low p-space-md lg:p-space-lg flex flex-col border border-surface-variant/40 shadow-sm">
            <span class="font-headline-lg text-headline-lg text-secondary-container font-bold block mb-space-xs">02</span>
            <h3 class="font-headline-sm text-headline-sm text-on-surface font-semibold mb-space-xs">Gire a roleta</h3>
            <p class="font-body-md text-body-md text-on-surface-variant">
              Bateu a fome e a indecisão? Abra a lista, filtre por faixa de preço se quiser e aperte para girar a roleta. O sorteio é 100% neutro e imparcial.
            </p>
          </div>

          <!-- Step 3 -->
          <div class="bg-surface-container-low p-space-md lg:p-space-lg flex flex-col border border-surface-variant/40 shadow-sm">
            <span class="font-headline-lg text-headline-lg text-tertiary-container font-bold block mb-space-xs">03</span>
            <h3 class="font-headline-sm text-headline-sm text-on-surface font-semibold mb-space-xs">Compartilhe e partiu</h3>
            <p class="font-body-md text-body-md text-on-surface-variant">
              Copie o resultado com um toque e cole no chat da turma. A decisão foi do algoritmo, então ninguém pode reclamar de quem sugeriu!
            </p>
          </div>
        </div>
      </div>
    </section>
  </main>

  <!-- Footer -->
  <footer class="w-full bg-surface-container-lowest border-t border-surface-variant/40 py-4 shrink-0">
    <div class="max-w-7xl mx-auto px-margin-mobile lg:px-margin text-center">
      <p class="font-body-sm text-body-sm text-on-surface-variant">
        Feito com 💜 por Linsmar
      </p>
    </div>
  </footer>
</body>
</html>
