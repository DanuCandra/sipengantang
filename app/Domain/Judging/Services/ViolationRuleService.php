<?php

namespace App\Domain\Judging\Services;

use App\Enums\ViolationAction;
use App\Enums\ViolationReasonCode;
use Illuminate\Validation\ValidationException;

class ViolationRuleService
{
    /** @return list<ViolationReasonCode> */
    public function reasonsFor(ViolationAction $intent): array
    {
        return match ($intent) {
            ViolationAction::Warning => [ViolationReasonCode::Shouting, ViolationReasonCode::Provocation, ViolationReasonCode::Disturbing, ViolationReasonCode::Other],
            ViolationAction::Disqualification => [ViolationReasonCode::LateHanging, ViolationReasonCode::NumberManipulation, ViolationReasonCode::Other],
        };
    }

    public function determineAction(ViolationAction $intent, ViolationReasonCode $reasonCode, bool $hasWarning): ViolationAction
    {
        if (! in_array($reasonCode, $this->reasonsFor($intent), true)) {
            throw ValidationException::withMessages(['reason_code' => 'Alasan tidak sesuai dengan tindakan yang dipilih.']);
        }

        return $intent === ViolationAction::Warning && ! $hasWarning ? ViolationAction::Warning : ViolationAction::Disqualification;
    }
}
