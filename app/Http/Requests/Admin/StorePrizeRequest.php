<?php

namespace App\Http\Requests\Admin;

use App\Enums\PrizeType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePrizeRequest extends FormRequest
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
        return [
            'rank' => [
                'required',
                'integer',
                'min:1',
                'max:1000',
                Rule::unique('prizes', 'rank')->where('competition_class_id', $this->route('competitionClass')?->id),
            ],
            'type' => ['required', Rule::enum(PrizeType::class)],
            'title' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:2000'],
            'cash_amount' => ['nullable', 'required_if:type,CASH', 'integer', 'min:0'],
        ];
    }
}
