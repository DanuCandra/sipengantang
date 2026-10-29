<x-layouts.public :title="$event->name">
    <section class="relative overflow-hidden bg-forest-900 text-white">
        <div class="relative mx-auto grid max-w-7xl items-center gap-8 px-5 py-10 sm:px-8 sm:py-16 lg:grid-cols-[minmax(0,1fr)_minmax(0,.9fr)]">
            <div class="min-w-0">
            <span class="rounded-full bg-white/10 px-3 py-1.5 text-xs font-extrabold text-gold-300">{{ $event->status->label() }}</span>
            <h1 class="mt-5 max-w-4xl break-words text-4xl font-extrabold tracking-tight sm:text-5xl">{{ $event->name }}</h1>
            <p class="mt-5 max-w-2xl text-base leading-8 text-forest-100">{{ $event->description ?: 'Kompetisi burung berkicau yang tertib, kompetitif, dan profesional.' }}</p>
            <div class="mt-7 flex flex-wrap gap-3 text-sm font-bold">
                <span class="rounded-xl bg-white/10 px-4 py-3">{{ $event->start_at->translatedFormat('d F Y · H.i') }} WIB</span>
                <span class="rounded-xl bg-white/10 px-4 py-3">{{ $event->location_name }}</span>
                <span class="rounded-xl bg-white/10 px-4 py-3">{{ $event->competitionClasses->count() }} kelas</span>
            </div>
            </div>
            <x-event-artwork :event="$event" class="aspect-[4/3] rounded-2xl border border-white/15 shadow-soft" />
        </div>
    </section>

    <section class="mx-auto grid max-w-7xl gap-8 px-5 py-10 sm:px-8 lg:grid-cols-[minmax(0,1fr)_20rem] lg:py-14">
        <div>
            <div class="flex items-end justify-between gap-4">
                <div>
                    <p class="text-xs font-extrabold uppercase tracking-wider text-forest-600">Kelas kompetisi</p>
                    <h2 class="mt-2 text-3xl font-extrabold">Pilih kelas Anda</h2>
                </div>
            </div>

            <div class="mt-6 grid gap-5 md:grid-cols-2">
                @forelse ($event->competitionClasses as $class)
                    @php($full = $class->quota !== null && $class->occupied_slots >= $class->quota)
                    <article class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h3 class="text-lg font-extrabold">{{ $class->name }}</h3>
                                <p class="mt-1 text-sm text-stone-500">{{ $class->bird_type }}</p>
                            </div>
                            <span class="rounded-full px-3 py-1 text-xs font-extrabold {{ $full ? 'bg-red-50 text-red-700' : 'bg-forest-50 text-forest-700' }}">
                                {{ $full ? 'Penuh' : 'Tersedia' }}
                            </span>
                        </div>
                        <div class="mt-5 grid grid-cols-2 gap-3">
                            <div class="rounded-xl bg-stone-50 p-3">
                                <p class="text-xs text-stone-500">Biaya</p>
                                <p class="mt-1 text-sm font-extrabold">Rp{{ number_format($class->registration_fee, 0, ',', '.') }}</p>
                            </div>
                            <div class="rounded-xl bg-stone-50 p-3">
                                <p class="text-xs text-stone-500">Kuota</p>
                                <p class="mt-1 text-sm font-extrabold">{{ $class->occupied_slots }} / {{ $class->quota ?? '∞' }}</p>
                            </div>
                        </div>

                        @if ($class->prizes->isNotEmpty())
                            <div class="mt-4 border-t border-stone-100 pt-4">
                                <p class="text-xs font-bold text-stone-600">Hadiah utama</p>
                                <p class="mt-1 text-sm font-extrabold text-gold-600">{{ $class->prizes->first()->title }}</p>
                            </div>
                        @endif

                        @if ($class->status === \App\Enums\CompetitionClassStatus::Finalized)
                            <x-ui.link-button :href="route('events.classes.results', [$event, $class])" class="mt-5 w-full">Lihat hasil resmi</x-ui.link-button>
                        @endif
                        @if (in_array($event->status, [\App\Enums\EventStatus::Ongoing, \App\Enums\EventStatus::Completed], true))
                            <div class="mt-5"><x-live-class :competition-class="$class" :endpoint="route('events.classes.live', [$event, $class])" :channel="'class.'.$class->id.'.public'" /></div>
                        @endif
                        @if ($event->status === \App\Enums\EventStatus::Upcoming && ! $full)
                            <a href="{{ route('participant.registrations.create', [$event, 'class' => $class->id]) }}" class="mt-5 inline-flex min-h-11 w-full items-center justify-center rounded-xl bg-forest-600 text-sm font-bold text-white hover:bg-forest-700">
                                Daftar kelas
                            </a>
                        @endif
                    </article>
                @empty
                    <div class="col-span-full rounded-2xl border border-dashed border-stone-300 bg-white p-8 text-center text-sm text-stone-500">Kelas belum diumumkan.</div>
                @endforelse
            </div>
        </div>

        <aside class="space-y-5">
            <div class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-extrabold uppercase tracking-wider text-forest-600">Lokasi</p>
                <h2 class="mt-2 font-extrabold">{{ $event->location_name }}</h2>
                <p class="mt-2 text-sm leading-6 text-stone-500">{{ $event->address }}</p>
            </div>

            @if ($event->contact_person || $event->contact_phone)
                <div class="rounded-2xl bg-gold-50 p-5">
                    <p class="text-xs font-extrabold uppercase tracking-wider text-gold-600">Informasi panitia</p>
                    <p class="mt-2 font-extrabold">{{ $event->contact_person ?: 'Panitia GantangScore' }}</p>
                    <p class="mt-1 text-sm text-stone-600">{{ $event->contact_phone }}</p>
                </div>
            @endif
        </aside>
    </section>
</x-layouts.public>
