<?php

namespace App\Http\Requests\Participant;

use Illuminate\Contracts\Validation\ValidationRule;

class StoreRegistrationRequest extends ChooseGantanganNumberRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return parent::authorize();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'competition_class_id' => ['required', 'integer', 'exists:competition_classes,id'],
            'bird_id' => ['required', 'integer', 'exists:birds,id'],
        ];
    }
}
