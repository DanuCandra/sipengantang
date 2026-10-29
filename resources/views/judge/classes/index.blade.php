<x-layouts.dashboard area="Juri" :user-name="auth()->user()->name">
    <div class="mx-auto max-w-6xl space-y-6">
        <div><p class="text-xs font-extrabold uppercase tracking-wider text-forest-600">Ruang Juri</p><h1 class="mt-2 text-3xl font-extrabold">{{ match ($mode ?? 'assignments') { 'scoring' => 'Penilaian Aktif', 'history' => 'Riwayat Nilai Saya', default => 'Selamat bertugas' } }}</h1><p class="mt-3 text-sm leading-6 text-stone-600">{{ ($mode ?? '') === 'history' ? 'Kelas yang memiliki penilaian Anda yang sudah dikirim. Buka kelas untuk melihat nilai milik Anda.' : 'Pilih kelas penugasan Anda untuk mulai atau melanjutkan penilaian.' }}</p></div>
        <x-ui.status />
        <div class="grid gap-5 md:grid-cols-2">
            @forelse ($classes as $competitionClass)
                <article class="flex min-w-0 flex-col rounded-2xl border border-stone-200 bg-white p-5 shadow-sm sm:p-6">
                    <div class="flex flex-wrap items-center justify-between gap-3"><p class="text-xs font-bold text-forest-700">{{ $competitionClass->event->name }}</p><span class="rounded-full bg-stone-100 px-3 py-1 text-xs font-bold">{{ $competitionClass->status->label() }}</span></div>
                    <h2 class="mt-4 text-xl font-extrabold">{{ $competitionClass->name }}</h2>
                    <p class="mt-2 text-sm text-stone-500">{{ $competitionClass->start_at?->translatedFormat('d F Y, H.i') }} WIB · {{ $competitionClass->event->location_name }}</p>
                    <div class="mt-5 rounded-xl bg-forest-50 p-4"><p class="text-sm font-extrabold text-forest-900">{{ $competitionClass->submitted_count }} / {{ $competitionClass->eligible_count }} nilai Anda sudah dikirim</p><p class="mt-1 text-xs leading-6 text-stone-600">Hanya peserta terverifikasi yang masuk penilaian.</p></div>
                    <x-ui.link-button :href="route('judge.classes.show', $competitionClass)" class="mt-5 w-full">Buka kelas penilaian</x-ui.link-button>
                </article>
            @empty
                <div class="col-span-full rounded-2xl border border-dashed border-stone-300 bg-white px-5 py-14 text-center"><h2 class="text-lg font-extrabold">Belum ada penugasan kelas</h2><p class="mt-2 text-sm leading-6 text-stone-500">Panitia akan menugaskan akun Juri Anda ke kelas kompetisi.</p></div>
            @endforelse
        </div>
        {{ $classes->links() }}
    </div>
</x-layouts.dashboard>
