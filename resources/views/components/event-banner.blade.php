@props(['event', 'editUrl'])
<section aria-label="Banner event" class="overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-stone-100 px-5 py-4">
        <h2 class="text-sm font-extrabold text-stone-900">Banner event</h2>
        <a href="{{ $editUrl }}#banner" class="inline-flex min-h-11 items-center rounded-lg px-3 text-sm font-bold text-forest-700 hover:bg-forest-50 focus-visible:outline-2 focus-visible:outline-forest-600">{{ $event->banner_path ? 'Ganti banner' : 'Unggah banner' }}</a>
    </div>
    @if ($event->banner_path)
        <div class="bg-stone-100 p-3 sm:p-5"><img src="{{ asset('storage/'.$event->banner_path) }}" alt="Banner event {{ $event->name }}" decoding="async" class="mx-auto max-h-[32rem] w-full rounded-xl object-contain"></div>
    @else
        <div class="flex flex-col items-center gap-3 px-5 py-10 text-center">
            <svg aria-hidden="true" class="size-10 text-forest-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="3"/><circle cx="8" cy="8" r="1.5"/><path d="m3 17 5-5 4 4 4-7 5 7"/></svg>
            <p class="text-sm font-bold text-stone-800">Banner event belum diunggah</p>
            <p class="max-w-lg text-sm leading-6 text-stone-600">Tambahkan banner melalui Edit Event agar poster acara tampil di halaman ini dan daftar event publik.</p>
        </div>
    @endif
</section>
