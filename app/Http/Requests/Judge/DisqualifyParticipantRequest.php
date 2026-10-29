<?php

namespace App\Http\Requests\Judge;

class DisqualifyParticipantRequest extends StoreViolationRequest
{
    public function rules(): array
    {
        return [...parent::rules(), 'note' => ['required', 'string', 'max:2000'], 'confirm_disqualification' => ['required', 'accepted']];
    }

    public function messages(): array
    {
        return [...parent::messages(), 'note.required' => 'Catatan kejadian wajib diisi untuk diskualifikasi, termasuk eskalasi peringatan.', 'confirm_disqualification.required' => 'Konfirmasi diskualifikasi wajib dicentang.', 'confirm_disqualification.accepted' => 'Konfirmasi diskualifikasi wajib dicentang.'];
    }
}
