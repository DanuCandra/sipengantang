<?php

namespace App\Policies;

use App\Domain\Certificate\Services\CertificateEligibility;
use App\Models\Certificate;
use App\Models\User;

class CertificatePolicy
{
    public function __construct(private CertificateEligibility $eligibility) {}

    public function view(User $user, Certificate $certificate): bool
    {
        if (! $this->eligibility->query()->whereKey($certificate->ranking_result_id)
            ->where('registration_id', $certificate->registration_id)
            ->where('rank', $certificate->rank)
            ->whereHas('rankingRun', fn ($query) => $query->where('competition_class_id', $certificate->competition_class_id)
                ->whereHas('competitionClass', fn ($class) => $class->where('event_id', $certificate->event_id)))
            ->exists()) {
            return false;
        }

        $certificate->loadMissing(['registration:id,participant_id', 'event']);

        return $certificate->registration->participant_id === $user->id || $user->can('view', $certificate->event);
    }
}
