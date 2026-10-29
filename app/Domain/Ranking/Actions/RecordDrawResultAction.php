<?php

namespace App\Domain\Ranking\Actions;

use App\Domain\Judging\Services\JudgingStateGuard;
use App\Domain\Ranking\Services\RankingInputBuilder;
use App\Domain\Ranking\Services\RankingService;
use App\Domain\Shared\Services\ActivityRecorder;
use App\Enums\ActivityAction;
use App\Enums\RankingRunType;
use App\Models\CompetitionClass;
use App\Models\RankingRun;
use App\Models\RankingTieResolution;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class RecordDrawResultAction
{
    public function __construct(private RankingInputBuilder $builder, private RankingService $ranking, private JudgingStateGuard $guard, private ActivityRecorder $audit) {}

    public function execute(User $admin, CompetitionClass $competitionClass, RankingRun $run, string $groupKey, array $orderedIds, string $note, ?string $ip = null): RankingTieResolution
    {
        return DB::transaction(function () use ($admin, $competitionClass, $run, $groupKey, $orderedIds, $note, $ip): RankingTieResolution {
            $class = CompetitionClass::query()->lockForUpdate()->findOrFail($competitionClass->id);
            Gate::forUser($admin)->authorize('manageJudging', $class);
            abort_unless($run->competition_class_id === $class->id, 404);
            if (! $this->guard->isScoringOpen($class) || $run->type !== RankingRunType::Provisional) {
                throw ValidationException::withMessages(['draw' => 'Draw hanya dapat dicatat sebelum finalisasi pada penjurian yang terbuka.']);
            }
            $source = $this->builder->build($class);
            $this->builder->requireComplete($source);
            if ($run->source_hash !== $source['hash']) {
                throw ValidationException::withMessages(['draw' => 'Nilai sumber berubah. Hitung ulang ranking sebelum mencatat draw.']);
            }
            $calculation = $this->ranking->calculate($source);
            $group = $this->ranking->tieGroups($calculation)[$groupKey] ?? [];
            $orderedIds = array_map('intval', $orderedIds);
            $sorted = $orderedIds;
            sort($sorted, SORT_NUMERIC);
            if ($group === [] || $sorted !== $group || count(array_unique($orderedIds)) !== count($orderedIds)) {
                throw ValidationException::withMessages(['draw' => 'Urutan harus memuat tepat seluruh peserta dalam kelompok draw ini, masing-masing satu kali.']);
            }
            Validator::make(['note' => trim($note)], ['note' => ['required', 'string', 'max:2000']], ['note.required' => 'Catat hasil lempar dadu dan kejadian undian.', 'note.max' => 'Catatan maksimal 2.000 karakter.'])->validate();
            if ($run->tieResolutions()->where('group_key', $groupKey)->exists()) {
                throw ValidationException::withMessages(['draw' => 'Hasil draw sudah dicatat dan tidak dapat ditimpa.']);
            }
            $resolution = $run->tieResolutions()->create(['group_key' => $groupKey, 'ordered_registration_ids' => $orderedIds, 'note' => trim($note), 'created_by' => $admin->id]);
            $this->audit->record($admin, ActivityAction::DrawResultRecorded, $resolution, ['ranking_run_id' => $run->id, 'competition_class_id' => $class->id], $ip);

            return $resolution;
        });
    }
}
