@props(['competitionClass', 'endpoint', 'channel', 'private' => false, 'fragmentUrl' => null])
<section data-live-monitor data-live-url="{{ $endpoint }}" data-live-channel="{{ $channel }}" data-live-private="{{ $private ? 'true' : 'false' }}" data-live-class="{{ $competitionClass->id }}" @if ($fragmentUrl) data-live-fragment-url="{{ $fragmentUrl }}" @endif class="rounded-2xl border border-forest-200 bg-forest-50 p-4 sm:p-5" aria-label="Progres langsung {{ $competitionClass->name }}">
    <div class="flex flex-wrap items-center justify-between gap-3"><p class="text-sm font-extrabold text-forest-900">Progres penjurian</p><span data-live-status role="status" aria-live="polite" class="rounded-full border border-stone-200 bg-white px-3 py-1 text-xs font-bold text-stone-700">Offline fallback</span></div>
    <p data-live-description class="mt-2 text-xs leading-6 text-stone-600">Memuat progres terbaru. Formulir tetap dapat digunakan saat koneksi langsung terputus.</p>
    <div data-live-progress role="progressbar" aria-label="Progres penjurian kelas" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0" class="mt-3 h-2 overflow-hidden rounded-full bg-forest-100"><div data-live-progress-bar class="h-full rounded-full bg-forest-600 transition-[width] duration-200 motion-reduce:transition-none" style="width: 0%"></div></div>
    <p class="mt-3 text-xs leading-6 text-stone-600"><span data-live-counts>Memuat progres…</span><span data-live-updated class="ml-2"></span></p>
    <p data-live-change hidden class="mt-3 text-sm font-bold text-forest-800"></p>
    <a data-live-final hidden class="mt-3 inline-flex min-h-11 items-center text-sm font-extrabold text-forest-800 underline underline-offset-4">Hasil resmi telah tersedia →</a>
    <noscript><p class="mt-3 text-sm text-stone-700">JavaScript tidak aktif. Muat ulang halaman untuk melihat status terbaru; formulir tetap dapat digunakan.</p></noscript>
</section>
