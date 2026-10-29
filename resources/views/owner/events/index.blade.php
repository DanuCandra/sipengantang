<x-layouts.dashboard area='Owner' :user-name='auth()->user()->name'>
    <div class='mx-auto max-w-7xl'>
        <div class='flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between'>
            <div><p class='text-xs font-extrabold uppercase tracking-[0.16em] text-forest-600'>Event milik Anda</p><h1 class='mt-2 text-3xl font-extrabold tracking-tight text-stone-900'>Kelola agenda GantangScore</h1><p class='mt-2 text-sm text-stone-500'>Buat event, tetapkan admin, dan pantau kesiapan kelas lomba.</p></div>
            <x-ui.link-button :href='route("owner.events.create")'>Buat event baru</x-ui.link-button>
        </div>
        <div class='mt-7'><x-ui.status /></div>

        @if ($events->isEmpty())
            <div class='mt-7 rounded-2xl border border-dashed border-stone-300 bg-white p-10 text-center'><h2 class='text-lg font-extrabold'>Belum ada event</h2><p class='mt-2 text-sm text-stone-500'>Mulai dari event draft, lalu lengkapi admin dan kelasnya.</p></div>
        @else
            <div class='mt-7 grid gap-5 lg:grid-cols-2'>
                @foreach ($events as $event)
                    <article class='rounded-2xl border border-stone-200 bg-white p-5 shadow-sm'>
                        <x-event-artwork :event="$event" class="mb-5 aspect-[16/9] rounded-xl" />
                        <div class='flex items-start justify-between gap-4'><div><span class='rounded-full bg-forest-50 px-3 py-1 text-xs font-extrabold text-forest-700'>{{ $event->status->label() }}</span><h2 class='mt-3 text-xl font-extrabold text-stone-900'>{{ $event->name }}</h2><p class='mt-1 text-sm text-stone-500'>{{ $event->location_name }} · {{ $event->start_at->translatedFormat('d F Y') }}</p></div><span class='grid size-11 shrink-0 place-items-center rounded-xl bg-gold-50 font-extrabold text-gold-600'>{{ $loop->iteration }}</span></div>
                        <div class='mt-5 grid grid-cols-2 gap-3'><div class='rounded-xl bg-stone-50 p-3'><p class='text-xs text-stone-500'>Admin</p><p class='mt-1 font-extrabold'>{{ $event->admins_count }}</p></div><div class='rounded-xl bg-stone-50 p-3'><p class='text-xs text-stone-500'>Kelas</p><p class='mt-1 font-extrabold'>{{ $event->competition_classes_count }}</p></div></div>
                        <a href='{{ route("owner.events.show", $event) }}' class='mt-5 inline-flex min-h-11 w-full items-center justify-center rounded-xl border border-stone-200 text-sm font-bold text-forest-700 transition hover:bg-forest-50'>Buka event</a>
                    </article>
                @endforeach
            </div>
            <div class='mt-6'>{{ $events->links() }}</div>
        @endif
    </div>
</x-layouts.dashboard>
