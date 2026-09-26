@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border border-surface-variant bg-surface-container-high text-on-surface placeholder:text-outline focus:border-primary focus:ring-1 focus:ring-primary text-sm px-space-md py-2.5 rounded-none shadow-sm transition-colors']) }}>

