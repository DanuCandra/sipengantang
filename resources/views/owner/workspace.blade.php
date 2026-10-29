<x-layouts.dashboard area="Owner" :user-name="auth()->user()->name">
    <div class="mx-auto max-w-7xl space-y-6">
        <header><p class="text-xs font-extrabold uppercase tracking-wider text-forest-600">Event milik Anda</p><h1 class="mt-2 text-3xl font-extrabold">{{ $section === 'admins' ? 'Admin Event' : 'Pantau Penjurian' }}</h1><p class="mt-3 text-sm text-stone-600">Pilih event untuk {{ $section === 'admins' ? 'menetapkan atau mengelola Admin penanggung jawab.' : 'melihat progres penilaian dan pelanggaran.' }}</p></header>
        <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
            @forelse ($events as $event)
                <article class="min-w-0 rounded-2xl border border-stone-200 bg-white p-5 shadow-sm"><x-event-artwork :event="$event" class="mb-5 aspect-[16/9] rounded-xl" /><span class="rounded-full bg-forest-50 px-3 py-1 text-xs font-bold text-forest-800">{{ $event->status->label() }}</span><h2 class="mt-4 break-words text-xl font-extrabold">{{ $event->name }}</h2><p class="mt-3 text-sm text-stone-600">{{ $event->admins_count }} Admin · {{ $event->competition_classes_count }} kelas</p><x-ui.link-button :href="route('owner.events.show', $event).($section === 'admins' ? '#tim-admin' : '#pantau-penjurian')" class="mt-5">{{ $section === 'admins' ? 'Kelola Admin event' : 'Pantau event' }}</x-ui.link-button></article>
            @empty
                <div class="col-span-full rounded-2xl border border-dashed border-stone-300 bg-white p-8 text-center"><h2 class="font-extrabold">Belum ada event</h2><p class="mt-3 text-sm text-stone-600">Buat event sebelum menugaskan Admin dan memantau lomba.</p><x-ui.link-button :href="route('owner.events.create')" class="mt-5">Buat event</x-ui.link-button></div>
            @endforelse
        </div>
        {{ $events->links() }}
    </div>
</x-layouts.dashboard>
