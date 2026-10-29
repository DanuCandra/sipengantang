@props(['violations', 'showRegistration' => false])
<section class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm sm:p-7" aria-labelledby="violation-history-title">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h2 id="violation-history-title" class="text-xl font-extrabold">Riwayat pelanggaran</h2>
        <span class="text-xs font-semibold text-stone-500">{{ $violations->total() }} catatan internal</span>
    </div>
    <p class="mt-2 text-xs leading-6 text-stone-500">Khusus petugas berwenang. Catatan tidak ditampilkan ke publik atau peserta.</p>
    <div class="mt-5 space-y-4">
        @forelse ($violations as $violation)
            <article class="min-w-0 rounded-xl border border-stone-200 p-4">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <span @class(['rounded-full px-3 py-1 text-xs font-extrabold', 'bg-red-50 text-red-800' => $violation->action === \App\Enums\ViolationAction::Disqualification, 'bg-gold-50 text-amber-900' => $violation->action === \App\Enums\ViolationAction::Warning])>{{ $violation->action->label() }}</span>
                    <time class="text-xs text-stone-500" datetime="{{ $violation->created_at->toIso8601String() }}">{{ $violation->created_at->translatedFormat('d F Y, H.i') }} WIB</time>
                </div>
                @if ($showRegistration)
                    <p class="mt-3 font-extrabold">{{ $violation->registration->bird->name }} · {{ $violation->registration->competitionClass->name }}</p>
                    <p class="mt-1 break-all text-xs text-stone-500">{{ $violation->registration->registration_number }}</p>
                @endif
                <p class="mt-3 break-words text-sm font-bold">{{ $violation->reason }}</p>
                @if ($violation->note)<p class="mt-2 whitespace-pre-line break-words text-sm leading-6 text-stone-700">{{ $violation->note }}</p>@endif
                <p class="mt-3 text-xs text-stone-500">Dicatat oleh {{ $violation->judge->name }}</p>
            </article>
        @empty
            <p class="rounded-xl border border-dashed border-stone-300 px-4 py-8 text-center text-sm text-stone-500">Belum ada peringatan atau diskualifikasi.</p>
        @endforelse
    </div>
    <div class="mt-5">{{ $violations->links() }}</div>
</section>
