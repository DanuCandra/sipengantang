<?php

namespace App\Http\Requests\Owner;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreEventRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return $this->eventRules();
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    protected function eventRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:5000'],
            'banner' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'location_name' => ['required', 'string', 'max:200'],
            'address' => ['required', 'string', 'max:2000'],
            'registration_open_at' => ['nullable', 'date'],
            'registration_close_at' => ['nullable', 'date', 'after_or_equal:registration_open_at'],
            'start_at' => ['required', 'date'],
            'end_at' => ['nullable', 'date', 'after_or_equal:start_at'],
            'contact_person' => ['nullable', 'string', 'max:150'],
            'contact_phone' => ['nullable', 'string', 'max:30'],
        ];
    }

    public function messages(): array
    {
        return [
            'registration_close_at.after_or_equal' => 'Penutupan pendaftaran tidak boleh lebih awal dari pembukaan.',
            'end_at.after_or_equal' => 'Waktu selesai tidak boleh lebih awal dari waktu mulai.',
        ];
    }
}
