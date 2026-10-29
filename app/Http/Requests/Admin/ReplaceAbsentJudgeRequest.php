<?php

namespace App\Http\Requests\Admin;

class ReplaceAbsentJudgeRequest extends RankingOperationRequest
{
    public function rules(): array
    {
        return ['replacement_email' => ['required', 'email', 'exists:users,email'], 'replacement_note' => ['required', 'string', 'max:2000']];
    }

    public function messages(): array
    {
        return ['replacement_email.exists' => 'Akun pengganti tidak ditemukan.', 'replacement_note.required' => 'Alasan penggantian Juri wajib diisi.'];
    }
}
