<x-layouts.dashboard area="Juri" :user-name="auth()->user()->name">
    <div class="mx-auto max-w-6xl space-y-6">
        <a href="{{ route('judge.classes.index') }}" class="inline-flex min-h-11 items-center text-sm font-bold text-forest-700">← Penugasan saya</a>
        <div class="rounded-2xl bg-forest-900 p-5 text-white sm:p-7"><p class="text-xs font-bold text-gold-300">{{ $competitionClass->event->name }}</p><h1 class="mt-3 text-2xl font-extrabold sm:text-3xl">{{ $competitionClass->name }}</h1><p class="mt-3 text-sm text-forest-100">{{ $competitionClass->status->label() }} · {{ $submitted }} / {{ $eligible }} nilai Anda sudah dikirim</p></div>
        <x-ui.status />
        <x-live-class :competition-class="$competitionClass" :endpoint="route('judge.classes.live', [$competitionClass, 'registrations' => $registrations->modelKeys()])" :channel="'class.'.$competitionClass->id.'.judge.'.auth()->id()" :private="true" />
        <form method="GET" class="flex flex-col gap-3 rounded-2xl border border-stone-200 bg-white p-4 sm:flex-row sm:items-end">
            <div class="min-w-0 flex-1"><x-form.field name="search" label="Cari nomor gantangan atau nama burung" :value="request('search')" placeholder="Contoh: 17 atau Panglima" /></div>
            <x-ui.button type="submit">Cari peserta</x-ui.button>
            @if (request()->filled('search'))<x-ui.link-button :href="route('judge.classes.show', $competitionClass)" variant="secondary">Reset</x-ui.link-button>@endif
        </form>
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @forelse ($registrations as $registration)
                @php($evaluation = $registration->judgeEvaluations->first())
                <article data-live-registration="{{ $registration->id }}" class="min-w-0 rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
                    <p data-live-registration-status class="mb-3 text-xs font-bold text-stone-600">{{ $registration->status->label() }}</p>
                    <div class="flex flex-wrap items-center justify-between gap-3"><p class="font-extrabold text-forest-800">Gantangan {{ $registration->gantangan_number ?? 'belum ditentukan' }}</p>
                        @if ($registration->status === \App\Enums\RegistrationStatus::Disqualified)<span class="rounded-full bg-red-50 px-3 py-1 text-xs font-extrabold text-red-800">Diskualifikasi</span>@else<x-ui.evaluation-status :status="$evaluation?->status" />@endif
                    </div>
                    <div class="mt-5 flex items-center gap-3">
                        @if ($registration->bird->photo_path)<img src="{{ asset('storage/'.$registration->bird->photo_path) }}" alt="Foto {{ $registration->bird->name }}" class="size-16 rounded-xl object-cover">@endif
                        <div class="min-w-0"><h2 class="break-words text-lg font-extrabold">{{ $registration->bird->name }}</h2><p class="mt-1 break-words text-sm text-stone-500">{{ $registration->participant->name }}</p></div>
                    </div>
                    <p class="mt-4 break-all text-xs text-stone-500">{{ $registration->registration_number }}</p>
                    @if ($registration->warning_count > 0)<p class="mt-3 rounded-lg bg-gold-50 p-3 text-xs font-bold text-amber-900">{{ $registration->warning_count }} peringatan tercatat</p>@endif
                    <x-ui.link-button :href="route('judge.evaluations.edit', [$competitionClass, $registration])" variant="secondary" class="mt-5 w-full">{{ $evaluation?->status === \App\Enums\EvaluationStatus::Submitted || $registration->status === \App\Enums\RegistrationStatus::Disqualified ? 'Lihat penilaian saya' : 'Buka lembar nilai' }}</x-ui.link-button>
                </article>
            @empty
                <div class="col-span-full rounded-2xl border border-dashed border-stone-300 bg-white px-5 py-12 text-center"><h2 class="font-extrabold">Peserta tidak ditemukan</h2><p class="mt-2 text-sm leading-6 text-stone-500">Peserta akan tersedia setelah pembayaran diverifikasi panitia. Coba sesuaikan pencarian Anda.</p></div>
            @endforelse
        </div>
        {{ $registrations->links() }}
    </div>
</x-layouts.dashboard>
