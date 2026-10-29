@php($editing = isset($event))

<form method='POST' action='{{ $action }}' enctype='multipart/form-data' class='space-y-6'>
    @csrf
    @if ($editing) @method('PUT') @endif

    <x-ui.status />

    <section class='rounded-2xl border border-stone-200 bg-white p-5 shadow-sm sm:p-7'>
        <div class='border-b border-stone-100 pb-5'>
            <p class='text-xs font-extrabold uppercase tracking-[0.16em] text-forest-600'>01 · Identitas event</p>
            <h2 class='mt-2 text-xl font-extrabold text-stone-900'>Informasi utama</h2>
        </div>
        <div class='mt-6 grid gap-5 md:grid-cols-2'>
            <x-form.field name='name' label='Nama event' :value='$event->name ?? null' required class='md:col-span-2' />
            <div class='space-y-2 md:col-span-2'>
                <label for='description' class='block text-sm font-bold text-stone-700'>Deskripsi <span class='font-normal text-stone-400'>(opsional)</span></label>
                <textarea id='description' name='description' rows='5' class='block w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-sm shadow-sm focus:border-forest-500 focus:ring-4 focus:ring-forest-100'>{{ old('description', $event->description ?? '') }}</textarea>
                @error('description') <p class='text-sm font-medium text-red-600'>{{ $message }}</p> @enderror
            </div>
            <div class='space-y-2 md:col-span-2'>
                <label for='banner' class='block text-sm font-bold text-stone-700'>Banner event <span class='font-normal text-stone-400'>(JPG, PNG, atau WebP · maks. 5 MB)</span></label>
                @if ($editing)<x-event-artwork :event="$event" class="mb-3 aspect-[16/9] max-h-80 rounded-xl" />@endif
                <input id='banner' name='banner' type='file' accept='.jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp' class='block min-h-12 w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-sm shadow-sm file:mr-4 file:rounded-lg file:border-0 file:bg-forest-50 file:px-3 file:py-2 file:font-bold file:text-forest-700 focus:border-forest-500 focus:ring-4 focus:ring-forest-100'>
                @error('banner') <p class='text-sm font-medium text-red-600'>{{ $message }}</p> @enderror
            </div>
            <x-form.field name='location_name' label='Nama lokasi' :value='$event->location_name ?? null' required />
            <x-form.field name='contact_person' label='Narahubung' :value='$event->contact_person ?? null' />
            <div class='space-y-2 md:col-span-2'>
                <label for='address' class='block text-sm font-bold text-stone-700'>Alamat lengkap</label>
                <textarea id='address' name='address' rows='3' required class='block w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-sm shadow-sm focus:border-forest-500 focus:ring-4 focus:ring-forest-100'>{{ old('address', $event->address ?? '') }}</textarea>
                @error('address') <p class='text-sm font-medium text-red-600'>{{ $message }}</p> @enderror
            </div>
            <x-form.field name='contact_phone' label='Nomor kontak' type='tel' :value='$event->contact_phone ?? null' />
        </div>
    </section>

    <section class='rounded-2xl border border-stone-200 bg-white p-5 shadow-sm sm:p-7'>
        <div class='border-b border-stone-100 pb-5'>
            <p class='text-xs font-extrabold uppercase tracking-[0.16em] text-forest-600'>02 · Jadwal</p>
            <h2 class='mt-2 text-xl font-extrabold text-stone-900'>Pendaftaran dan pelaksanaan</h2>
        </div>
        <div class='mt-6 grid gap-5 md:grid-cols-2'>
            <x-form.field name='registration_open_at' label='Pendaftaran dibuka' type='datetime-local' :value='isset($event) && $event->registration_open_at ? $event->registration_open_at->format("Y-m-d\TH:i") : null' />
            <x-form.field name='registration_close_at' label='Pendaftaran ditutup' type='datetime-local' :value='isset($event) && $event->registration_close_at ? $event->registration_close_at->format("Y-m-d\TH:i") : null' />
            <x-form.field name='start_at' label='Event dimulai' type='datetime-local' :value='isset($event) ? $event->start_at->format("Y-m-d\TH:i") : null' required />
            <x-form.field name='end_at' label='Event selesai' type='datetime-local' :value='isset($event) && $event->end_at ? $event->end_at->format("Y-m-d\TH:i") : null' />
        </div>
    </section>

    <div class='flex flex-col-reverse gap-3 sm:flex-row sm:justify-end'>
        <x-ui.link-button :href='$back' variant='secondary'>Batal</x-ui.link-button>
        <x-ui.button>{{ $editing ? 'Simpan perubahan' : 'Buat event draft' }}</x-ui.button>
    </div>
</form>
