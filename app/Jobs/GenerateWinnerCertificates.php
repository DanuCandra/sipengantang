<?php

namespace App\Jobs;

use App\Domain\Certificate\Actions\GenerateCertificateAction;
use App\Domain\Certificate\Services\CertificateEligibility;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GenerateWinnerCertificates implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 60;

    public int $uniqueFor = 120;

    public function __construct(public int $rankingRunId)
    {
        $this->onConnection('database')->afterCommit();
    }

    public function uniqueId(): string
    {
        return (string) $this->rankingRunId;
    }

    public function backoff(): array
    {
        return [10, 30];
    }

    public function handle(CertificateEligibility $eligibility, GenerateCertificateAction $action): void
    {
        $eligibility->query()->where('ranking_run_id', $this->rankingRunId)->eachById(fn ($result) => $action->execute($result));
    }
}
