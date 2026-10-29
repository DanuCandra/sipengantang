<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class RankingOperationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $class = $this->route('competitionClass');

        return $class->event_id === $this->route('event')->id && $this->user()->can('manageJudging', $class);
    }

    public function rules(): array
    {
        return [];
    }
}
