<?php

namespace App\Http\Requests\Admin;

class RecordDrawResultRequest extends RankingOperationRequest
{
    public function rules(): array
    {
        return ['group_key' => ['required', 'string', 'size:64'], 'ordered_registration_ids' => ['required', 'array', 'min:2'],
            'ordered_registration_ids.*' => ['required', 'integer', 'distinct'], 'note' => ['required', 'string', 'max:2000'], 'confirmation' => ['required', 'accepted']];
    }

    public function messages(): array
    {
        return ['ordered_registration_ids.*.distinct' => 'Peserta tidak boleh dipilih dua kali.', 'note.required' => 'Catat hasil lempar dadu dan kejadian undian.', 'confirmation.accepted' => 'Konfirmasi hasil draw wajib dicentang.', 'confirmation.required' => 'Konfirmasi hasil draw wajib dicentang.'];
    }
}
