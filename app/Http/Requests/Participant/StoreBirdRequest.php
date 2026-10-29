<?php

namespace App\Http\Requests\Participant;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreBirdRequest extends FormRequest
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
        return $this->birdRules(requiredPhoto: true);
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    protected function birdRules(bool $requiredPhoto): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'bird_type' => ['required', 'string', 'max:120'],
            'photo' => [$requiredPhoto ? 'required' : 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'description' => ['nullable', 'string', 'max:3000'],
        ];
    }
}
