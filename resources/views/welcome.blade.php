<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Onde Vamos Comer? 🍽️ Decida onde comer na roleta</title>

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.bunny.net">
  <link href="https://fonts.bunny.net/css?family=figtree:400,600,800,900&display=swap" rel="stylesheet" />

  <!-- Scripts & Styles -->
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased font-sans bg-gray-50 text-gray-900 dark:bg-gray-950 dark:text-gray-100 min-h-screen flex flex-col justify-between selection:bg-rose-500 selection:text-white">
  <!-- Navbar -->
  <header class="max-w-7xl mx-auto w-full px-6 py-6 flex items-center justify-between">
    <div class="flex items-center gap-2">
      <span class="text-3xl">🍽️</span>
      <span class="text-xl font-black tracking-tight bg-gradient-to-r from-rose-600 via-amber-500 to-orange-500 bg-clip-text text-transparent">
        Onde Vamos Comer?
      </span>
    </div>

    <nav class="flex items-center gap-3">
      @auth
        <a
          href="{{ route('dashboard') }}"
          class="px-5 py-2.5 rounded-xl font-semibold text-sm bg-rose-600 hover:bg-rose-700 text-white shadow-md transition"
        >
          Meu Dashboard ➔
        </a>
      @else
        <a
          href="{{ route('login') }}"
          class="px-4 py-2 rounded-xl font-medium text-sm text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition"
        >
          Entrar
        </a>
        <a
          href="{{ route('register') }}"
          class="px-5 py-2.5 rounded-xl font-semibold text-sm bg-gradient-to-r from-amber-500 to-rose-600 hover:from-amber-600 hover:to-rose-700 text-white shadow-md shadow-rose-500/20 transition"
        >
          Criar Conta Grátis
        </a>
      @endauth
    </nav>
  </header>

  <!-- Hero Section -->
  <main class="max-w-7xl mx-auto px-6 py-12 lg:py-20 text-center flex-1 flex flex-col items-center justify-center">
    <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-rose-100 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-900/60 text-rose-700 dark:text-rose-300 text-xs font-bold uppercase tracking-wider mb-6 animate-pulse">
      <span>🎲</span> Fim da indecisão do casal e dos amigos
    </div>

    <h1 class="text-4xl sm:text-6xl lg:text-7xl font-black tracking-tight max-w-4xl text-gray-900 dark:text-white leading-[1.1] mb-6">
      Nunca mais passe meia hora perguntando: <br class="hidden sm:inline" />
      <span class="bg-gradient-to-r from-rose-600 via-amber-500 to-orange-500 bg-clip-text text-transparent">
        "Onde vamos comer hoje?"
      </span>
    </h1>

    <p class="text-base sm:text-xl text-gray-600 dark:text-gray-300 max-w-2xl mx-auto mb-10 leading-relaxed">
      Salve seus restaurantes, botecos e lanchonetes favoritos. Convide o mozão ou a galera para a lista compartilhada e gire a roleta gastronômica!
    </p>

    <!-- CTAs -->
    <div class="flex flex-col sm:flex-row items-center gap-4 justify-center w-full max-w-md">
      @auth
        <a
          href="{{ route('dashboard') }}"
          class="w-full sm:w-auto px-8 py-4 rounded-2xl font-black text-lg bg-gradient-to-r from-amber-500 to-rose-600 hover:from-amber-600 hover:to-rose-700 text-white shadow-xl shadow-rose-500/30 hover:scale-105 active:scale-95 transition"
        >
          Ir para Minhas Listas ➔
        </a>
      @else
        <a
          href="{{ route('register') }}"
          class="w-full sm:w-auto px-8 py-4 rounded-2xl font-black text-lg bg-gradient-to-r from-amber-500 to-rose-600 hover:from-amber-600 hover:to-rose-700 text-white shadow-xl shadow-rose-500/30 hover:scale-105 active:scale-95 transition"
        >
          Começar Agora (Grátis) 🎲
        </a>
        <a
          href="{{ route('login') }}"
          class="w-full sm:w-auto px-6 py-4 rounded-2xl font-semibold text-base bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700 transition"
        >
          Já tenho conta
        </a>
      @endauth
    </div>

    <!-- Feature Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-16 lg:mt-24 text-left w-full max-w-5xl">
      <div class="bg-white dark:bg-gray-900 rounded-3xl p-6 border border-gray-200 dark:border-gray-800 shadow-sm hover:border-rose-300 dark:hover:border-rose-900 transition">
        <div class="text-3xl mb-3">🎡</div>
        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1">Roleta Interativa</h3>
        <p class="text-sm text-gray-600 dark:text-gray-400">
          Gire a roda animada com física desacelerando, confetes na tela e filtros por preço e lugares não visitados.
        </p>
      </div>

      <div class="bg-white dark:bg-gray-900 rounded-3xl p-6 border border-gray-200 dark:border-gray-800 shadow-sm hover:border-rose-300 dark:hover:border-rose-900 transition">
        <div class="text-3xl mb-3">👥</div>
        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1">Listas Compartilhadas</h3>
        <p class="text-sm text-gray-600 dark:text-gray-400">
          Crie listas e compartilhe o link de convite com um clique. Todo mundo pode adicionar recomendações e girar.
        </p>
      </div>

      <div class="bg-white dark:bg-gray-900 rounded-3xl p-6 border border-gray-200 dark:border-gray-800 shadow-sm hover:border-rose-300 dark:hover:border-rose-900 transition">
        <div class="text-3xl mb-3">📅</div>
        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1">Google Agenda em 1 Clique</h3>
        <p class="text-sm text-gray-600 dark:text-gray-400">
          Restaurante sorteado? Adicione o compromisso ao seu Google Calendar automaticamente com local e dicas.
        </p>
      </div>
    </div>
  </main>

  <!-- Footer -->
  <footer class="max-w-7xl mx-auto w-full px-6 py-8 text-center text-xs text-gray-500 dark:text-gray-400 border-t border-gray-200 dark:border-gray-800">
    <p>Onde Vamos Comer? • Desenvolvido com Laravel 12, Livewire 3, Alpine.js & PostgreSQL</p>
  </footer>
</body>
</html>
