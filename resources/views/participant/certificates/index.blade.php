<x-layouts.dashboard area="Peserta" :user-name="auth()->user()->name">
    <div class="mx-auto max-w-6xl space-y-6">
        <header><p class="text-xs font-extrabold uppercase tracking-wider text-forest-600">Penghargaan resmi</p><h1 class="mt-2 text-3xl font-extrabold">Piagam saya</h1><p class="mt-3 text-sm leading-6 text-stone-600">Piagam diterbitkan untuk peringkat hadiah sesuai hasil FINAL. Data pada piagam mengikuti snapshot hasil resmi.</p></header>
        <x-ui.status />
        <div data-live-monitor data-live-certificate="true" data-live-private="true" data-live-channel="user.{{ auth()->id() }}" data-live-url="{{ route('participant.certificates.live', ['results' => $results->modelKeys()]) }}" class="rounded-xl bg-forest-50 p-4"><span data-live-status role="status" class="text-xs font-extrabold text-forest-800">Offline fallback</span><p data-live-description class="mt-2 text-xs text-stone-600">Memeriksa ketersediaan piagam.</p><p data-live-updated class="mt-2 text-xs text-stone-500"></p><a data-live-new-certificate hidden href="{{ route('participant.certificates.index') }}" class="mt-3 inline-flex min-h-11 items-center text-sm font-extrabold text-forest-800 underline">Piagam baru tersedia. Muat ulang daftar →</a></div>
        <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
            @forelse ($results as $result)
                <article data-certificate-result="{{ $result->id }}" data-ready="{{ in_array($result->id, $available, true) ? 'true' : 'false' }}" class="min-w-0 rounded-2xl border border-stone-200 bg-white p-5 shadow-sm"><span class="inline-flex rounded-full bg-gold-50 px-3 py-1 text-xs font-extrabold text-amber-900">Juara {{ $result->rank }}</span><h2 class="mt-4 break-words text-xl font-extrabold">{{ $result->entry_snapshot['bird_name'] }}</h2><p class="mt-2 break-words text-sm font-bold text-forest-800">{{ $result->rankingRun->input_snapshot['class']['name'] }}</p><p class="mt-2 break-words text-sm text-stone-600">{{ $result->rankingRun->input_snapshot['class']['event_name'] }}</p>
                    @if (in_array($result->id, $available, true))
                        <p class="mt-4 text-xs text-stone-500">Diterbitkan {{ $result->certificate->generated_at->translatedFormat('d F Y, H.i') }} WIB</p><div class="mt-5 flex flex-wrap gap-3"><x-ui.link-button :href="route('certificates.pdf', $result->certificate)" variant="secondary" target="_blank" rel="noopener">Lihat / cetak <span class="sr-only">(tab baru)</span></x-ui.link-button><x-ui.link-button :href="route('certificates.pdf', [$result->certificate, 'download' => 1])">Unduh PDF</x-ui.link-button></div>
                    @else
                        <p data-certificate-pending role="status" class="mt-5 rounded-xl bg-stone-50 p-4 text-sm leading-6 text-stone-600">Piagam menunggu pembuatan atau pemulihan file. Muat ulang setelah diproses; hubungi panitia jika belum tersedia.</p>
                    @endif
                </article>
            @empty
                <div class="col-span-full rounded-2xl border border-dashed border-stone-300 bg-white p-10 text-center"><h2 class="font-extrabold">Belum ada piagam pemenang</h2><p class="mt-3 text-sm leading-6 text-stone-500">Piagam muncul setelah hasil kelas difinalisasi dan Anda memperoleh peringkat hadiah.</p><x-ui.link-button :href="route('participant.registrations.index')" variant="secondary" class="mt-5">Lihat pendaftaran saya</x-ui.link-button></div>
            @endforelse
        </div>
        {{ $results->links() }}
    </div>
</x-layouts.dashboard>
