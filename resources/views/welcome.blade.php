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
        <a class="px-space-md py-1.5 font-label-md text-label-md text-on-surface-variant hover:text-on-surface hover:bg-surface-container transition-colors rounded-none" href="#recursos">Recursos</a>
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
  <main class="w-full flex-1 pt-16 bg-surface">
    <!-- Hero Section -->
    <section class="relative w-full border-b border-surface-variant/40 bg-gradient-to-b from-surface-container-lowest via-surface to-surface">
      <div class="max-w-7xl mx-auto px-margin-mobile lg:px-margin pt-space-xl pb-space-xl">
        <div class="flex flex-col items-start gap-space-md max-w-3xl">
          <!-- Top Badge -->
          <div class="inline-flex items-center gap-space-xs px-2.5 py-1 bg-surface-container-low text-primary text-label-sm font-label-sm tracking-widest uppercase border border-surface-variant/50">
            <span class="w-1.5 h-1.5 bg-primary"></span>
            ZERO INDECISÃO • DIRETO AO PONTO
          </div>

          <!-- Main Title -->
          <h1 class="font-display-lg text-display-lg-mobile lg:text-display-lg tracking-tight text-on-surface font-bold">
            Acabe com o <span class="text-primary-container">“tanto faz, escolhe você”</span> na hora de comer.
          </h1>

          <!-- Subtitle -->
          <p class="font-body-lg text-body-lg text-on-surface-variant">
            Um jeito simples, direto e sem estresse para decidir onde comer com os amigos ou com o mozão. Guarde seus lugares favoritos em listas compartilhadas, gire a roleta da sorte e ninguém mais passa fome discutindo no grupo.
          </p>

          <!-- CTAs -->
          <div class="flex flex-wrap items-center gap-space-sm pt-space-sm">
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
    <section id="como-funciona" class="w-full py-space-xl border-b border-surface-variant/40 bg-surface">
      <div class="max-w-7xl mx-auto px-margin-mobile lg:px-margin">
        <div class="mb-space-lg">
          <span class="font-label-sm text-label-sm uppercase tracking-widest text-primary block mb-1">
            PASSO A PASSO
          </span>
          <h2 class="font-headline-lg text-headline-lg text-on-surface font-bold">
            Três passos e zero dor de cabeça
          </h2>
          <p class="font-body-md text-body-md text-on-surface-variant mt-1 max-w-xl">
            Sem cadastro complicado para convidados, sem anúncios e sem discussão desnecessária.
          </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-space-md">
          <!-- Step 1 -->
          <div class="bg-surface-container-low p-space-lg flex flex-col justify-between border border-surface-variant/40 shadow-sm">
            <div>
              <span class="font-headline-lg text-headline-lg text-primary font-bold block mb-space-xs">01</span>
              <h3 class="font-headline-sm text-headline-sm text-on-surface font-semibold mb-space-xs">Monte suas listas</h3>
              <p class="font-body-md text-body-md text-on-surface-variant">
                Cadastre os botecos, restaurantes, pizzarias ou hamburguerias que vocês gostam. Compartilhe o código de convite para os amigos entrarem na lista também.
              </p>
            </div>
            <div class="mt-space-lg pt-space-sm border-t border-surface-variant/30 flex items-center gap-space-xs text-outline font-label-sm text-label-sm">
              <span class="material-symbols-outlined text-sm">format_list_bulleted</span>
              <span>Listas com amigos ou a dois</span>
            </div>
          </div>

          <!-- Step 2 -->
          <div class="bg-surface-container-low p-space-lg flex flex-col justify-between border border-surface-variant/40 shadow-sm">
            <div>
              <span class="font-headline-lg text-headline-lg text-secondary-container font-bold block mb-space-xs">02</span>
              <h3 class="font-headline-sm text-headline-sm text-on-surface font-semibold mb-space-xs">Gire a roleta</h3>
              <p class="font-body-md text-body-md text-on-surface-variant">
                Bateu a fome e a indecisão? Abra a lista, filtre por faixa de preço se quiser e aperte para girar a roleta. O sorteio é 100% neutro e imparcial.
              </p>
            </div>
            <div class="mt-space-lg pt-space-sm border-t border-surface-variant/30 flex items-center gap-space-xs text-outline font-label-sm text-label-sm">
              <span class="material-symbols-outlined text-sm">casino</span>
              <span>Sorteio rápido e sem viés</span>
            </div>
          </div>

          <!-- Step 3 -->
          <div class="bg-surface-container-low p-space-lg flex flex-col justify-between border border-surface-variant/40 shadow-sm">
            <div>
              <span class="font-headline-lg text-headline-lg text-tertiary-container font-bold block mb-space-xs">03</span>
              <h3 class="font-headline-sm text-headline-sm text-on-surface font-semibold mb-space-xs">Compartilhe e partiu</h3>
              <p class="font-body-md text-body-md text-on-surface-variant">
                Copie o resultado com um toque e cole no chat da turma. A decisão foi do algoritmo, então ninguém pode reclamar de quem sugeriu!
              </p>
            </div>
            <div class="mt-space-lg pt-space-sm border-t border-surface-variant/30 flex items-center gap-space-xs text-outline font-label-sm text-label-sm">
              <span class="material-symbols-outlined text-sm">content_copy</span>
              <span>Copie o texto para colar onde quiser</span>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Recursos Section -->
    <section id="recursos" class="w-full py-space-xl border-b border-surface-variant/40 bg-surface-container-lowest">
      <div class="max-w-7xl mx-auto px-margin-mobile lg:px-margin">
        <div class="mb-space-lg">
          <span class="font-label-sm text-label-sm uppercase tracking-widest text-primary block mb-1">
            RECURSOS
          </span>
          <h2 class="font-headline-lg text-headline-lg text-on-surface font-bold">
            Tudo pensado para descomplicar seu rolê
          </h2>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-space-md">
          <!-- Feature 1 -->
          <div class="bg-surface-container p-space-md border border-surface-variant/40 flex flex-col gap-space-xs">
            <span class="material-symbols-outlined text-primary text-3xl">casino</span>
            <h4 class="font-headline-sm text-headline-sm text-on-surface font-semibold">Roleta Interativa</h4>
            <p class="font-body-sm text-body-sm text-on-surface-variant">
              Gire a roda animada com física fluida que sorteia um restaurante da lista selecionada de forma justa.
            </p>
          </div>

          <!-- Feature 2 -->
          <div class="bg-surface-container p-space-md border border-surface-variant/40 flex flex-col gap-space-xs">
            <span class="material-symbols-outlined text-secondary-container text-3xl">group_add</span>
            <h4 class="font-headline-sm text-headline-sm text-on-surface font-semibold">Listas Compartilhadas</h4>
            <p class="font-body-sm text-body-sm text-on-surface-variant">
              Convide os amigos por código de acesso. Cada participante pode visualizar a lista e acompanhar as escolhas.
            </p>
          </div>

          <!-- Feature 3 -->
          <div class="bg-surface-container p-space-md border border-surface-variant/40 flex flex-col gap-space-xs">
            <span class="material-symbols-outlined text-primary-container text-3xl">filter_alt</span>
            <h4 class="font-headline-sm text-headline-sm text-on-surface font-semibold">Filtros Inteligentes</h4>
            <p class="font-body-sm text-body-sm text-on-surface-variant">
              Filtre por faixa de preço ($ a $$$$) ou exclua restaurantes já visitados para experimentar novidades.
            </p>
          </div>

          <!-- Feature 4 -->
          <div class="bg-surface-container p-space-md border border-surface-variant/40 flex flex-col gap-space-xs">
            <span class="material-symbols-outlined text-tertiary-container text-3xl">calendar_month</span>
            <h4 class="font-headline-sm text-headline-sm text-on-surface font-semibold">Google Agenda & Maps</h4>
            <p class="font-body-sm text-body-sm text-on-surface-variant">
              Adicione o rolê diretamente na sua agenda ou abra a rota no Google Maps com apenas 1 clique.
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- Bottom CTA Banner -->
    <section class="w-full py-space-xl bg-surface">
      <div class="max-w-7xl mx-auto px-margin-mobile lg:px-margin">
        <div class="bg-surface-container-high p-space-lg lg:p-space-xl flex flex-col lg:flex-row items-start lg:items-center justify-between gap-space-md border border-surface-variant/40 shadow-xl">
          <div class="max-w-2xl">
            <div class="flex items-center gap-space-xs text-secondary-container mb-space-xs font-label-sm text-label-sm">
              <span class="material-symbols-outlined text-sm">lock_open</span>
              <span>100% GRATUITO • SEM BUROCRACIA</span>
            </div>
            <h3 class="font-headline-lg text-headline-lg text-on-surface font-bold">
              Pronto para parar de discutir onde comer?
            </h3>
            <p class="font-body-md text-body-md text-on-surface-variant mt-1">
              Crie suas listas agora mesmo, adicione seus restaurantes preferidos e deixe a roleta escolher o destino da noite.
            </p>
          </div>

          @auth
            <a
              href="{{ route('dashboard') }}"
              class="bg-primary hover:bg-primary/90 text-on-primary font-label-lg text-label-lg px-space-lg py-3 shrink-0 flex items-center gap-space-xs transition-colors font-semibold shadow-md"
            >
              <span class="material-symbols-outlined text-lg">dashboard</span>
              Ir para Minhas Listas
            </a>
          @else
            <a
              href="{{ route('register') }}"
              class="bg-primary hover:bg-primary/90 text-on-primary font-label-lg text-label-lg px-space-lg py-3 shrink-0 flex items-center gap-space-xs transition-colors font-semibold shadow-md"
            >
              <span class="material-symbols-outlined text-lg">play_arrow</span>
              Criar Conta Gratuita
            </a>
          @endauth
        </div>
      </div>
    </section>
  </main>

  <!-- Footer -->
  <footer class="w-full bg-surface-container-lowest border-t border-surface-variant/40 py-6">
    <div class="max-w-7xl mx-auto px-margin-mobile lg:px-margin text-center">
      <p class="font-body-sm text-body-sm text-on-surface-variant">
        Feito com 💜 por Linsmar
      </p>
    </div>
  </footer>
</body>
</html>
