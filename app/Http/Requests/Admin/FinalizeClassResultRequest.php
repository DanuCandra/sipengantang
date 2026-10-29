<?php

namespace App\Http\Requests\Admin;

class FinalizeClassResultRequest extends RankingOperationRequest
{
    public function rules(): array
    {
        return ['confirmation' => ['required', 'in:FINALISASI']];
    }

    public function messages(): array
    {
        return ['confirmation.required' => 'Ketik FINALISASI untuk mengonfirmasi.', 'confirmation.in' => 'Konfirmasi harus tepat FINALISASI.'];
    }
}
