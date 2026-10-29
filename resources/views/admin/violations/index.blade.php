<x-layouts.dashboard area="Admin" :user-name="auth()->user()->name">
    <div class="mx-auto max-w-5xl space-y-6">
        <a href="{{ route('admin.events.show', $event) }}" class="inline-flex min-h-11 items-center text-sm font-bold text-forest-700">← Kembali ke event</a>
        <div><p class="text-xs font-bold text-forest-700">{{ $event->name }}</p><h1 class="mt-2 text-2xl font-extrabold sm:text-3xl">Peringatan &amp; diskualifikasi</h1><p class="mt-3 text-sm leading-6 text-stone-600">Riwayat permanen penjurian untuk event ini. DQ tidak menghapus riwayat nilai.</p></div>
        <x-violation-history :violations="$violations" :show-registration="true" />
    </div>
</x-layouts.dashboard>
