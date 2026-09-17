@props([
    'type' => 'submit',
    'variant' => 'primary',
])

@php
    $classes = match ($variant) {
        'secondary' => 'border border-stone-200 bg-white text-stone-700 hover:border-forest-300 hover:bg-forest-50',
        'ghost' => 'text-stone-600 hover:bg-stone-100 hover:text-forest-800',
        default => 'bg-forest-600 text-white shadow-lg shadow-forest-900/10 hover:bg-forest-700',
    };
@endphp

<button
    type='{{ $type }}'
    {{ $attributes->merge(['class' => 'inline-flex min-h-11 items-center justify-center gap-2 rounded-xl px-5 py-2.5 text-sm font-bold transition duration-200 focus-visible:outline-2 focus-visible:outline-offset-2 disabled:cursor-not-allowed disabled:opacity-60 '.$classes]) }}
>
    {{ $slot }}
</button>
