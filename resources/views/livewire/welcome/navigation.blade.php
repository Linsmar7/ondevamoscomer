<nav class="-mx-3 flex flex-1 justify-end">
  @auth
    <a
      href="{{ url('/dashboard') }}"
      class="px-4 py-2 font-label-md text-label-md font-semibold text-on-surface hover:text-primary hover:bg-surface-container-high transition rounded-md"
    >
      Entrar no App
    </a>
  @else
    <a
      href="{{ route('login') }}"
      class="px-4 py-2 font-label-md text-label-md text-on-surface hover:text-primary transition"
    >
      Entrar
    </a>

    @if (Route::has('register'))
      <a
        href="{{ route('register') }}"
        class="px-4 py-2 bg-primary text-on-primary font-label-md text-label-md font-semibold hover:bg-primary/90 transition shadow-sm rounded-md"
      >
        Cadastrar
      </a>
    @endif
  @endauth
</nav>
