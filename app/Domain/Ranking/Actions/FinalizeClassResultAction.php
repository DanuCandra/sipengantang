<?php

namespace App\Domain\Ranking\Actions;

use App\Domain\Judging\Services\JudgingStateGuard;
use App\Domain\Ranking\Services\RankingInputBuilder;
use App\Domain\Ranking\Services\RankingService;
use App\Domain\Shared\Services\ActivityRecorder;
use App\Enums\ActivityAction;
use App\Enums\CompetitionClassStatus;
use App\Enums\RankingRunType;
use App\Events\ResultFinalized;
use App\Models\CompetitionClass;
use App\Models\RankingRun;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class FinalizeClassResultAction
{
    public function __construct(private RankingInputBuilder $builder, private RankingService $ranking, private JudgingStateGuard $guard, private ActivityRecorder $audit) {}

    public function execute(User $admin, CompetitionClass $competitionClass, ?string $ip = null): RankingRun
    {
        return DB::transaction(function () use ($admin, $competitionClass, $ip): RankingRun {
            $class = CompetitionClass::query()->lockForUpdate()->findOrFail($competitionClass->id);
            Gate::forUser($admin)->authorize('manageJudging', $class);
            if (! $this->guard->isScoringOpen($class) || $class->rankingRuns()->where('type', RankingRunType::Final)->exists()) {
                throw ValidationException::withMessages(['ranking' => 'Kelas tidak dapat difinalisasi atau hasilnya sudah final.']);
            }
            $source = $this->builder->build($class);
            $this->builder->requireComplete($source);
            $calculation = $this->ranking->calculate($source);
            $groups = $this->ranking->tieGroups($calculation);
            $provisional = $class->rankingRuns()->where('type', RankingRunType::Provisional)->where('source_hash', $source['hash'])->latest('id')->first();
            $recorded = $provisional?->tieResolutions()->get()->keyBy('group_key') ?? collect();
            $resolutions = [];
            foreach ($groups as $key => $ids) {
                $resolution = $recorded->get($key);
                if ($resolution === null) {
                    throw ValidationException::withMessages(['ranking' => 'Masih ada draw yang belum diselesaikan. Catat urutan hasil lempar dadu sebelum finalisasi.']);
                }
                $resolutions[$key] = ['ordered_registration_ids' => $resolution->ordered_registration_ids, 'note' => $resolution->note, 'created_by' => $resolution->created_by, 'recorded_at' => $resolution->created_at->toIso8601String(), 'resolution_id' => $resolution->id];
            }
            $run = $this->ranking->persist($class, $source, $calculation, RankingRunType::Final, $admin->id, $resolutions);
            $class->update(['status' => CompetitionClassStatus::Finalized]);
            $this->audit->record($admin, ActivityAction::ResultFinalized, $run, ['competition_class_id' => $class->id, 'source_hash' => $source['hash']], $ip);
            ResultFinalized::dispatch($run->id, $class->id);

            return $run;
        });
    }
}
