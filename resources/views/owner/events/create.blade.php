<x-layouts.dashboard area='Owner' :user-name='auth()->user()->name'>
    <div class='mx-auto max-w-4xl'><p class='text-xs font-extrabold uppercase tracking-[0.16em] text-forest-600'>Event baru</p><h1 class='mt-2 text-3xl font-extrabold tracking-tight'>Susun agenda perlombaan</h1><p class='mt-2 text-sm text-stone-500'>Event disimpan sebagai draft sehingga aman untuk dilengkapi sebelum dipublikasikan.</p><div class='mt-7'>@include('events._form', ['action' => route('owner.events.store'), 'back' => route('owner.events.index')])</div></div>
</x-layouts.dashboard>
