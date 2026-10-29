<x-layouts.public :title="'Hasil resmi '.$info['class_name']">
    <div class="mx-auto max-w-6xl px-5 py-10 sm:py-14">
        <a href="{{ route('events.show', $event) }}" class="inline-flex min-h-11 items-center text-sm font-bold text-forest-700">← Informasi event</a>
        <header class="my-6 rounded-3xl bg-forest-900 p-6 text-white sm:p-10"><p class="text-xs font-extrabold uppercase tracking-wider text-gold-300">Hasil resmi · FINAL</p><h1 class="mt-4 text-3xl font-extrabold sm:text-4xl">{{ $info['class_name'] }}</h1><p class="mt-3 text-forest-100">{{ $info['event_name'] }}</p><p class="mt-4 text-xs text-forest-100">Difinalisasi {{ $info['finalized_at']->translatedFormat('d F Y, H.i') }} WIB. Hasil dibaca dari snapshot resmi.</p></header>
        <p class="mb-6 text-sm leading-6 text-stone-600">Nilai preferensi TOPSIS semakin tinggi berarti peringkat semakin baik. Jika hasil TOPSIS draw, urutan resmi mengikuti hasil lempar dadu yang dicatat panitia di lokasi lomba.</p>
        <x-ranking-results :results="$results" :final="true" />
        <div class="mt-6">{{ $results->links() }}</div>
        @if ($info['prizes'])<section class="mt-8 rounded-2xl border border-stone-200 bg-white p-6"><h2 class="text-xl font-extrabold">Hadiah kelas</h2><div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">@foreach ($info['prizes'] as $prize)<article class="rounded-xl bg-gold-50 p-4"><p class="text-xs font-bold text-amber-900">Peringkat {{ $prize['rank'] }}</p><h3 class="mt-2 font-extrabold">{{ $prize['title'] }}</h3>@if ($prize['cash_amount'])<p class="mt-2 text-sm font-bold">Rp{{ number_format($prize['cash_amount'], 0, ',', '.') }}</p>@endif<p class="mt-2 text-sm leading-6 text-stone-600">{{ $prize['description'] }}</p></article>@endforeach</div></section>@endif
    </div>
</x-layouts.public>
