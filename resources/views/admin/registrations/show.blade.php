<x-layouts.dashboard area="Admin" :user-name="auth()->user()->name">
    <div class="mx-auto max-w-5xl space-y-6">
        <a href="{{ route('admin.events.registrations.index', $event) }}" class="inline-flex min-h-11 items-center text-sm font-bold text-forest-700">← Daftar peserta</a>
        <div class="grid items-start gap-6 lg:grid-cols-[minmax(0,1.1fr)_minmax(18rem,.9fr)]">
            <div class="space-y-6">
                <section class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm sm:p-7">
                    <span class="rounded-full bg-forest-50 px-3 py-1 text-xs font-extrabold text-forest-800">{{ $registration->status->label() }}</span>
                    <p class="mt-5 break-all text-xs font-bold uppercase tracking-wider text-stone-500">{{ $registration->registration_number }}</p>
                    <h1 class="mt-2 text-3xl font-extrabold">{{ $registration->participant->name }}</h1>
                    <p class="mt-2 text-sm text-stone-600">{{ $registration->bird->name }} · {{ $registration->competitionClass->name }}</p>
                    <dl class="mt-6 divide-y divide-stone-100 rounded-xl bg-stone-50 px-4 text-sm">
                        <div class="flex flex-wrap justify-between gap-2 py-3"><dt class="text-stone-500">Email</dt><dd class="break-all font-bold">{{ $registration->participant->email }}</dd></div>
                        <div class="flex flex-wrap justify-between gap-2 py-3"><dt class="text-stone-500">Telepon</dt><dd class="font-bold">{{ $registration->participant->phone ?: '-' }}</dd></div>
                        <div class="flex flex-wrap justify-between gap-2 py-3"><dt class="text-stone-500">Biaya pendaftaran</dt><dd class="font-bold">Rp{{ number_format($registration->registration_fee_snapshot, 0, ',', '.') }}</dd></div>
                        <div class="flex flex-wrap justify-between gap-2 py-3"><dt class="text-stone-500">Terdaftar</dt><dd class="font-bold">{{ $registration->registered_at->translatedFormat('d F Y, H.i') }} WIB</dd></div>
                    </dl>
                </section>
                <x-payment-history :payments="$payments" :event="$event" />
                <x-violation-history :violations="$violations" />
            </div>
            <aside class="rounded-2xl bg-forest-900 p-6 text-white">
                @if ($registration->bird->photo_path)
                    <img src="{{ asset('storage/'.$registration->bird->photo_path) }}" alt="Foto {{ $registration->bird->name }}" class="aspect-square w-full rounded-xl object-cover">
                @endif
                <p class="mt-5 text-xs font-extrabold uppercase tracking-wider text-gold-300">Burung peserta</p>
                <h2 class="mt-2 text-2xl font-extrabold">{{ $registration->bird->name }}</h2>
                <p class="mt-1 text-sm text-forest-100">{{ $registration->bird->bird_type }}</p>
                <p class="mt-4 text-sm leading-6 text-forest-100">{{ $registration->bird->description ?: 'Tidak ada keterangan tambahan.' }}</p>
            </aside>
        </div>
    </div>
</x-layouts.dashboard>
