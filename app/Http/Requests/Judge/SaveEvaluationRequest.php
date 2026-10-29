<?php

namespace App\Http\Requests\Judge;

use App\Models\JudgeEvaluation;
use Illuminate\Foundation\Http\FormRequest;

class SaveEvaluationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $registration = $this->route('registration');

        return $registration->competition_class_id === $this->route('competitionClass')->id
            && $this->user()->can('score', [JudgeEvaluation::class, $registration]);
    }

    public function rules(): array
    {
        return ['scores' => ['sometimes', 'array']];
    }

    public function messages(): array
    {
        return ['scores.array' => 'Format nilai tidak valid.'];
    }
}
