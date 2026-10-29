<x-layouts.dashboard area="Admin" :user-name="auth()->user()->name">
    @php($isFinal = $competitionClass->status === \App\Enums\CompetitionClassStatus::Finalized)
    <div class="mx-auto max-w-7xl space-y-6">
        <a href="{{ route('admin.events.show', $event) }}" class="inline-flex min-h-11 items-center text-sm font-bold text-forest-700">← {{ $event->name }}</a>
        <x-ui.status />
        <header class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
            <div><p class="text-xs font-extrabold uppercase tracking-wider text-forest-600">{{ $competitionClass->status->label() }}</p><h1 class="mt-3 text-3xl font-extrabold tracking-tight">{{ $competitionClass->name }}</h1><p class="mt-2 text-sm text-stone-500">{{ $competitionClass->bird_type }} · Rp{{ number_format($competitionClass->registration_fee, 0, ',', '.') }} · Kuota {{ $competitionClass->quota ?? 'tidak dibatasi' }}</p></div>
            @if (! $isFinal)<x-ui.link-button :href="route('admin.events.classes.edit', [$event, $competitionClass])" variant="secondary">Edit kelas</x-ui.link-button>@endif
        </header>
        <div class="flex flex-wrap gap-3"><x-ui.link-button :href="route('admin.events.classes.ranking', [$event, $competitionClass])">Ranking &amp; finalisasi</x-ui.link-button><x-ui.link-button :href="route('admin.events.classes.judges.index', [$event, $competitionClass])" variant="secondary">Kelola Juri &amp; penjurian</x-ui.link-button></div>
        @if ($isFinal)<p role="status" class="rounded-xl border border-forest-200 bg-forest-50 p-4 text-sm leading-6 text-forest-900">Kelas sudah final. Informasi kelas dan hadiah resmi terkunci. Hasil publik memakai snapshot yang disimpan saat finalisasi.</p>@endif
        <div class="grid gap-6 xl:grid-cols-[minmax(0,1.1fr)_minmax(20rem,.9fr)]">
            <section class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm sm:p-6"><p class="text-xs font-extrabold uppercase tracking-wider text-forest-600">Snapshot penilaian</p><h2 class="mt-1 text-xl font-extrabold">Kriteria TOPSIS kelas</h2><div class="mt-5 space-y-3">
                @foreach ($competitionClass->criteria as $criterion)
                    <div class="grid grid-cols-[2.5rem_minmax(0,1fr)_4rem] items-center gap-3 rounded-xl bg-stone-50 p-3"><span class="grid size-9 place-items-center rounded-lg bg-forest-100 text-xs font-extrabold text-forest-700">{{ $criterion->code }}</span><div><p class="text-sm font-extrabold">{{ $criterion->name }}</p><p class="text-xs text-stone-500">Benefit · skor {{ $criterion->min_score }}–{{ $criterion->max_score }}</p></div><span class="text-right text-sm font-extrabold text-gold-600">{{ number_format((float) $criterion->weight * 100) }}%</span></div>
                @endforeach
            </div></section>
            <section id="hadiah" class="scroll-mt-24 rounded-2xl border border-stone-200 bg-white p-5 shadow-sm sm:p-6"><p class="text-xs font-extrabold uppercase tracking-wider text-forest-600">Penghargaan</p><h2 class="mt-1 text-xl font-extrabold">Hadiah peringkat</h2><div class="mt-5 space-y-3">
                @forelse ($competitionClass->prizes as $prize)
                    <div class="flex items-center gap-3 rounded-xl bg-stone-50 p-3"><span class="grid size-9 shrink-0 place-items-center rounded-lg bg-gold-100 text-sm font-extrabold text-gold-600">{{ $prize->rank }}</span><div class="min-w-0 flex-1"><p class="break-words text-sm font-extrabold">{{ $prize->title }}</p><p class="text-xs text-stone-500">{{ $prize->type->label() }}@if ($prize->cash_amount) · Rp{{ number_format($prize->cash_amount, 0, ',', '.') }}@endif</p></div>
                        @if (! $isFinal)<form method="POST" action="{{ route('admin.events.classes.prizes.destroy', [$event, $competitionClass, $prize]) }}">@csrf @method('DELETE')<button aria-label="Hapus {{ $prize->title }}" class="min-h-11 rounded-lg px-2 text-xs font-bold text-red-700 hover:bg-red-50">Hapus</button></form>@endif
                    </div>
                @empty
                    <p class="rounded-xl border border-dashed border-stone-300 p-5 text-center text-sm text-stone-500">Belum ada hadiah.</p>
                @endforelse
            </div>
                @if (! $isFinal)
                    <form method="POST" action="{{ route('admin.events.classes.prizes.store', [$event, $competitionClass]) }}" data-submit-feedback class="mt-6 space-y-4 border-t border-stone-100 pt-5">@csrf<h3 class="font-extrabold">Tambah hadiah</h3>
                        <div class="grid grid-cols-2 gap-3"><x-form.field name="rank" label="Peringkat" type="number" min="1" required /><div class="space-y-2"><label for="type" class="block text-sm font-bold text-stone-700">Jenis</label><select id="type" name="type" required class="min-h-12 w-full rounded-xl border border-stone-200 bg-white px-4 text-sm shadow-sm focus:border-forest-500 focus:ring-forest-100">@foreach (\App\Enums\PrizeType::cases() as $type)<option value="{{ $type->value }}" @selected(old('type') === $type->value)>{{ $type->label() }}</option>@endforeach</select></div></div>
                        <x-form.field name="title" label="Nama hadiah" required /><x-form.field name="cash_amount" label="Nominal uang (Rupiah)" type="number" min="0" /><div class="space-y-2"><label for="prize-description" class="block text-sm font-bold text-stone-700">Keterangan</label><textarea id="prize-description" name="description" rows="2" class="w-full rounded-xl border border-stone-200 px-4 py-3 text-sm shadow-sm">{{ old('description') }}</textarea></div><x-ui.button class="w-full">Tambahkan hadiah</x-ui.button>
                    </form>
                @endif
            </section>
        </div>
    </div>
</x-layouts.dashboard>
