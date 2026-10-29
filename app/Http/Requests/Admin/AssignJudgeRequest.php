<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AssignJudgeRequest extends FormRequest
{
    public function authorize(): bool
    {
        $competitionClass = $this->route('competitionClass');

        return $competitionClass->event_id === $this->route('event')->id && $this->user()->can('manageJudging', $competitionClass);
    }

    public function rules(): array
    {
        return ['email' => ['required', 'email', 'exists:users,email']];
    }

    public function messages(): array
    {
        return ['email.required' => 'Email akun Juri wajib diisi.', 'email.email' => 'Format email Juri tidak valid.', 'email.exists' => 'Akun Juri tidak ditemukan.'];
    }
}
