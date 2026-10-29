<x-layouts.dashboard area='Owner' :user-name='auth()->user()->name'>
    <div class='mx-auto max-w-7xl'>
        <x-ui.status />
        <div class='mt-5 rounded-3xl bg-forest-900 p-6 text-white shadow-soft sm:p-8'>
            <div class='flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between'><div><span class='rounded-full bg-white/10 px-3 py-1.5 text-xs font-extrabold text-gold-300'>{{ $event->status->label() }}</span><h1 class='mt-4 text-3xl font-extrabold tracking-tight'>{{ $event->name }}</h1><p class='mt-2 max-w-2xl text-sm leading-6 text-forest-100'>{{ $event->location_name }} · {{ $event->start_at->translatedFormat('d F Y, H.i') }} WIB</p></div><div class='flex flex-wrap gap-3'><x-ui.link-button :href='route("owner.events.edit", $event)' variant='secondary'>Edit event</x-ui.link-button>@if ($event->status === \App\Enums\EventStatus::Draft)<form method='POST' action='{{ route("owner.events.publish", $event) }}'>@csrf<x-ui.button class='bg-gold-400 text-forest-900 hover:bg-gold-300'>Publikasikan</x-ui.button></form>@endif</div></div>
        </div>

        <div class="mt-6"><x-event-banner :event="$event" :edit-url="route('owner.events.edit', $event)" /></div>
        <div class='mt-6 grid gap-6 xl:grid-cols-[minmax(0,1.25fr)_minmax(20rem,.75fr)]'>
            <section class='rounded-2xl border border-stone-200 bg-white p-5 shadow-sm sm:p-6'>
                <div class='flex items-center justify-between'><div><p class='text-xs font-extrabold uppercase tracking-wider text-forest-600'>Kelas lomba</p><h2 class='mt-1 text-xl font-extrabold'>{{ $event->competitionClasses->count() }} kelas disiapkan</h2></div></div>
                @forelse ($event->competitionClasses as $class)
                    <div class='mt-4 flex flex-col gap-3 rounded-xl border border-stone-100 bg-stone-50 p-4 sm:flex-row sm:items-center sm:justify-between'><div><p class='font-extrabold text-stone-900'>{{ $class->name }}</p><p class='mt-1 text-sm text-stone-500'>{{ $class->bird_type }} · Rp{{ number_format($class->registration_fee, 0, ',', '.') }} · {{ $class->prizes_count }} hadiah</p></div><span class='w-fit rounded-full bg-white px-3 py-1 text-xs font-bold text-stone-600'>{{ $class->status->label() }}</span></div>
                @empty
                    <div class='mt-5 rounded-xl border border-dashed border-stone-300 p-7 text-center text-sm text-stone-500'>Belum ada kelas. Admin yang ditugaskan dapat menambahkan kelas.</div>
                @endforelse
            </section>

            <aside id="tim-admin" class='rounded-2xl border border-stone-200 bg-white p-5 shadow-sm sm:p-6'>
                <p class='text-xs font-extrabold uppercase tracking-wider text-forest-600'>Tim event</p><h2 class='mt-1 text-xl font-extrabold'>Admin penanggung jawab</h2>
                <form method="POST" action="{{ route('owner.events.admins.store', $event) }}" class="mt-5 space-y-3">
                    @csrf
                    <label for="assign-admin-email" class="block text-sm font-bold text-stone-700">Pilih akun Admin</label>
                    <select id="assign-admin-email" name="email" required @disabled($availableAdmins->isEmpty()) @if ($errors->has('email')) aria-invalid="true" aria-describedby="assign-admin-error" @endif class="min-h-12 w-full min-w-0 rounded-xl border border-stone-300 bg-white px-3 text-sm text-stone-800 focus-visible:outline-2 focus-visible:outline-forest-600">
                        <option value="">Pilih Admin yang akan ditugaskan</option>
                        @foreach ($availableAdmins as $candidate)<option value="{{ $candidate->email }}" @selected(old('email') === $candidate->email)>{{ $candidate->name }} — {{ $candidate->email }}</option>@endforeach
                    </select>
                    @error('email')<p id="assign-admin-error" role="alert" class="text-sm font-medium text-red-700">{{ $message }}</p>@enderror
                    @if ($availableAdmins->isEmpty())<p class="text-sm leading-6 text-stone-600">Tidak ada akun Admin aktif yang belum ditugaskan. Anda dapat membuat akun baru di bawah.</p>@endif
                    <x-ui.button class="w-full" :disabled="$availableAdmins->isEmpty()">Tetapkan Admin pilihan</x-ui.button>
                </form>
                @if ($availableAdmins->hasPages())<div class="mt-4">{{ $availableAdmins->links() }}</div>@endif
                <details class='mt-4 rounded-xl border border-stone-200 p-4'><summary class='cursor-pointer text-sm font-extrabold text-forest-700'>Atau buat akun Admin baru</summary><form method='POST' action='{{ route("owner.events.admins.accounts.store", $event) }}' class='mt-4 space-y-3'>@csrf<x-form.field name='name' label='Nama lengkap' required /><x-form.field name='email' id='new-admin-email' label='Email baru' type='email' required /><x-form.field name='phone' label='Nomor telepon' type='tel' /><x-form.field name='password' label='Kata sandi sementara' type='password' required /><x-form.field name='password_confirmation' label='Konfirmasi kata sandi' type='password' required /><x-ui.button class='w-full'>Buat dan tugaskan</x-ui.button></form></details>
                <div class='mt-5 space-y-3 border-t border-stone-100 pt-5'>
                    @forelse ($event->admins as $admin)
                        <div class='flex items-center gap-3 rounded-xl bg-stone-50 p-3'><div class='grid size-9 shrink-0 place-items-center rounded-lg bg-forest-100 font-extrabold text-forest-700'>{{ str($admin->name)->substr(0,1)->upper() }}</div><div class='min-w-0 flex-1'><p class='truncate text-sm font-extrabold'>{{ $admin->name }}</p><p class='truncate text-xs text-stone-500'>{{ $admin->email }}</p></div><form method='POST' action='{{ route("owner.events.admins.destroy", [$event, $admin->pivot->id]) }}'>@csrf @method('DELETE')<button aria-label='Hapus penugasan {{ $admin->name }}' class='rounded-lg px-2 py-2 text-xs font-bold text-red-600 hover:bg-red-50'>Hapus</button></form></div>
                    @empty
                        <p class='text-sm text-stone-500'>Belum ada admin yang ditugaskan.</p>
                    @endforelse
                </div>
            </aside>
        </div>
        <div class="mt-6"><x-violation-history :violations="$violations" :show-registration="true" /></div>
        <section id="pantau-penjurian" class="mt-6 space-y-4"><h2 class="text-xl font-extrabold">Pantau penjurian</h2>@foreach ($event->competitionClasses as $class)<article><h3 class="mb-2 text-sm font-bold">{{ $class->name }}</h3><x-live-class :competition-class="$class" :endpoint="route('owner.events.classes.live', [$event, $class])" :channel="'event.'.$event->id.'.admin'" :private="true" /></article>@endforeach</section>
    </div>
</x-layouts.dashboard>
