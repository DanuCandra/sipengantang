<?php

namespace App\Http\Requests\Participant;

use App\Domain\Registration\Services\GantanganNumberService;
use App\Models\Registration;
use Illuminate\Foundation\Http\FormRequest;

class ChooseGantanganNumberRequest extends FormRequest
{
    public function authorize(): bool
    {
        $registration = $this->route('registration');

        return $registration ? $this->user()->can('chooseGantanganNumber', $registration) : $this->user()->can('create', Registration::class);
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('gantangan_number')) {
            $this->merge(['gantangan_number' => GantanganNumberService::normalize($this->input('gantangan_number'))]);
        }
    }

    public function rules(): array
    {
        return ['gantangan_number' => GantanganNumberService::RULES];
    }

    public function messages(): array
    {
        return GantanganNumberService::MESSAGES;
    }
}
