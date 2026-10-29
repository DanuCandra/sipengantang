@props(['event'])
<div {{ $attributes->merge(['class' => 'overflow-hidden bg-stone-100']) }}>
    @if ($event->banner_path)
        <img src="{{ asset('storage/'.$event->banner_path) }}" alt="Banner event {{ $event->name }}" decoding="async" class="h-full w-full object-contain">
    @else
        <div class="flex h-full min-h-24 flex-col items-center justify-center gap-2 px-4 py-6 text-center text-forest-700"><svg aria-hidden="true" class="size-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="3" y="3" width="18" height="18" rx="3"/><path d="m3 17 5-5 4 4 4-7 5 7"/><circle cx="8" cy="8" r="1.5"/></svg><span class="text-xs font-bold">Banner belum tersedia</span></div>
    @endif
</div>
