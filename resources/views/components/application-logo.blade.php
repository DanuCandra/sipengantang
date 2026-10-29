@props(['compact' => false])

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-3']) }}>
    <span class='grid size-11 shrink-0 place-items-center rounded-2xl bg-forest-600 text-white shadow-lg shadow-forest-900/10'>
        <svg aria-hidden='true' class='size-6' viewBox='0 0 24 24' fill='none'>
            <path d='M5 16.5c3.2-3.8 7.8-5.7 13.8-5.8-1 4.5-4.1 7.3-8.6 7.3H7' stroke='currentColor' stroke-width='1.8' stroke-linecap='round' stroke-linejoin='round'/>
            <path d='M8.2 13.6C7 11.3 7.5 8.8 9.9 6.2c2.9 1.3 4.1 3.2 3.7 5.6M4 20h16' stroke='currentColor' stroke-width='1.8' stroke-linecap='round' stroke-linejoin='round'/>
            <circle cx='16.8' cy='8' r='.9' fill='currentColor'/>
        </svg>
    </span>

    @unless ($compact)
        <span class='leading-none'>
            <span class='block text-base font-extrabold tracking-tight text-forest-900'>GantangScore</span>
            <span class='mt-1 block text-[10px] font-bold uppercase tracking-[0.18em] text-forest-600'>Event & kompetisi burung</span>
        </span>
    @endunless
</span>
