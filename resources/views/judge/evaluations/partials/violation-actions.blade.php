<section class="rounded-2xl border border-stone-200 bg-white p-5 sm:p-6" aria-labelledby="violation-actions-title">
    <h2 id="violation-actions-title" class="text-lg font-extrabold">Tindakan pelanggaran</h2>
    <p class="mt-2 text-sm leading-6 text-stone-600">Periksa kejadian dan riwayat sebelum menyimpan. Tindakan dicatat permanen; DQ tidak dapat dibatalkan melalui halaman ini.</p>
    @foreach (['warning' => $warningReasons, 'disqualification' => $disqualificationReasons] as $formType => $reasons)
        @php
            $isDirect = $formType === 'disqualification';
            $willDisqualify = $isDirect || $registration->warning_count > 0;
            $formId = 'violation-'.$formType;
            $hasFormErrors = $errors->any() && old('violation_form') === $formType;
            $formOld = fn ($key) => old('violation_form') === $formType ? old($key, '') : '';
            $title = $isDirect ? 'Diskualifikasi langsung' : ($willDisqualify ? 'Pelanggaran berulang → DQ' : 'Beri peringatan');
        @endphp
        @if ($isDirect)<details @if ($hasFormErrors) open @endif class="mt-5 rounded-xl border border-red-200 p-4"><summary class="min-h-11 cursor-pointer py-2 text-sm font-extrabold text-red-800">Tindakan berisiko: diskualifikasi langsung</summary>@endif
        <a href="#{{ $formId }}-fallback" data-dialog-open="{{ $formId }}" class="mt-4 inline-flex min-h-11 items-center justify-center rounded-xl border px-5 py-3 text-sm font-extrabold focus-visible:outline-2 focus-visible:outline-offset-2 {{ $willDisqualify ? 'border-red-300 bg-red-50 text-red-800 hover:bg-red-100' : 'border-gold-300 bg-gold-50 text-amber-900 hover:bg-gold-100' }}">{{ $title }}</a>
        <div id="{{ $formId }}-fallback" data-dialog-fallback="{{ $formId }}" class="mt-4 rounded-xl border border-stone-200 p-4">
            <form data-live-registration-form="{{ $registration->id }}" method="POST" action="{{ route($isDirect ? 'judge.disqualifications.store' : 'judge.warnings.store', [$competitionClass, $registration]) }}" data-dialog-form data-submit-feedback class="space-y-4">
                @csrf
                <input type="hidden" name="violation_form" value="{{ $formType }}">
                <h3 id="{{ $formId }}-title" class="text-xl font-extrabold">{{ $title }}</h3>
                <p class="break-words text-sm leading-6 text-stone-600"><strong>{{ $registration->bird->name }}</strong> · Gantangan {{ $registration->gantangan_number ?? 'belum ditentukan' }}</p>
                @if ($willDisqualify)<p class="rounded-xl bg-red-50 p-4 text-sm leading-6 text-red-800">{{ $isDirect ? 'Pelanggaran berat dapat langsung mengakibatkan diskualifikasi tanpa peringatan.' : 'Peserta sudah mendapat peringatan. Menyimpan pelanggaran ini akan mendiskualifikasi peserta.' }} Nilai tetap tersimpan, tetapi peserta tidak diikutkan dalam peringkat.</p>@endif
                <div class="space-y-2">
                    <label for="{{ $formId }}-reason-code" class="block text-sm font-bold">Alasan pelanggaran</label>
                    <select id="{{ $formId }}-reason-code" name="reason_code" required class="min-h-11 w-full rounded-xl border border-stone-300 bg-white p-3 text-sm focus-visible:outline-2 focus-visible:outline-forest-600">
                        <option value="">Pilih alasan</option>
                        @foreach ($reasons as $reason)<option value="{{ $reason->value }}" @selected($formOld('reason_code') === $reason->value)>{{ $reason->label() }}</option>@endforeach
                    </select>
                </div>
                <div class="space-y-2">
                    <label for="{{ $formId }}-reason" class="block text-sm font-bold">Penjelasan alasan lainnya</label>
                    <input id="{{ $formId }}-reason" name="reason" value="{{ $formOld('reason') }}" maxlength="255" aria-describedby="{{ $formId }}-reason-help" class="min-h-11 w-full rounded-xl border border-stone-300 p-3 text-sm focus-visible:outline-2 focus-visible:outline-forest-600">
                    <p id="{{ $formId }}-reason-help" class="text-xs text-stone-500">Wajib diisi jika memilih Lainnya. Maksimal 255 karakter.</p>
                </div>
                <div class="space-y-2">
                    <label for="{{ $formId }}-note" class="block text-sm font-bold">Catatan kejadian {{ $willDisqualify ? '(wajib)' : '(opsional)' }}</label>
                    <textarea id="{{ $formId }}-note" name="note" rows="4" maxlength="2000" @required($willDisqualify) class="w-full rounded-xl border border-stone-300 p-3 text-sm focus-visible:outline-2 focus-visible:outline-forest-600">{{ $formOld('note') }}</textarea>
                    <p class="text-xs text-stone-500">Catatan internal petugas, maksimal 2.000 karakter.</p>
                </div>
                @if ($willDisqualify)
                    <label class="flex items-start gap-3 rounded-xl border border-red-200 p-4 text-sm font-bold leading-6 text-red-800"><input type="checkbox" name="confirm_disqualification" value="1" required class="mt-1 size-5 shrink-0 accent-red-700">Saya memastikan peserta dan kejadian sudah benar serta memahami tindakan ini mengakibatkan diskualifikasi.</label>
                @endif
                <div class="flex flex-col gap-3 sm:flex-row-reverse">
                    <x-ui.button :variant="$willDisqualify ? 'danger' : 'primary'" data-pending-text="Mencatat…">{{ $willDisqualify ? 'Konfirmasi diskualifikasi' : 'Simpan peringatan' }}</x-ui.button>
                    <x-ui.button type="button" variant="secondary" data-dialog-close hidden class="hidden">Batal</x-ui.button>
                </div>
            </form>
        </div>
        @if ($isDirect)</details>@endif
        <dialog id="{{ $formId }}" aria-labelledby="{{ $formId }}-title" @if ($hasFormErrors) data-auto-open @endif class="fixed inset-0 m-auto max-h-[90dvh] w-[calc(100%_-_2rem)] max-w-lg overflow-y-auto rounded-2xl border-0 bg-white p-5 text-stone-900 shadow-xl backdrop:bg-stone-900/60 sm:p-7"><div data-dialog-content></div></dialog>
    @endforeach
</section>
