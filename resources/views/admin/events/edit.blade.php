<x-layouts.dashboard area='Admin' :user-name='auth()->user()->name'>
    <div class='mx-auto max-w-4xl'><p class='text-xs font-extrabold uppercase tracking-[0.16em] text-forest-600'>Edit event</p><h1 class='mt-2 text-3xl font-extrabold tracking-tight'>{{ $event->name }}</h1><p class='mt-2 text-sm text-stone-500'>Perubahan informasi tidak mengubah status publikasi event.</p><div class='mt-7'>@include('events._form', ['action' => route('admin.events.update', $event), 'back' => route('admin.events.show', $event), 'event' => $event])</div></div>
</x-layouts.dashboard>
