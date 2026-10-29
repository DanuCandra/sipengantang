@props([
    'name',
    'label',
    'type' => 'text',
    'value' => null,
    'autocomplete' => null,
    'required' => false,
    'id' => null,
])

@php($fieldId = $id ?? $name)

<div class='space-y-2'>
    <label for='{{ $fieldId }}' class='block text-sm font-bold text-stone-700'>
        {{ $label }}
        @unless ($required)
            <span class='font-normal text-stone-600'>{{ $type === 'tel' ? '(opsional)' : '' }}</span>
        @endunless
    </label>

    <input
        id='{{ $fieldId }}'
        name='{{ $name }}'
        type='{{ $type }}'
        value='{{ old($name, $value) }}'
        @if ($autocomplete) autocomplete='{{ $autocomplete }}' @endif
        @if ($required) required @endif
        @if ($errors->has($name)) aria-invalid="true" aria-describedby="{{ trim($attributes->get('aria-describedby', '').' '.$fieldId.'-error') }}" @endif
        {{ $attributes->except($errors->has($name) ? ['aria-describedby'] : [])->merge(['class' => 'block min-h-12 w-full rounded-xl border bg-white px-4 py-3 text-sm text-stone-900 shadow-sm transition placeholder:text-stone-600 focus:border-forest-500 focus:ring-4 focus:ring-forest-100 '.($errors->has($name) ? 'border-red-300' : 'border-stone-200')]) }}
    >

    @error($name)
        <p id="{{ $fieldId }}-error" role="alert" class='text-sm font-medium text-red-700'>{{ $message }}</p>
    @enderror
</div>
