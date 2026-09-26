@props(['active'])

@php
$classes = ($active ?? false)
      ? 'inline-flex items-center px-3 py-1.5 rounded-full bg-primary/10 text-primary font-body text-xs font-semibold uppercase tracking-wider transition-colors duration-150'
      : 'inline-flex items-center px-3 py-1.5 rounded-full text-on-surface-variant hover:text-on-surface hover:bg-surface-container-high font-body text-xs uppercase tracking-wider transition-colors duration-150';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
  {{ $slot }}
</a>
