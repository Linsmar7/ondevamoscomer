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
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-space-lg lg:gap-space-xl items-center">
          
          <!-- Left Column: Title & CTAs -->
          <div class="lg:col-span-7 flex flex-col items-start gap-space-md">
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
            </div>
          </div>

          <!-- Right Column: Interactive Roulette Preview -->
          <div class="hidden lg:flex justify-end lg:col-span-5">
            <div class="flex flex-col items-center bg-surface-container-low border border-surface-variant/50 p-space-md lg:p-space-lg shadow-2xl relative overflow-hidden w-[340px]">
              <!-- Subtle ambient glow in background -->
              <div class="absolute -top-12 -right-12 w-36 h-36 bg-primary/10 rounded-full blur-2xl pointer-events-none"></div>
              <div class="absolute -bottom-12 -left-12 w-36 h-36 bg-secondary-container/10 rounded-full blur-2xl pointer-events-none"></div>

              <!-- Header -->
              <div class="w-full flex items-center justify-between pb-space-xs border-b border-surface-variant/30 mb-space-xs">
                <div class="flex items-center gap-1.5 text-label-sm font-label-sm uppercase tracking-wider text-primary">
                  <span class="w-2 h-2 rounded-full bg-primary animate-pulse"></span>
                  <span>Roleta Express</span>
                </div>
                <span class="text-label-sm font-label-sm px-2 py-0.5 bg-surface-container text-outline border border-surface-variant/40">
                  Teste Rápido
                </span>
              </div>

              <!-- Roulette Wheel Container -->
              <div class="relative w-[260px] h-[260px] my-space-xs flex items-center justify-center">
                <!-- Top Pointer Arrow -->
                <div class="absolute -top-2 left-1/2 -translate-x-1/2 z-20 pointer-events-none drop-shadow-md">
                  <div class="w-0 h-0 border-l-[10px] border-l-transparent border-r-[10px] border-r-transparent border-t-[16px] border-t-primary"></div>
                </div>

                <!-- The Wheel SVG (Spins) -->
                <div id="hero-wheel" class="w-full h-full will-change-transform" style="transform: rotate(0deg); transform-origin: 50% 50%;">
                  <svg viewBox="0 0 260 260" class="w-full h-full drop-shadow-lg">
                    <!-- Outer dark ring with dash -->
                    <circle cx="130" cy="130" r="126" fill="#141b2b" stroke="#2e3545" stroke-width="4"/>
                    <circle cx="130" cy="130" r="122" fill="none" stroke="#f59e0b" stroke-width="2" stroke-dasharray="6 4" opacity="0.6"/>

                    <!-- Slices -->
                    <g transform="rotate(0, 130, 130)">
                      <path d="M 130 130 L 70 26.08 A 120 120 0 0 1 190 26.08 Z" fill="#d4004b"/>
                      <text x="130" y="56" fill="#ffffff" font-size="12" font-weight="700" text-anchor="middle" font-family="'Work Sans', sans-serif">🍕 Pizza</text>
                    </g>
                    <g transform="rotate(60, 130, 130)">
                      <path d="M 130 130 L 70 26.08 A 120 120 0 0 1 190 26.08 Z" fill="#f59e0b"/>
                      <text x="130" y="56" fill="#141b2b" font-size="12" font-weight="700" text-anchor="middle" font-family="'Work Sans', sans-serif">🍔 Burger</text>
                    </g>
                    <g transform="rotate(120, 130, 130)">
                      <path d="M 130 130 L 70 26.08 A 120 120 0 0 1 190 26.08 Z" fill="#6366f1"/>
                      <text x="130" y="56" fill="#ffffff" font-size="12" font-weight="700" text-anchor="middle" font-family="'Work Sans', sans-serif">🍣 Sushi</text>
                    </g>
                    <g transform="rotate(180, 130, 130)">
                      <path d="M 130 130 L 70 26.08 A 120 120 0 0 1 190 26.08 Z" fill="#0284c7"/>
                      <text x="130" y="56" fill="#ffffff" font-size="12" font-weight="700" text-anchor="middle" font-family="'Work Sans', sans-serif">🌮 Tacos</text>
                    </g>
                    <g transform="rotate(240, 130, 130)">
                      <path d="M 130 130 L 70 26.08 A 120 120 0 0 1 190 26.08 Z" fill="#059669"/>
                      <text x="130" y="56" fill="#ffffff" font-size="12" font-weight="700" text-anchor="middle" font-family="'Work Sans', sans-serif">🍝 Massas</text>
                    </g>
                    <g transform="rotate(300, 130, 130)">
                      <path d="M 130 130 L 70 26.08 A 120 120 0 0 1 190 26.08 Z" fill="#9333ea"/>
                      <text x="130" y="56" fill="#ffffff" font-size="12" font-weight="700" text-anchor="middle" font-family="'Work Sans', sans-serif">🥩 Churras</text>
                    </g>

                    <circle cx="130" cy="130" r="120" fill="none" stroke="#141b2b" stroke-width="2"/>
                  </svg>
                </div>

                <!-- Center Hub Button -->
                <button
                  type="button"
                  id="hero-spin-btn"
                  class="absolute z-10 w-16 h-16 rounded-full bg-surface-container-high hover:bg-surface-bright flex flex-col items-center justify-center transition-transform hover:scale-105 active:scale-95 cursor-pointer shadow-xl border-2 border-primary"
                  aria-label="Girar roleta"
                >
                  <span class="font-headline-sm text-xs font-bold text-primary tracking-wider uppercase">Girar</span>
                  <span class="material-symbols-outlined text-xs text-outline -mt-0.5">casino</span>
                </button>
              </div>

              <!-- Status / Result Message -->
              <div id="hero-result" class="w-full mt-space-xs pt-space-xs text-center border-t border-surface-variant/30 min-h-[40px] flex items-center justify-center">
                <p class="font-body-sm text-body-sm text-on-surface-variant">
                  Aperte <span class="text-primary font-semibold">GIRAR</span> para sortear!
                </p>
              </div>
            </div>
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

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const btn = document.getElementById('hero-spin-btn');
      const wheel = document.getElementById('hero-wheel');
      const resultEl = document.getElementById('hero-result');

      if (!btn || !wheel || !resultEl) return;

      let isSpinning = false;
      let currentRotation = 0;

      const items = [
        { name: 'Pizza', emoji: '🍕' },
        { name: 'Burger', emoji: '🍔' },
        { name: 'Sushi', emoji: '🍣' },
        { name: 'Tacos', emoji: '🌮' },
        { name: 'Massas', emoji: '🍝' },
        { name: 'Churras', emoji: '🥩' },
      ];

      btn.addEventListener('click', () => {
        if (isSpinning) return;
        isSpinning = true;
        btn.disabled = true;

        const selectedIndex = Math.floor(Math.random() * items.length);
        const fullRotations = 5 * 360;
        const targetSliceAngle = (360 - selectedIndex * 60) % 360;
        const jitter = Math.floor(Math.random() * 26) - 13;
        const currentBase = currentRotation % 360;
        const diff = (targetSliceAngle - currentBase + 360) % 360;

        currentRotation += fullRotations + diff + jitter;

        wheel.style.transition = 'transform 3.5s cubic-bezier(0.15, 0.9, 0.25, 1.0)';
        wheel.style.transform = `rotate(${currentRotation}deg)`;

        resultEl.innerHTML = '<span class="text-primary font-medium animate-pulse text-xs">Sorteando o rango de hoje...</span>';

        setTimeout(() => {
          isSpinning = false;
          btn.disabled = false;
          const winner = items[selectedIndex];
          resultEl.innerHTML = `<span class="text-on-surface font-semibold text-xs">Hoje vai ser: <span class="text-primary font-bold">${winner.emoji} ${winner.name}!</span> Partiu?</span>`;
          if (typeof window.confetti === 'function') {
            window.confetti({
              particleCount: 50,
              spread: 60,
              origin: { y: 0.6 }
            });
          }
        }, 3600);
      });
    });
  </script>
</body>
</html>
