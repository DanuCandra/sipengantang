<?php

namespace App\Enums;

enum ActivityAction: string
{
    case EventCreated = 'EVENT_CREATED';
    case EventUpdated = 'EVENT_UPDATED';
    case EventPublished = 'EVENT_PUBLISHED';
    case AdminAssigned = 'ADMIN_ASSIGNED';
    case AdminRemoved = 'ADMIN_REMOVED';
    case CompetitionClassCreated = 'COMPETITION_CLASS_CREATED';
    case CompetitionClassUpdated = 'COMPETITION_CLASS_UPDATED';
    case PrizeCreated = 'PRIZE_CREATED';
    case PrizeRemoved = 'PRIZE_REMOVED';
    case ParticipantProfileUpdated = 'PARTICIPANT_PROFILE_UPDATED';
    case BirdCreated = 'BIRD_CREATED';
    case BirdUpdated = 'BIRD_UPDATED';
    case RegistrationCreated = 'REGISTRATION_CREATED';
    case PaymentSubmitted = 'PAYMENT_SUBMITTED';
    case PaymentVerified = 'PAYMENT_VERIFIED';
    case PaymentRejected = 'PAYMENT_REJECTED';
    case JudgeAssigned = 'JUDGE_ASSIGNED';
    case JudgingStarted = 'JUDGING_STARTED';
    case ScoreDraftSaved = 'SCORE_DRAFT_SAVED';
    case ScoreSubmitted = 'SCORE_SUBMITTED';
    case WarningCreated = 'WARNING_CREATED';
    case ParticipantDisqualified = 'PARTICIPANT_DISQUALIFIED';
    case JudgeReplaced = 'JUDGE_REPLACED';
    case DrawResultRecorded = 'DRAW_RESULT_RECORDED';
    case ResultFinalized = 'RESULT_FINALIZED';
    case EventCompleted = 'EVENT_COMPLETED';
    case CertificateGenerated = 'CERTIFICATE_GENERATED';
    case GantanganNumberChosen = 'GANTANGAN_NUMBER_CHOSEN';
}
