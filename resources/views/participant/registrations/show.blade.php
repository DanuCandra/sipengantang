<x-layouts.dashboard area="Peserta" :user-name="auth()->user()->name">
    <div class="mx-auto max-w-6xl space-y-6">
        <a href="{{ route('participant.registrations.index') }}" class="inline-flex min-h-11 items-center text-sm font-bold text-forest-700">← Pendaftaran saya</a>
        <x-ui.status />
        @error('gantangan_number')<p role="alert" class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm font-semibold text-red-800">{{ $message }}</p>@enderror
        @if ($registration->status === \App\Enums\RegistrationStatus::Disqualified)
            <p role="status" class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm leading-6 text-red-800"><strong>Diskualifikasi.</strong> Pendaftaran ini tidak dapat melanjutkan penjurian dan tidak diikutkan dalam peringkat. Hubungi panitia untuk informasi lebih lanjut.</p>
        @elseif ($registration->warning_count > 0)
            <p role="status" class="rounded-xl border border-gold-200 bg-gold-50 p-4 text-sm leading-6 text-amber-900"><strong>Peringatan tercatat.</strong> Pelanggaran peringatan berikutnya mengakibatkan diskualifikasi. Hubungi panitia bila membutuhkan penjelasan.</p>
        @endif
        @error('payment')
            <p role="alert" class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm font-semibold text-red-800">{{ $message }}</p>
        @enderror

        <div class="grid items-start gap-6 xl:grid-cols-[minmax(0,1.15fr)_minmax(18rem,.85fr)]">
            <div class="min-w-0 space-y-6">
                <section class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm sm:p-7">
                    <span class="rounded-full bg-forest-50 px-3 py-1 text-xs font-extrabold text-forest-800">{{ $registration->status->label() }}</span>
                    <p class="mt-5 break-all text-xs font-bold uppercase tracking-wider text-stone-500">{{ $registration->registration_number }}</p>
                    <h1 class="mt-2 text-2xl font-extrabold sm:text-3xl">{{ $registration->competitionClass->event->name }}</h1>
                    <p class="mt-2 text-sm text-stone-600">{{ $registration->competitionClass->name }} · {{ $registration->bird->name }}</p>
                    <dl class="mt-6 grid gap-3 sm:grid-cols-2">
                        <div class="rounded-xl bg-stone-50 p-4"><dt class="text-xs text-stone-500">Biaya pendaftaran</dt><dd class="mt-1 text-xl font-extrabold">Rp{{ number_format($registration->registration_fee_snapshot, 0, ',', '.') }}</dd></div>
                        <div class="rounded-xl bg-stone-50 p-4"><dt class="text-xs text-stone-500">Nomor gantangan</dt><dd class="mt-1 font-extrabold">{{ $registration->gantangan_number ?? 'Belum dipilih' }}</dd>@if ($registration->gantangan_number !== null)<p class="mt-2 text-xs leading-5 text-forest-800">Nomor sudah tersimpan dan terkunci.</p>@endif</div>
                    </dl>
                    <ol class="mt-6 space-y-4 border-t border-stone-100 pt-5 text-sm">
                        <li><p class="font-bold text-forest-800">✓ Pendaftaran dibuat</p><p class="mt-1 text-stone-500">{{ $registration->registered_at->translatedFormat('d F Y, H.i') }} WIB</p></li>
                        @if ($registration->status === \App\Enums\RegistrationStatus::WaitingVerification)
                            <li class="rounded-xl bg-gold-50 p-4"><p class="font-extrabold text-amber-900">Bukti dikirim · Menunggu verifikasi</p><p class="mt-1 leading-6 text-stone-600">Panitia sedang memeriksa bukti Anda. Hasil pemeriksaan akan tampil di halaman ini.</p></li>
                        @elseif ($registration->status === \App\Enums\RegistrationStatus::PaymentRejected)
                            <li class="rounded-xl bg-red-50 p-4"><p class="font-extrabold text-red-800">Pembayaran ditolak</p><p class="mt-1 leading-6 text-red-800">Periksa alasan pada riwayat pembayaran, lalu kirim bukti baru.</p></li>
                        @elseif ($registration->status === \App\Enums\RegistrationStatus::Verified)
                            <li class="rounded-xl bg-forest-50 p-4"><p class="font-extrabold text-forest-800">✓ Pembayaran terverifikasi</p><p class="mt-1 text-stone-600">{{ $registration->verified_at?->translatedFormat('d F Y, H.i') }} WIB</p>
                                <div class="mt-4 flex flex-wrap gap-3">
                                    <x-ui.link-button :href="route('participant.registrations.proof', $registration)" target="_blank" rel="noopener" variant="secondary">Lihat PDF <span class="sr-only">(tab baru)</span></x-ui.link-button>
                                    <x-ui.link-button :href="route('participant.registrations.proof', [$registration, 'download' => 1])">Unduh bukti pendaftaran</x-ui.link-button>
                                </div>
                            </li>
                        @endif
                    </ol>
                </section>

                @if ($canChooseGantanganNumber)
                    <section class="rounded-2xl border border-forest-200 bg-white p-5 shadow-sm sm:p-7" aria-labelledby="gantangan-title">
                        <h2 id="gantangan-title" class="text-xl font-extrabold">Pilih nomor gantangan Anda</h2>
                        <p class="mt-3 text-sm leading-6 text-stone-600">Pendaftaran ini belum memiliki nomor gantangan. Anda dapat memilihnya sekali sebelum penjurian dimulai.</p>
                        <form method="POST" action="{{ route('participant.registrations.gantangan-number', $registration) }}" data-submit-feedback class="mt-5 space-y-4">@csrf<x-gantangan-number-field /><x-ui.button data-pending-text="Menyimpan nomor…">Simpan nomor gantangan</x-ui.button></form>
                    </section>
                @endif
                @if (in_array($registration->status, [\App\Enums\RegistrationStatus::PendingPayment, \App\Enums\RegistrationStatus::PaymentRejected], true))
                    <section class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm sm:p-7" aria-labelledby="payment-title">
                        <p class="text-xs font-extrabold uppercase tracking-wider text-forest-600">Pembayaran pendaftaran</p>
                        <h2 id="payment-title" class="mt-2 text-xl font-extrabold">{{ $registration->status === \App\Enums\RegistrationStatus::PaymentRejected ? 'Kirim bukti baru' : 'Selesaikan pembayaran Anda' }}</h2>
                        <div class="mt-5 rounded-xl bg-forest-900 p-5 text-white">
                            <p class="text-xs text-forest-100">Total yang perlu dibayar</p>
                            <p class="mt-1 text-3xl font-extrabold">Rp{{ number_format($registration->registration_fee_snapshot, 0, ',', '.') }}</p>
                            @if (config('gantangscore.payment.bank_name') && config('gantangscore.payment.account_number') && config('gantangscore.payment.account_name'))
                                <div class="mt-5 border-t border-white/15 pt-4">
                                    <p class="text-sm font-bold">{{ config('gantangscore.payment.bank_name') }}</p>
                                    <p class="mt-1 break-all text-xl font-extrabold text-gold-300">{{ config('gantangscore.payment.account_number') }}</p>
                                    <p class="mt-1 text-sm text-forest-100">a.n. {{ config('gantangscore.payment.account_name') }}</p>
                                </div>
                            @else
                                <p class="mt-4 text-sm leading-6 text-forest-100">Hubungi panitia untuk mendapatkan rekening pembayaran sebelum melakukan transfer.</p>
                                @if ($registration->competitionClass->event->contact_phone)
                                    <p class="mt-2 text-sm font-bold text-gold-300">{{ $registration->competitionClass->event->contact_person ?: 'Panitia' }} · {{ $registration->competitionClass->event->contact_phone }}</p>
                                @endif
                            @endif
                            @if (config('gantangscore.payment.instructions'))
                                <p class="mt-4 text-sm leading-6 text-forest-100">{{ config('gantangscore.payment.instructions') }}</p>
                            @endif
                        </div>
                        <form method="POST" action="{{ route('participant.registrations.payments.store', $registration) }}" enctype="multipart/form-data" data-submit-feedback class="mt-6 space-y-5">
                            @csrf
                            <x-form.field name="amount" label="Nominal transfer (Rupiah)" type="number" :value="$registration->registration_fee_snapshot" min="1" max="999999999999" step="1" inputmode="numeric" required />
                            <div class="space-y-2">
                                <label for="proof" class="block text-sm font-bold text-stone-700">Bukti pembayaran</label>
                                <input id="proof" name="proof" type="file" accept="image/jpeg,image/png,image/webp" required aria-describedby="proof-help" class="block w-full min-w-0 rounded-xl border border-stone-300 bg-white p-3 text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-forest-50 file:px-3 file:py-2 file:font-bold file:text-forest-800 focus-visible:outline-2 focus-visible:outline-forest-600">
                                <p id="proof-help" class="text-xs leading-6 text-stone-500">JPG, JPEG, PNG, atau WebP, maksimal 5 MB. Pastikan nominal dan informasi transfer terbaca jelas.</p>
                                @error('proof')<p role="alert" class="text-sm font-semibold text-red-700">{{ $message }}</p>@enderror
                            </div>
                            <x-ui.button class="w-full sm:w-auto" data-pending-text="Mengirim bukti…">Kirim bukti pembayaran</x-ui.button>
                        </form>
                    </section>
                @endif
                <x-payment-history :payments="$payments" />
            </div>

            <aside class="rounded-2xl bg-forest-900 p-6 text-white">
                @if ($registration->bird->photo_path)
                    <img src="{{ asset('storage/'.$registration->bird->photo_path) }}" alt="Foto {{ $registration->bird->name }}" class="aspect-[4/3] w-full rounded-xl object-cover">
                @endif
                <p class="mt-5 text-xs font-extrabold uppercase tracking-wider text-gold-300">Burung terdaftar</p>
                <h2 class="mt-2 text-2xl font-extrabold">{{ $registration->bird->name }}</h2>
                <p class="mt-1 text-sm text-forest-100">{{ $registration->bird->bird_type }}</p>
                <p class="mt-5 border-t border-white/15 pt-4 text-sm leading-7 text-forest-100">{{ $registration->competitionClass->event->start_at->translatedFormat('d F Y, H.i') }} WIB<br>{{ $registration->competitionClass->event->location_name }}</p>
                <x-ui.link-button :href="route('events.show', $registration->competitionClass->event)" variant="secondary" class="mt-5 w-full">Lihat event</x-ui.link-button>
            </aside>
        </div>
    </div>
</x-layouts.dashboard>
