<?php

namespace App\Http\Requests\Participant;

use Illuminate\Foundation\Http\FormRequest;

class SubmitPaymentProofRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('submitPayment', $this->route('registration'));
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'integer', 'min:1', 'max:999999999999'],
            'proof' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'amount.required' => 'Nominal transfer wajib diisi.',
            'amount.integer' => 'Nominal transfer harus berupa Rupiah bulat tanpa titik atau koma.',
            'amount.min' => 'Nominal transfer harus lebih dari nol.',
            'amount.max' => 'Nominal transfer terlalu besar.',
            'proof.required' => 'Bukti pembayaran wajib diunggah.',
            'proof.image' => 'Bukti pembayaran harus berupa gambar.',
            'proof.mimes' => 'Gunakan bukti pembayaran JPG, JPEG, PNG, atau WebP.',
            'proof.max' => 'Ukuran bukti pembayaran maksimal 5 MB.',
        ];
    }
}
