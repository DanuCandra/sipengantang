<?php

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rules\Password;

class StoreJudgeAccountRequest extends AssignJudgeRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ];
    }

    public function messages(): array
    {
        return [...parent::messages(), 'name.required' => 'Nama Juri wajib diisi.', 'email.unique' => 'Email sudah digunakan. Pilih akun Juri yang sudah ada.', 'password.required' => 'Password wajib diisi.', 'password.min' => 'Password minimal 8 karakter.', 'password.confirmed' => 'Konfirmasi password tidak sesuai.'];
    }
}
