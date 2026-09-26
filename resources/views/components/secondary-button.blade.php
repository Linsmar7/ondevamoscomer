<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center gap-space-xs px-space-md py-2.5 bg-surface-container-high hover:bg-surface-bright text-on-surface border border-surface-variant font-label-md text-label-md uppercase tracking-wider transition-colors cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed']) }}>
  {{ $slot }}
</button>

