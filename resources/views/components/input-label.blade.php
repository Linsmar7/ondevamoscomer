@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-label-sm text-label-sm uppercase tracking-wider text-outline mb-1']) }}>
  {{ $value ?? $slot }}
</label>

