@props(['payments', 'event' => null])
<section class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm sm:p-7" aria-labelledby="payment-history-title">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h2 id="payment-history-title" class="text-xl font-extrabold">Riwayat pembayaran</h2>
        <span class="text-xs font-semibold text-stone-500">{{ $payments->total() }} percobaan</span>
    </div>
    <div class="mt-5 space-y-4">
        @forelse ($payments as $payment)
            <article class="rounded-xl border border-stone-200 p-4">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <p class="font-extrabold">Rp{{ number_format($payment->amount, 0, ',', '.') }}</p>
                    <x-ui.payment-status :status="$payment->status" />
                </div>
                <p class="mt-2 text-xs leading-6 text-stone-500">Dikirim {{ $payment->uploaded_at->translatedFormat('d F Y, H.i') }} WIB</p>
                @if ($payment->reviewed_at)
                    <p class="text-xs leading-6 text-stone-500">Ditinjau {{ $payment->reviewed_at->translatedFormat('d F Y, H.i') }} WIB</p>
                @endif
                @if ($payment->rejection_reason)
                    <p class="mt-3 rounded-lg bg-red-50 p-3 text-sm leading-6 text-red-800"><strong>Alasan penolakan:</strong> {{ $payment->rejection_reason }}</p>
                @endif
                <div class="mt-3 flex flex-wrap gap-4">
                    <a href="{{ route('payments.proof', $payment) }}" target="_blank" rel="noopener" class="inline-flex min-h-11 items-center text-sm font-bold text-forest-700 underline underline-offset-4">Lihat bukti <span class="sr-only">(tab baru)</span></a>
                    @if ($event)
                        <a href="{{ route('admin.events.payments.show', [$event, $payment]) }}" class="inline-flex min-h-11 items-center text-sm font-bold text-forest-700 underline underline-offset-4">Detail peninjauan</a>
                    @endif
                </div>
            </article>
        @empty
            <div class="rounded-xl border border-dashed border-stone-300 px-4 py-8 text-center text-sm leading-6 text-stone-500">Belum ada bukti pembayaran yang dikirim.</div>
        @endforelse
    </div>
    <div class="mt-5">{{ $payments->links() }}</div>
</section>
