<x-layouts.dashboard area="Admin" :user-name="auth()->user()->name">
    <div class="mx-auto max-w-6xl space-y-6">
        <a href="{{ route('admin.events.classes.show', [$event, $competitionClass]) }}" class="inline-flex min-h-11 items-center text-sm font-bold text-forest-700">← {{ $competitionClass->name }}</a>
        <div><p class="text-xs font-extrabold uppercase tracking-wider text-forest-600">{{ $event->name }}</p><h1 class="mt-2 text-3xl font-extrabold">Juri & progres penilaian</h1><p class="mt-2 text-sm text-stone-600">{{ $competitionClass->name }} · {{ $competitionClass->status->label() }}</p></div>
        <x-ui.status />
        <x-live-class :competition-class="$competitionClass" :endpoint="route('admin.events.classes.live', [$event, $competitionClass])" :channel="'event.'.$event->id.'.admin'" :private="true" />
        @if ($errors->any())<div role="alert" class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800"><ul class="list-inside list-disc">@foreach ($errors->all() as $message)<li>{{ $message }}</li>@endforeach</ul></div>@endif
        <x-ui.link-button :href="route('admin.events.classes.ranking', [$event, $competitionClass])" variant="secondary">Ranking &amp; finalisasi hasil</x-ui.link-button>
        @error('judging')<p role="alert" class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm font-bold text-red-800">{{ $message }}</p>@enderror
        <section data-live-summary-class="{{ $competitionClass->id }}" class="rounded-2xl bg-forest-900 p-5 text-white sm:p-7">
            <div class="grid gap-4 sm:grid-cols-3">
                <div><p class="text-sm text-forest-100">Peserta terverifikasi</p><p data-summary-eligible class="mt-1 text-3xl font-extrabold">{{ $progress['eligible'] }}</p></div>
                <div><p class="text-sm text-forest-100">Juri ditugaskan</p><p data-summary-assigned class="mt-1 text-3xl font-extrabold">{{ $progress['assigned'] }}</p></div>
                <div><p class="text-sm text-forest-100">Evaluasi sudah dikirim</p><p data-summary-submitted class="mt-1 text-3xl font-extrabold">{{ $progress['submitted'] }} / {{ $progress['expected'] }}</p></div>
            </div>
            <div data-summary-progress role="progressbar" aria-label="Progres evaluasi kelas" aria-valuenow="{{ $progress['percent'] }}" aria-valuemin="0" aria-valuemax="100" class="mt-5 h-2 overflow-hidden rounded-full bg-white/20"><div data-summary-bar class="h-full rounded-full bg-gold-400" style="width: {{ $progress['percent'] }}%"></div></div>
            <p class="mt-3 text-xs leading-6 text-forest-100"><span data-summary-percent>{{ $progress['percent'] }}%</span> berdasarkan peserta terverifikasi × penugasan saat ini. Draft tidak dihitung selesai. Ini bukan keputusan finalisasi hasil.</p>
        </section>
        @if ($competitionClass->status === \App\Enums\CompetitionClassStatus::Upcoming)
            <section class="rounded-2xl border border-gold-200 bg-gold-50 p-5"><h2 class="font-extrabold">Buka penjurian kelas</h2><p class="mt-2 text-sm leading-6 text-stone-700">Pastikan penugasan Juri dan persiapan lomba sudah sesuai. Memulai kelas juga mengubah event menjadi berlangsung.</p><form method="POST" action="{{ route('admin.events.classes.judging.start', [$event, $competitionClass]) }}" class="mt-4" data-submit-feedback>@csrf<x-ui.button data-pending-text="Membuka penjurian…">Mulai penjurian</x-ui.button></form></section>
        @endif
        <div class="grid items-start gap-6 xl:grid-cols-[minmax(0,1.2fr)_minmax(19rem,.8fr)]">
            <section class="min-w-0 rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-3"><h2 class="text-xl font-extrabold">Penugasan Juri</h2><x-ui.link-button :href="route('admin.events.classes.judges.index', [$event, $competitionClass])" variant="secondary">Muat ulang progres</x-ui.link-button></div>
                <div class="mt-5 space-y-4">
                    @forelse ($assignments as $assignment)
                        <article class="rounded-xl border border-stone-200 p-4">
                            <div class="flex flex-wrap items-center justify-between gap-2"><h3 class="font-extrabold">{{ $assignment->judge->name }}</h3><span class="rounded-full bg-stone-100 px-3 py-1 text-xs font-bold">{{ $assignment->replaced_at ? 'Penugasan diganti' : $assignment->judge->status->label() }}</span></div><p class="mt-1 break-all text-xs text-stone-500">{{ $assignment->judge->email }}</p>
                            @if ($assignment->replaced_at)
                                <p class="mt-3 text-sm leading-6 text-stone-600">Digantikan oleh {{ $assignment->replacement->judge->name }} pada {{ $assignment->replaced_at->translatedFormat('d F Y, H.i') }} WIB.</p><p class="mt-2 break-words text-xs leading-6 text-stone-500">{{ $assignment->replacement_note }}</p>
                            @else
                                <p data-live-judge-submitted="{{ $assignment->judge_id }}" class="mt-3 text-sm font-bold text-forest-800">{{ $progress['by_judge'][$assignment->judge_id]['SUBMITTED'] ?? 0 }} / {{ $progress['eligible'] }} sudah dikirim</p><p data-live-judge-drafts="{{ $assignment->judge_id }}" class="mt-1 text-xs text-stone-500">{{ $progress['by_judge'][$assignment->judge_id]['DRAFT'] ?? 0 }} draft tersimpan</p>
                                @if (in_array($competitionClass->status, [\App\Enums\CompetitionClassStatus::Upcoming, \App\Enums\CompetitionClassStatus::Ongoing], true) && ! in_array($assignment->judge_id, $submittedJudgeIds, true))
                                    <details class="mt-4 rounded-xl border border-gold-200 p-3"><summary class="min-h-11 cursor-pointer py-2 text-sm font-bold text-amber-900">Ganti Juri absen</summary><p class="mt-2 text-xs leading-6 text-stone-500">Hanya untuk Juri yang belum memiliki nilai submitted di kelas ini. Draft lama tetap tersimpan tetapi tidak diwariskan. Pengganti harus menilai sendiri seluruh peserta.</p><form method="POST" action="{{ route('admin.events.classes.judges.replace', [$event, $competitionClass, $assignment]) }}" data-submit-feedback class="mt-4 space-y-4">@csrf<div><label for="replacement-email-{{ $assignment->id }}" class="mb-2 block text-sm font-bold">Email akun pengganti</label><input id="replacement-email-{{ $assignment->id }}" type="email" name="replacement_email" required class="min-h-11 w-full rounded-xl border border-stone-300 p-3 text-sm"></div><div><label for="replacement-note-{{ $assignment->id }}" class="mb-2 block text-sm font-bold">Alasan penggantian</label><textarea id="replacement-note-{{ $assignment->id }}" name="replacement_note" required maxlength="2000" rows="3" class="w-full rounded-xl border border-stone-300 p-3 text-sm"></textarea></div><x-ui.button variant="secondary" data-pending-text="Mengganti…">Konfirmasi penggantian</x-ui.button></form></details>
                                @endif
                            @endif
                        </article>
                    @empty
                        <p class="rounded-xl border border-dashed border-stone-300 p-7 text-center text-sm leading-6 text-stone-500">Belum ada Juri ditugaskan. Pilih akun yang sudah ada atau buat akun Juri baru.</p>
                    @endforelse
                </div>
                <div class="mt-5">{{ $assignments->links() }}</div>
            </section>
            @if (in_array($competitionClass->status, [\App\Enums\CompetitionClassStatus::Upcoming, \App\Enums\CompetitionClassStatus::Ongoing], true))
                <section class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm"><h2 class="text-xl font-extrabold">Tugaskan akun sebagai Juri</h2><p class="mt-2 text-sm leading-6 text-stone-600">Masukkan email akun aktif. Akun mendapat peran Juri dan akses ke kelas ini; peran sebelumnya tetap dipertahankan.</p><form method="POST" action="{{ route('admin.events.classes.judges.store', [$event, $competitionClass]) }}" class="mt-5 space-y-4" data-submit-feedback>@csrf<x-form.field name="email" label="Email akun" type="email" required /><x-ui.button class="w-full" data-pending-text="Menugaskan…">Tugaskan sebagai Juri</x-ui.button></form><div class="mt-5 border-t border-stone-100 pt-5"><p class="mb-3 text-sm text-stone-600">Belum memiliki akun?</p><x-ui.link-button :href="route('admin.events.classes.judges.create', [$event, $competitionClass])" variant="secondary" class="w-full">Buat akun Juri baru</x-ui.link-button></div></section>
            @endif
        </div>
    </div>
</x-layouts.dashboard>
