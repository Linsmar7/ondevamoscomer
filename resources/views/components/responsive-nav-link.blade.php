@props(['active'])

@php
$classes = ($active ?? false)
      ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-primary text-start text-base font-body font-semibold text-primary bg-primary/10 transition duration-150 ease-in-out'
      : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-body text-on-surface-variant hover:text-on-surface hover:bg-surface-container-high transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
  {{ $slot }}
</a>
