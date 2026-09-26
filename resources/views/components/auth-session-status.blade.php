@props(['status'])

@if ($status)
  <div {{ $attributes->merge(['class' => 'font-medium text-sm text-tertiary-fixed font-body bg-tertiary/10 border border-tertiary/30 px-3 py-2 rounded-md']) }}>
    {{ $status }}
  </div>
@endif
