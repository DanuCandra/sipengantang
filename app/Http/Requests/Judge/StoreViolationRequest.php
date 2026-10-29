<?php

namespace App\Http\Requests\Judge;

use App\Enums\ViolationReasonCode;
use App\Models\Violation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreViolationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $registration = $this->route('registration');

        return $registration->competition_class_id === $this->route('competitionClass')->id
            && $this->user()->can('create', [Violation::class, $registration]);
    }

    public function rules(): array
    {
        return [
            'reason_code' => ['required', Rule::enum(ViolationReasonCode::class)],
            'reason' => [Rule::requiredIf($this->input('reason_code') === ViolationReasonCode::Other->value), 'nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:2000'],
            'confirm_disqualification' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'reason_code.required' => 'Pilih alasan pelanggaran.', 'reason_code.enum' => 'Alasan pelanggaran tidak valid.',
            'reason.required' => 'Jelaskan alasan bila memilih Lainnya.', 'reason.string' => 'Alasan harus berupa teks.', 'reason.max' => 'Alasan maksimal 255 karakter.',
            'note.string' => 'Catatan harus berupa teks.', 'note.max' => 'Catatan maksimal 2.000 karakter.',
            'confirm_disqualification.boolean' => 'Konfirmasi diskualifikasi tidak valid.',
        ];
    }
}
