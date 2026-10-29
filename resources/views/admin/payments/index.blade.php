<x-layouts.dashboard area="Admin" :user-name="auth()->user()->name">
    <div class="mx-auto max-w-7xl space-y-6">
        <a href="{{ route('admin.events.show', $event) }}" class="inline-flex min-h-11 items-center text-sm font-bold text-forest-700">← {{ $event->name }}</a>
        <div>
            <p class="text-xs font-extrabold uppercase tracking-wider text-forest-600">Pembayaran peserta</p>
            <h1 class="mt-2 text-3xl font-extrabold">Antrean verifikasi</h1>
            <p class="mt-2 text-sm leading-6 text-stone-600">Periksa bukti transfer dan nominal pendaftaran sebelum menyetujui pembayaran.</p>
        </div>
        <x-ui.status />
        <nav aria-label="Filter status pembayaran" class="flex flex-wrap gap-2">
            @foreach (\App\Enums\PaymentStatus::cases() as $option)
                <a href="{{ route('admin.events.payments.index', [$event, 'status' => $option->value]) }}" @if ($status === $option->value) aria-current="page" @endif @class(['inline-flex min-h-11 items-center rounded-xl border px-4 py-2 text-sm font-bold transition', 'border-forest-600 bg-forest-600 text-white' => $status === $option->value, 'border-stone-200 bg-white text-stone-700 hover:border-forest-400' => $status !== $option->value])>{{ $option->label() }}</a>
            @endforeach
        </nav>
        <div class="grid gap-4 md:grid-cols-2 2xl:grid-cols-3">
            @forelse ($payments as $payment)
                <article class="flex min-w-0 flex-col rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div><h2 class="text-lg font-extrabold">{{ $payment->registration->participant->name }}</h2><p class="mt-1 text-sm text-stone-600">{{ $payment->registration->bird->name }}</p></div>
                        <x-ui.payment-status :status="$payment->status" />
                    </div>
                    <p class="mt-4 text-xs font-semibold text-stone-500">{{ $payment->registration->competitionClass->name }}</p>
                    <p class="mt-1 break-all text-xs text-stone-500">{{ $payment->registration->registration_number }}</p>
                    <p class="mt-1 text-2xl font-extrabold text-forest-800">Rp{{ number_format($payment->amount, 0, ',', '.') }}</p>
                    @if ($payment->amount !== $payment->registration->registration_fee_snapshot)
                        <p class="mt-2 rounded-lg bg-gold-50 p-3 text-xs font-bold leading-5 text-amber-900">Nominal berbeda dari biaya pendaftaran Rp{{ number_format($payment->registration->registration_fee_snapshot, 0, ',', '.') }}. Periksa sebelum memutuskan.</p>
                    @endif
                    <p class="mt-4 text-xs text-stone-500">{{ $payment->uploaded_at->translatedFormat('d F Y, H.i') }} WIB</p>
                    <x-ui.link-button :href="route('admin.events.payments.show', [$event, $payment])" variant="secondary" class="mt-5 w-full">Periksa pembayaran</x-ui.link-button>
                </article>
            @empty
                <div class="col-span-full rounded-2xl border border-dashed border-stone-300 bg-white px-5 py-14 text-center">
                    <h2 class="text-lg font-extrabold">Tidak ada pembayaran pada status ini</h2>
                    <p class="mt-2 text-sm text-stone-500">Bukti yang dikirim peserta akan muncul di antrean menunggu verifikasi.</p>
                </div>
            @endforelse
        </div>
        {{ $payments->links() }}
    </div>
</x-layouts.dashboard>
