@props(['messages'])

@if ($messages)
  <ul {{ $attributes->merge(['class' => 'text-xs text-error font-body space-y-1 mt-1']) }}>
    @foreach ((array) $messages as $message)
      <li>{{ $message }}</li>
    @endforeach
  </ul>
@endif
