<x-layouts.dashboard area="Admin" :user-name="auth()->user()->name">
    <div class="mx-auto max-w-7xl space-y-6">
        <header><p class="text-xs font-extrabold uppercase tracking-wider text-forest-600">Ruang operasional</p><h1 class="mt-2 text-3xl font-extrabold">{{ $title }}</h1><p class="mt-3 break-words text-sm text-stone-600">{{ $event?->name ?? 'Pilih event yang ditugaskan kepada Anda untuk memulai.' }}</p></header>
        <x-ui.status />
        @if (! $event)
            <div class="rounded-2xl border border-dashed border-stone-300 bg-white p-8 text-center"><h2 class="font-extrabold">Belum ada event ditugaskan</h2><p class="mt-3 text-sm leading-6 text-stone-600">Owner perlu menugaskan akun Anda sebagai Admin event. Menu operasional akan mengikuti event yang dipilih pada sidebar.</p><x-ui.link-button :href="route('admin.events.index')" class="mt-5" variant="secondary">Lihat daftar event</x-ui.link-button></div>
        @else
            <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-forest-200 bg-forest-50 p-4"><p class="text-sm font-bold text-forest-900">Pilih kelas untuk {{ strtolower($title) }}.</p>@if ($section === 'classes')<x-ui.link-button :href="route('admin.events.classes.create', $event)">Tambah kelas</x-ui.link-button>@endif</div>
            <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                @forelse ($classes as $class)
                    @php
                        $target = match ($section) {
                            'judges' => route('admin.events.classes.judges.index', [$event, $class]),
                            'judging' => route('admin.events.classes.judges.index', [$event, $class, 'monitor' => 1]),
                            'results' => route('admin.events.classes.ranking', [$event, $class]),
                            'prizes' => route('admin.events.classes.show', [$event, $class, 'tab' => 'prizes']).'#hadiah',
                            default => route('admin.events.classes.show', [$event, $class]),
                        };
                    @endphp
                    <article class="flex min-w-0 flex-col rounded-2xl border border-stone-200 bg-white p-5 shadow-sm"><span class="w-fit rounded-full bg-forest-50 px-3 py-1 text-xs font-bold text-forest-800">{{ $class->status->label() }}</span><h2 class="mt-4 break-words text-xl font-extrabold">{{ $class->name }}</h2><p class="mt-2 text-sm text-stone-600">{{ $class->bird_type }}</p><p class="mt-3 text-xs text-stone-600">{{ $class->judge_assignments_count }} Juri aktif · {{ $class->prizes_count }} hadiah</p><x-ui.link-button :href="$target" class="mt-5 w-full">Buka {{ strtolower($title) }}</x-ui.link-button></article>
                @empty
                    <div class="col-span-full rounded-2xl border border-dashed border-stone-300 bg-white p-8 text-center"><h2 class="font-extrabold">Belum ada kelas</h2><p class="mt-3 text-sm text-stone-600">Tambahkan kelas sebelum mengatur Juri, penjurian, hadiah, dan hasil.</p><x-ui.link-button :href="route('admin.events.classes.create', $event)" class="mt-5">Tambah kelas</x-ui.link-button></div>
                @endforelse
            </div>
            {{ $classes->links() }}
        @endif
    </div>
</x-layouts.dashboard>
