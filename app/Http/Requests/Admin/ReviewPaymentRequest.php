<?php

namespace App\Http\Requests\Admin;

use App\Enums\PaymentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReviewPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $payment = $this->route('paymentSubmission');
        $payment->loadMissing('registration.competitionClass');

        return $payment->registration->competitionClass->event_id === $this->route('event')->id
            && $this->user()->can('review', $payment);
    }

    public function rules(): array
    {
        return [
            'decision' => ['required', Rule::in([PaymentStatus::Verified->value, PaymentStatus::Rejected->value])],
            'rejection_reason' => [Rule::requiredIf($this->input('decision') === PaymentStatus::Rejected->value), 'nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'decision.required' => 'Pilih verifikasi atau tolak pembayaran.',
            'decision.in' => 'Keputusan pembayaran tidak valid.',
            'rejection_reason.required' => 'Alasan penolakan wajib diisi.',
            'rejection_reason.string' => 'Alasan penolakan harus berupa teks.',
            'rejection_reason.max' => 'Alasan penolakan maksimal 2.000 karakter.',
        ];
    }
}
