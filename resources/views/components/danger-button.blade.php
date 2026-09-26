<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center gap-space-xs px-space-md py-2.5 bg-secondary-container hover:bg-secondary-container/90 text-on-secondary-container font-label-md text-label-md uppercase tracking-wider transition-colors cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed']) }}>
  {{ $slot }}
</button>

