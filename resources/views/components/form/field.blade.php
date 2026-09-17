@props([
    'name',
    'label',
    'type' => 'text',
    'value' => null,
    'autocomplete' => null,
    'required' => false,
])

<div class='space-y-2'>
    <label for='{{ $name }}' class='block text-sm font-bold text-stone-700'>
        {{ $label }}
        @unless ($required)
            <span class='font-normal text-stone-400'>{{ $type === 'tel' ? '(opsional)' : '' }}</span>
        @endunless
    </label>

    <input
        id='{{ $name }}'
        name='{{ $name }}'
        type='{{ $type }}'
        value='{{ old($name, $value) }}'
        @if ($autocomplete) autocomplete='{{ $autocomplete }}' @endif
        @if ($required) required @endif
        {{ $attributes->merge(['class' => 'block min-h-12 w-full rounded-xl border bg-white px-4 py-3 text-sm text-stone-900 shadow-sm transition placeholder:text-stone-400 focus:border-forest-500 focus:ring-4 focus:ring-forest-100 '.($errors->has($name) ? 'border-red-300' : 'border-stone-200')]) }}
    >

    @error($name)
        <p class='text-sm font-medium text-red-600'>{{ $message }}</p>
    @enderror
</div>
