<x-layouts.dashboard area="Admin" :user-name="auth()->user()->name">
    <div class="mx-auto max-w-7xl space-y-6">
        <a href="{{ route('admin.events.payments.index', $event) }}" class="inline-flex min-h-11 items-center text-sm font-bold text-forest-700">← Antrean pembayaran</a>
        <x-ui.status />
        @error('payment')<p role="alert" class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm font-semibold text-red-800">{{ $message }}</p>@enderror
        <div class="grid items-start gap-6 xl:grid-cols-[minmax(0,1.25fr)_minmax(20rem,.75fr)]">
            <section class="min-w-0 rounded-2xl border border-stone-200 bg-white p-4 shadow-sm sm:p-6">
                <div class="flex flex-wrap items-center justify-between gap-3"><h1 class="text-xl font-extrabold">Bukti pembayaran</h1><x-ui.payment-status :status="$payment->status" /></div>
                <a href="{{ route('payments.proof', $payment) }}" target="_blank" rel="noopener" class="mt-5 block rounded-xl bg-stone-100 p-3 focus-visible:outline-2 focus-visible:outline-forest-600">
                    <img src="{{ route('payments.proof', $payment) }}" alt="Bukti pembayaran {{ $payment->registration->registration_number }}" class="max-h-[42rem] w-full rounded-lg object-contain">
                    <span class="mt-3 block text-center text-xs font-bold text-forest-700">Buka gambar ukuran penuh (tab baru)</span>
                </a>
            </section>
            <section class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm sm:p-7">
                <p class="break-all text-xs font-extrabold uppercase tracking-wider text-forest-600">{{ $payment->registration->registration_number }}</p>
                <h2 class="mt-3 text-2xl font-extrabold">{{ $payment->registration->participant->name }}</h2>
                <p class="mt-1 text-sm text-stone-600">{{ $payment->registration->bird->name }} · {{ $payment->registration->competitionClass->name }}</p>
                <dl class="mt-5 divide-y divide-stone-100 text-sm">
                    <div class="flex flex-wrap justify-between gap-2 py-3"><dt class="text-stone-500">Nominal transfer</dt><dd class="font-extrabold">Rp{{ number_format($payment->amount, 0, ',', '.') }}</dd></div>
                    <div class="flex flex-wrap justify-between gap-2 py-3"><dt class="text-stone-500">Biaya pendaftaran</dt><dd class="font-bold">Rp{{ number_format($payment->registration->registration_fee_snapshot, 0, ',', '.') }}</dd></div>
                    <div class="flex flex-wrap justify-between gap-2 py-3"><dt class="text-stone-500">Dikirim</dt><dd class="font-bold">{{ $payment->uploaded_at->translatedFormat('d F Y, H.i') }} WIB</dd></div>
                    @if ($payment->reviewed_at)
                        <div class="py-3"><dt class="text-stone-500">Ditinjau oleh {{ $payment->reviewer?->name ?? 'Admin' }}</dt><dd class="mt-1 font-bold">{{ $payment->reviewed_at->translatedFormat('d F Y, H.i') }} WIB</dd></div>
                    @endif
                </dl>
                @if ($payment->amount !== $payment->registration->registration_fee_snapshot)
                    <p role="status" class="mt-4 rounded-xl bg-gold-50 p-4 text-sm font-semibold leading-6 text-amber-900">Nominal transfer berbeda dari biaya saat pendaftaran. Pastikan kesesuaiannya dengan bukti sebelum memberikan keputusan.</p>
                @endif
                @if ($payment->rejection_reason)
                    <div class="mt-4 rounded-xl bg-red-50 p-4 text-sm leading-6 text-red-800"><p class="font-extrabold">Alasan penolakan</p><p class="mt-1">{{ $payment->rejection_reason }}</p></div>
                @endif
                @if ($payment->status === \App\Enums\PaymentStatus::WaitingVerification)
                    <div class="mt-6 flex flex-wrap gap-3 border-t border-stone-100 pt-5">
                        <a href="#reject-payment-fallback" data-dialog-open="reject-payment" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-red-200 px-5 py-2.5 text-sm font-bold text-red-700 hover:bg-red-50">Tolak</a>
                        <form method="POST" action="{{ route('admin.events.payments.update', [$event, $payment]) }}" data-submit-feedback>
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="decision" value="{{ \App\Enums\PaymentStatus::Verified->value }}">
                            <x-ui.button data-pending-text="Memverifikasi…">Verifikasi pembayaran</x-ui.button>
                        </form>
                    </div>
                    <dialog id="reject-payment" aria-labelledby="reject-payment-title" @if ($errors->has('rejection_reason')) data-auto-open @endif class="m-auto max-h-[90dvh] w-[calc(100%_-_2rem)] max-w-lg overflow-y-auto rounded-2xl border-0 bg-white p-6 shadow-xl backdrop:bg-stone-950/50">
                        <h2 id="reject-payment-title" class="mb-5 text-xl font-extrabold">Tolak pembayaran</h2>
                        <div data-dialog-content></div>
                    </dialog>
                    <div id="reject-payment-fallback" data-dialog-fallback="reject-payment" class="mt-5">
                        <noscript><p class="mb-3 text-sm font-bold">Isi alasan untuk menolak pembayaran.</p></noscript>
                        <div data-rejection-form>@include('admin.payments._reject-form')</div>
                    </div>
                @endif
                <a href="{{ route('admin.events.registrations.show', [$event, $payment->registration]) }}" class="mt-5 inline-flex min-h-11 items-center text-sm font-bold text-forest-700 underline underline-offset-4">Lihat pendaftaran dan semua riwayat</a>
            </section>
        </div>
    </div>
</x-layouts.dashboard>
