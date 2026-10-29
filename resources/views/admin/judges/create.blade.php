<x-layouts.dashboard area="Admin" :user-name="auth()->user()->name">
    <div class="mx-auto max-w-2xl space-y-6">
        <a href="{{ route('admin.events.classes.judges.index', [$event, $competitionClass]) }}" class="inline-flex min-h-11 items-center text-sm font-bold text-forest-700">← Penugasan Juri</a>
        <div><h1 class="text-3xl font-extrabold">Buat akun Juri</h1><p class="mt-3 text-sm leading-6 text-stone-600">Akun baru langsung ditugaskan ke kelas {{ $competitionClass->name }}.</p></div>
        <x-ui.status />
        @error('judging')<p role="alert" class="rounded-xl bg-red-50 p-4 text-sm font-bold text-red-800">{{ $message }}</p>@enderror
        <form method="POST" action="{{ route('admin.events.classes.judges.accounts', [$event, $competitionClass]) }}" class="space-y-5 rounded-2xl border border-stone-200 bg-white p-5 shadow-sm sm:p-7" data-submit-feedback>
            @csrf
            <x-form.field name="name" label="Nama Juri" required />
            <x-form.field name="email" label="Email Juri" type="email" autocomplete="off" required />
            <x-form.field name="phone" label="Nomor telepon" type="tel" />
            <x-form.field name="password" label="Password (minimal 8 karakter)" type="password" autocomplete="new-password" required />
            <x-form.field name="password_confirmation" label="Konfirmasi password" type="password" autocomplete="new-password" required />
            <x-ui.button class="w-full" data-pending-text="Membuat akun…">Buat & tugaskan Juri</x-ui.button>
        </form>
    </div>
</x-layouts.dashboard>
