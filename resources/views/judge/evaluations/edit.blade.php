<x-layouts.dashboard area="Juri" :user-name="auth()->user()->name">
    <div class="mx-auto max-w-4xl space-y-5">
        <a href="{{ route('judge.classes.show', $competitionClass) }}" class="inline-flex min-h-11 items-center text-sm font-bold text-forest-700">← {{ $competitionClass->name }}</a>
        <x-ui.status />
        <x-live-class :competition-class="$competitionClass" :endpoint="route('judge.classes.live', [$competitionClass, 'registrations' => [$registration->id]])" :channel="'class.'.$competitionClass->id.'.judge.'.auth()->id()" :private="true" />
        @if ($errors->any() && old('violation_form'))
            <div role="alert" class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800"><p class="font-extrabold">Pelanggaran belum tersimpan. Periksa formulir dan riwayat terbaru.</p><ul class="mt-2 list-inside list-disc space-y-1">@foreach ($errors->all() as $message)<li>{{ $message }}</li>@endforeach</ul></div>
        @endif
        @error('violation')<p role="alert" class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm font-bold text-red-800">{{ $message }}</p>@enderror
        @error('judging')<p role="alert" class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm font-bold text-red-800">{{ $message }}</p>@enderror
        @error('scores')<p role="alert" class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm font-bold text-red-800">{{ $message }}</p>@enderror
        <section class="flex flex-wrap items-center gap-4 rounded-2xl bg-forest-900 p-5 text-white sm:p-6">
            @if ($registration->bird->photo_path)<img src="{{ asset('storage/'.$registration->bird->photo_path) }}" alt="Foto {{ $registration->bird->name }}" class="size-20 rounded-xl object-cover">@endif
            <div class="min-w-0 flex-1"><p class="text-xs font-bold text-gold-300">Gantangan {{ $registration->gantangan_number ?? 'belum ditentukan' }}</p><h1 class="mt-2 break-words text-2xl font-extrabold">{{ $registration->bird->name }}</h1><p class="mt-1 break-words text-sm text-forest-100">{{ $registration->participant->name }} · {{ $registration->bird->bird_type }}</p></div>
            <x-ui.evaluation-status :status="$evaluation?->status" />
        </section>

        @if ($registration->status === \App\Enums\RegistrationStatus::Disqualified)
            <p role="status" class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm leading-6 text-red-800"><strong>Diskualifikasi.</strong> Peserta tidak dapat dinilai lagi dan dikeluarkan dari peserta yang memenuhi syarat peringkat. Seluruh riwayat nilai tetap tersimpan.</p>
        @elseif ($registration->warning_count > 0)
            <p role="status" class="rounded-xl border border-gold-200 bg-gold-50 p-4 text-sm leading-6 text-amber-900"><strong>Peringatan sudah tercatat.</strong> Pelanggaran peringatan berikutnya mengakibatkan diskualifikasi, termasuk jika dicatat oleh juri lain.</p>
        @endif

        @if ($evaluation?->status === \App\Enums\EvaluationStatus::Submitted)
            <p role="status" class="rounded-xl border border-forest-200 bg-forest-50 p-4 text-sm leading-6 text-forest-900">✓ Nilai Anda dikirim {{ $evaluation->submitted_at?->translatedFormat('d F Y, H.i') }} WIB. Nilai terkunci dan tidak dapat diubah.</p>
        @elseif (! $canWrite)
            <p role="status" class="rounded-xl border border-gold-200 bg-gold-50 p-4 text-sm leading-6 text-amber-900">{{ $registration->status === \App\Enums\RegistrationStatus::Disqualified ? 'Peserta didiskualifikasi. Riwayat nilai Anda tetap tersimpan dan hanya dapat dibaca.' : 'Form nilai belum dibuka atau penjurian kelas sudah ditutup. Hubungi panitia bila diperlukan.' }}</p>
        @endif

        <form data-live-evaluation-form="{{ $registration->id }}" data-live-registration-form="{{ $registration->id }}" method="POST" action="{{ route('judge.evaluations.draft', [$competitionClass, $registration]) }}" data-submit-feedback class="space-y-5">
            @csrf
            @foreach ($competitionClass->criteria as $criterion)
                <fieldset class="rounded-2xl border border-stone-200 bg-white px-4 pb-5 shadow-sm sm:px-6">
                    <legend class="max-w-full px-1 pt-5 text-base font-extrabold">{{ $criterion->code }} · {{ $criterion->name }}</legend>
                    <p class="mt-1 text-xs leading-6 text-stone-600">Bobot {{ number_format((float) $criterion->weight * 100) }}% · Pilih nilai {{ $criterion->min_score }}–{{ $criterion->max_score }}</p>
                    @if ($canWrite)
                        <div class="mt-4 grid grid-cols-5 gap-1.5 sm:grid-cols-10">
                            @for ($value = $criterion->min_score; $value <= $criterion->max_score; $value++)
                                <label class="relative flex min-h-11 cursor-pointer items-center justify-center rounded-xl border border-stone-300 text-base font-extrabold text-stone-700 transition has-[:checked]:border-forest-600 has-[:checked]:bg-forest-600 has-[:checked]:text-white has-[:focus-visible]:outline-2 has-[:focus-visible]:outline-offset-2 has-[:focus-visible]:outline-forest-600">
                                    <input type="radio" name="scores[{{ $criterion->id }}]" value="{{ $value }}" @checked((string) old('scores.'.$criterion->id, $scores[$criterion->id] ?? '') === (string) $value) class="sr-only">
                                    {{ $value }}
                                </label>
                            @endfor
                        </div>
                        @error('scores.'.$criterion->id)<p role="alert" class="mt-3 text-sm font-bold text-red-700">{{ $message }}</p>@enderror
                    @else
                        <p class="mt-3 text-2xl font-extrabold text-forest-800">{{ $scores[$criterion->id] ?? 'Belum dinilai' }}</p>
                    @endif
                </fieldset>
            @endforeach
            @if ($canWrite)
                <div class="sticky bottom-0 z-10 rounded-2xl border border-stone-200 bg-white p-4 shadow-lg">
                    <p class="mb-3 text-xs leading-6 text-stone-600">Draft boleh belum lengkap. Submit memerlukan seluruh nilai dan tidak dapat dibatalkan atau diubah.</p>
                    <div class="grid grid-cols-2 gap-3">
                        <x-ui.button formaction="{{ route('judge.evaluations.draft', [$competitionClass, $registration]) }}" variant="secondary" data-pending-text="Menyimpan…">Simpan draft</x-ui.button>
                        <x-ui.button formaction="{{ route('judge.evaluations.submit', [$competitionClass, $registration]) }}" data-pending-text="Mengirim…">Submit nilai</x-ui.button>
                    </div>
                </div>
            @endif
        </form>
        @if ($canViolate)
            @include('judge.evaluations.partials.violation-actions')
        @endif
        <x-violation-history :violations="$violations" />
        <x-ui.link-button :href="route('judge.classes.show', $competitionClass)" variant="secondary" class="w-full">Kembali ke peserta kelas</x-ui.link-button>
    </div>
</x-layouts.dashboard>
