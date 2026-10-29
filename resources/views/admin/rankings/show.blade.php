<x-layouts.dashboard area="Admin" :user-name="auth()->user()->name">
    <div class="mx-auto max-w-6xl space-y-6">
        <a href="{{ route('admin.events.classes.show', [$event, $competitionClass]) }}" class="inline-flex min-h-11 items-center text-sm font-bold text-forest-700">← Kembali ke kelas</a>
        <x-ui.status />
        <x-live-class :competition-class="$competitionClass" :endpoint="route('admin.events.classes.live', [$event, $competitionClass])" :channel="'event.'.$event->id.'.admin'" :private="true" :fragment-url="route('admin.events.classes.ranking', [$event, $competitionClass, 'fragment' => 1, 'page' => request('page', 1)])" />
        @if ($errors->any())<div role="alert" class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm leading-6 text-red-800"><ul class="list-inside list-disc">@foreach ($errors->all() as $message)<li>{{ $message }}</li>@endforeach</ul></div>@endif
        @php($isFinal = $run?->type === \App\Enums\RankingRunType::Final)
        <section class="rounded-2xl bg-forest-900 p-5 text-white sm:p-7">
            <p class="text-xs font-bold text-gold-300">{{ $event->name }}</p><h1 class="mt-3 text-2xl font-extrabold sm:text-3xl">Hasil TOPSIS · {{ $competitionClass->name }}</h1>
            <span class="mt-4 inline-flex rounded-full bg-white/15 px-3 py-1 text-xs font-extrabold">{{ $isFinal ? 'FINAL · Hasil resmi' : 'PROVISIONAL · Belum resmi' }}</span>
            <p class="mt-4 text-sm leading-6 text-forest-100">{{ $progress['submitted'] }} / {{ $progress['expected'] }} evaluasi submitted · {{ $progress['assigned'] }} Juri aktif. {{ $isFinal ? 'Hasil terkunci dan dibaca dari snapshot final.' : 'Ranking masih dapat berubah; tidak ditampilkan kepada publik.' }}</p>
            @if ($run)<p class="mt-2 text-xs text-forest-100">Dihitung {{ $run->calculated_at->translatedFormat('d F Y, H.i') }} WIB · {{ $run->algorithm }} v{{ $run->algorithm_version }}</p>@endif
        </section>
        @if (! $isFinal)
            <div class="flex flex-wrap items-center gap-3"><form method="POST" action="{{ route('admin.events.classes.ranking.recalculate', [$event, $competitionClass]) }}" data-submit-feedback>@csrf<x-ui.button data-pending-text="Menghitung…">Hitung ulang dari database</x-ui.button></form><x-ui.link-button :href="route('admin.events.classes.ranking', [$event, $competitionClass])" variant="secondary">Muat ulang halaman</x-ui.link-button></div>
            <p class="rounded-xl border border-gold-200 bg-gold-50 p-4 text-sm leading-6 text-amber-900">Hasil sementara ini memakai evaluasi submitted yang tersedia. Finalisasi membutuhkan minimal 3 Juri aktif, semua penilaian peserta eligible lengkap, dan seluruh draw selesai. Jika nilai sumber berubah, hitung ulang dan tinjau draw kembali.</p>
        @else
            <x-ui.link-button :href="route('events.classes.results', [$event, $competitionClass])" variant="secondary">Buka hasil resmi publik</x-ui.link-button>
            <section class="rounded-2xl border border-stone-200 bg-white p-5"><h2 class="font-extrabold">Piagam pemenang</h2><p class="mt-2 text-sm leading-6 text-stone-600">Diproses oleh queue dari peringkat hadiah pada snapshot FINAL. Jika worker sempat berhenti atau file hilang, antrekan ulang secara aman tanpa menimpa piagam yang sudah tersedia.</p><form method="POST" action="{{ route('admin.events.classes.certificates.generate', [$event, $competitionClass]) }}" data-submit-feedback class="mt-4">@csrf<x-ui.button variant="secondary" data-pending-text="Mengantrekan…">Antrekan pembuatan / pemulihan piagam</x-ui.button></form></section>
        @endif
        <div data-live-ranking="{{ $competitionClass->id }}">@include('admin.rankings.partials.results')</div>
        @if (! $isFinal && $groups)
            <section class="space-y-5 rounded-2xl border border-gold-200 bg-white p-5 sm:p-7"><h2 class="text-xl font-extrabold">Penyelesaian draw di lokasi lomba</h2><p class="text-sm leading-6 text-stone-600">Peserta melakukan lempar dadu secara langsung. Catat urutan hasilnya dari total dadu tertinggi ke terendah. Jika lemparan masih seri, jangan menetapkan urutan sebelum hasil undian benar-benar berbeda. Sistem tidak melempar dadu atau memilih pemenang otomatis.</p>
                @foreach ($groups as $key => $ids)
                    <div class="rounded-xl border border-stone-200 p-4">
                        @if ($resolutions->has($key))
                            <p class="font-extrabold text-forest-800">Hasil undian telah dicatat</p><ol class="mt-3 list-inside list-decimal space-y-2 text-sm">@foreach ($resolutions[$key]->ordered_registration_ids as $id)<li>{{ $entries[$id]['entry']['bird_name'] }}</li>@endforeach</ol><p class="mt-3 whitespace-pre-line break-words text-sm leading-6 text-stone-600">{{ $resolutions[$key]->note }}</p><p class="mt-2 text-xs text-stone-500">Dicatat {{ $resolutions[$key]->created_at->translatedFormat('d F Y, H.i') }} WIB oleh {{ $resolutions[$key]->creator->name }}. Tidak dapat ditimpa.</p>
                        @else
                            <form method="POST" action="{{ route('admin.events.classes.ranking.draw', [$event, $competitionClass, $run]) }}" data-submit-feedback class="space-y-4">@csrf<input type="hidden" name="group_key" value="{{ $key }}">
                                <h3 class="font-extrabold">{{ count($ids) }} peserta dengan nilai TOPSIS sama</h3>
                                @foreach ($ids as $offset => $unusedId)<div><label for="draw-{{ $key }}-{{ $offset }}" class="mb-2 block text-sm font-bold">Urutan hasil undian {{ $offset + 1 }}</label><select id="draw-{{ $key }}-{{ $offset }}" name="ordered_registration_ids[]" required class="min-h-11 w-full rounded-xl border border-stone-300 bg-white p-3 text-sm focus-visible:outline-2 focus-visible:outline-forest-600"><option value="">Pilih peserta</option>@foreach ($ids as $id)<option value="{{ $id }}">{{ $entries[$id]['entry']['bird_name'] }} · {{ $entries[$id]['entry']['registration_number'] }}</option>@endforeach</select></div>@endforeach
                                <div><label for="draw-note-{{ $key }}" class="mb-2 block text-sm font-bold">Catatan hasil lempar dadu (wajib)</label><textarea id="draw-note-{{ $key }}" name="note" maxlength="2000" required rows="3" placeholder="Catat peserta, total dadu, serta urutan hasil undian di lokasi." class="w-full rounded-xl border border-stone-300 p-3 text-sm focus-visible:outline-2 focus-visible:outline-forest-600"></textarea></div>
                                <label class="flex items-start gap-3 text-sm leading-6"><input type="checkbox" name="confirmation" value="1" required class="mt-1 size-5 shrink-0 accent-forest-700">Saya memastikan seluruh urutan sesuai hasil lempar dadu nyata. Keputusan ini tercatat permanen.</label><x-ui.button data-pending-text="Mencatat…">Simpan hasil draw</x-ui.button>
                            </form>
                        @endif
                    </div>
                @endforeach
            </section>
        @endif
        @if (! $isFinal)
            <section class="rounded-2xl border border-red-200 bg-white p-5 sm:p-7"><h2 class="text-xl font-extrabold">Finalisasi hasil kelas</h2><p class="mt-3 text-sm leading-6 text-stone-600">Sistem menghitung ulang nilai sumber, menyimpan hasil resmi, lalu mengunci kelas. Penilaian, pelanggaran, penugasan, informasi kelas, dan hadiah tidak bisa diubah setelah final. Tidak ada pembatalan finalisasi pada MVP.</p>
                <a href="#finalize-fallback" data-dialog-open="finalize-dialog" class="mt-5 inline-flex min-h-11 items-center rounded-xl bg-red-700 px-5 py-3 text-sm font-bold text-white hover:bg-red-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-700">Tinjau &amp; finalisasi</a>
                <div id="finalize-fallback" data-dialog-fallback="finalize-dialog" class="mt-5"><form method="POST" action="{{ route('admin.events.classes.ranking.finalize', [$event, $competitionClass]) }}" data-dialog-form data-submit-feedback class="space-y-4">@csrf<h3 id="finalize-title" class="text-xl font-extrabold">Publikasikan hasil resmi?</h3><p class="text-sm leading-6 text-red-800">Pastikan hasil draw dan seluruh penilaian benar. Finalisasi tidak dapat dibatalkan.</p><x-form.field name="confirmation" label="Ketik FINALISASI" autocomplete="off" required /><div class="flex flex-wrap gap-3"><x-ui.button variant="danger" data-pending-text="Memfinalisasi…">Finalisasi &amp; publikasikan</x-ui.button><x-ui.button type="button" variant="secondary" data-dialog-close hidden class="hidden">Batal</x-ui.button></div></form></div>
                <dialog id="finalize-dialog" aria-labelledby="finalize-title" class="fixed inset-0 m-auto max-h-[90dvh] w-[calc(100%_-_2rem)] max-w-lg overflow-y-auto rounded-2xl bg-white p-6 text-stone-900 shadow-xl backdrop:bg-stone-900/60"><div data-dialog-content></div></dialog>
            </section>
        @endif
    </div>
</x-layouts.dashboard>
