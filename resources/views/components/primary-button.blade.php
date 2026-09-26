<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center gap-space-xs px-space-lg py-2.5 bg-primary text-on-primary font-label-lg text-label-lg font-semibold uppercase tracking-wider hover:bg-primary/90 focus:outline-none transition-colors cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed shadow-md']) }}>
  {{ $slot }}
</button>

