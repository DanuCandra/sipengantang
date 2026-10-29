<x-layouts.dashboard area="Peserta" :user-name="auth()->user()->name">
    <div class="mx-auto max-w-6xl space-y-6">
        <a href="{{ route('events.show', $event) }}" class="inline-flex min-h-11 items-center text-sm font-bold text-forest-700">← {{ $event->name }}</a>
        <header><h1 class="text-3xl font-extrabold">Daftar kelas kompetisi</h1><p class="mt-3 text-sm leading-6 text-stone-600">Pilih kelas, burung, dan nomor gantangan Anda sebelum melanjutkan pembayaran.</p></header>
        <x-ui.status />
        @error('competition_class_id')<p role="alert" class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm font-semibold text-red-800">{{ $message }}</p>@enderror
        @if ($classes->isEmpty())
            <div class="rounded-2xl border border-dashed border-stone-300 bg-white p-8 text-center"><h2 class="font-extrabold">Belum ada kelas yang membuka pendaftaran</h2><p class="mt-3 text-sm text-stone-600">Silakan kembali memeriksa agenda nanti.</p></div>
        @else
            <div class="grid items-start gap-6 lg:grid-cols-[minmax(0,.8fr)_minmax(0,1.2fr)]">
                <section class="min-w-0 rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
                    <h2 class="text-sm font-extrabold text-forest-700">1 · Pilih kelas</h2>
                    <div class="mt-4 space-y-3">
                        @foreach ($classes as $class)
                            <a href="{{ route('participant.registrations.create', ['event' => $event, 'class' => $class->id]) }}" @if ($selectedClass?->id === $class->id) aria-current="page" @endif @class(['block rounded-xl border p-4 transition focus-visible:outline-2 focus-visible:outline-forest-600', 'border-forest-500 bg-forest-50' => $selectedClass?->id === $class->id, 'border-stone-200 hover:border-forest-300' => $selectedClass?->id !== $class->id])>
                                <p class="break-words font-extrabold">{{ $class->name }}</p><p class="mt-2 text-sm text-stone-600">{{ $class->bird_type }}</p>
                                <div class="mt-3 flex flex-wrap justify-between gap-2 text-sm"><span class="font-extrabold text-forest-800">Rp{{ number_format($class->registration_fee, 0, ',', '.') }}</span><span class="text-stone-600">Terisi {{ $class->occupied_slots }} / {{ $class->quota ?? 'tidak dibatasi' }}</span></div>
                            </a>
                        @endforeach
                    </div>
                </section>
                <section class="min-w-0 rounded-2xl border border-stone-200 bg-white p-5 shadow-sm sm:p-6">
                    <h2 class="text-sm font-extrabold text-forest-700">2 · Pilih burung dan nomor gantangan</h2>
                    @if ($selectedClass)<div class="mt-4 rounded-xl bg-gold-50 p-4"><p class="break-words font-extrabold">{{ $selectedClass->name }}</p><p class="mt-2 text-sm text-stone-600">Hanya burung jenis {{ $selectedClass->bird_type }} yang dapat dipilih.</p></div>@endif
                    @if ($birds->isEmpty())
                        <div class="mt-5 rounded-xl border border-dashed border-stone-300 p-6 text-center"><p class="font-extrabold">Belum ada burung yang kompatibel</p><p class="mt-3 text-sm text-stone-600">Tambahkan profil burung dengan jenis {{ $selectedClass?->bird_type }} terlebih dahulu.</p><x-ui.link-button :href="route('participant.birds.create')" class="mt-4">Tambah burung</x-ui.link-button></div>
                    @else
                        <form method="POST" action="{{ route('participant.registrations.store') }}" data-submit-feedback class="mt-5 space-y-5">
                            @csrf
                            <input type="hidden" name="competition_class_id" value="{{ $selectedClass->id }}">
                            <fieldset><legend class="mb-3 text-sm font-bold text-stone-700">Burung yang didaftarkan</legend><div class="grid gap-3 sm:grid-cols-2">
                                @foreach ($birds as $bird)
                                    <label class="min-w-0 cursor-pointer rounded-xl border border-stone-200 p-3 has-[:checked]:border-forest-600 has-[:checked]:bg-forest-50 has-[:focus-visible]:outline-2 has-[:focus-visible]:outline-offset-2 has-[:focus-visible]:outline-forest-600">
                                        <input type="radio" name="bird_id" value="{{ $bird->id }}" class="sr-only" @checked(old('bird_id') == $bird->id) required>
                                        <div class="flex items-center gap-3">@if ($bird->photo_path)<img src="{{ asset('storage/'.$bird->photo_path) }}" alt="Foto {{ $bird->name }}" class="size-14 shrink-0 rounded-xl object-cover">@endif<div class="min-w-0"><p class="break-words font-extrabold">{{ $bird->name }}</p><p class="mt-1 text-xs text-stone-600">{{ $bird->bird_type }}</p></div></div>
                                    </label>
                                @endforeach
                            </div>@error('bird_id')<p role="alert" class="mt-3 text-sm font-semibold text-red-700">{{ $message }}</p>@enderror</fieldset>
                            <x-gantangan-number-field />
                            <div class="rounded-2xl bg-forest-900 p-5 text-white">
                                <h3 class="text-sm font-extrabold text-gold-300">3 · Konfirmasi pendaftaran</h3>
                                <dl class="mt-4 space-y-3 text-sm"><div class="flex justify-between gap-4"><dt class="text-forest-100">Event</dt><dd class="break-words text-right font-bold">{{ $event->name }}</dd></div><div class="flex justify-between gap-4"><dt class="text-forest-100">Kelas</dt><dd class="break-words text-right font-bold">{{ $selectedClass->name }}</dd></div><div class="flex justify-between gap-4"><dt class="text-forest-100">Biaya</dt><dd class="text-right font-bold">Rp{{ number_format($selectedClass->registration_fee, 0, ',', '.') }}</dd></div></dl>
                                <p class="mt-4 text-xs leading-6 text-forest-100">Pembayaran tetap diverifikasi panitia. Nomor gantangan langsung dipesan setelah pendaftaran berhasil.</p>
                                <x-ui.button class="mt-5 w-full" data-pending-text="Mendaftarkan…">Konfirmasi pendaftaran</x-ui.button>
                            </div>
                        </form>
                    @endif
                </section>
            </div>
        @endif
    </div>
</x-layouts.dashboard>
