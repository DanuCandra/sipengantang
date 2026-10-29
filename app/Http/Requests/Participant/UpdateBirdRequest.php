<?php

namespace App\Http\Requests\Participant;

class UpdateBirdRequest extends StoreBirdRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function rules(): array
    {
        return $this->birdRules(requiredPhoto: false);
    }
}
