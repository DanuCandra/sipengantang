<form method="POST" action="{{ route('admin.events.payments.update', [$event, $payment]) }}" data-submit-feedback class="space-y-4">
    @csrf
    @method('PATCH')
    <input type="hidden" name="decision" value="{{ \App\Enums\PaymentStatus::Rejected->value }}">
    <div class="space-y-2">
        <label for="rejection_reason" class="block text-sm font-bold text-stone-700">Alasan penolakan</label>
        <textarea id="rejection_reason" name="rejection_reason" required maxlength="2000" rows="4" class="w-full rounded-xl border border-stone-300 bg-white px-4 py-3 text-sm focus:border-forest-500 focus:ring-4 focus:ring-forest-100" placeholder="Jelaskan hal yang perlu diperbaiki peserta.">{{ old('rejection_reason') }}</textarea>
        @error('rejection_reason')<p role="alert" class="text-sm font-semibold text-red-700">{{ $message }}</p>@enderror
    </div>
    <div class="flex flex-wrap justify-end gap-3">
        <x-ui.button type="button" variant="secondary" data-dialog-close hidden>Batal</x-ui.button>
        <x-ui.button class="bg-red-700 text-white hover:bg-red-800" data-pending-text="Menyimpan…">Tolak pembayaran</x-ui.button>
    </div>
</form>
