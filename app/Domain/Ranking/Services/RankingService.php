<?php

namespace App\Domain\Ranking\Services;

use App\Enums\RankingRunStatus;
use App\Enums\RankingRunType;
use App\Models\CompetitionClass;
use App\Models\RankingRun;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class RankingService
{
    public function __construct(private TopsisCalculator $calculator) {}

    public function calculate(array $source): array
    {
        try {
            return $this->calculator->calculate($source['alternatives'], $source['criteria']);
        } catch (InvalidArgumentException $exception) {
            throw ValidationException::withMessages(['ranking' => $exception->getMessage()]);
        }
    }

    public function tieGroups(array $calculation): array
    {
        $groups = [];
        foreach ($calculation['results'] as $result) {
            if ($result['is_tied']) {
                $groups[$result['preference_key']][] = $result['registration_id'];
            }
        }
        $keyed = [];
        foreach ($groups as $ids) {
            sort($ids, SORT_NUMERIC);
            $keyed[hash('sha256', json_encode($ids, JSON_THROW_ON_ERROR))] = $ids;
        }

        return $keyed;
    }

    public function persist(CompetitionClass $competitionClass, array $source, array $calculation, RankingRunType $type, ?int $actorId, array $resolutions = []): RankingRun
    {
        $run = $competitionClass->rankingRuns()->create([
            'type' => $type, 'status' => RankingRunStatus::Completed, 'algorithm' => TopsisCalculator::ALGORITHM,
            'algorithm_version' => TopsisCalculator::VERSION, 'source_hash' => $source['hash'],
            'criteria_snapshot' => $source['criteria'], 'input_snapshot' => $source['input'],
            'calculation_snapshot' => [...$calculation, 'draw_resolutions' => $resolutions],
            'calculated_at' => now(), 'finalized_at' => $type === RankingRunType::Final ? now() : null, 'created_by' => $actorId,
        ]);
        $ranks = [];
        foreach ($this->tieGroups($calculation) as $key => $ids) {
            if (isset($resolutions[$key])) {
                $base = min(array_column(array_filter($calculation['results'], fn ($row) => in_array($row['registration_id'], $ids, true)), 'rank'));
                foreach ($resolutions[$key]['ordered_registration_ids'] as $offset => $id) {
                    $ranks[$id] = $base + $offset;
                }
            }
        }
        $run->results()->createMany(array_map(fn ($result) => [
            'registration_id' => $result['registration_id'], 'entry_snapshot' => $source['entries'][$result['registration_id']],
            'criterion_averages' => $result['criterion_averages'],
            'positive_distance' => number_format($result['positive_distance'], 10, '.', ''),
            'negative_distance' => number_format($result['negative_distance'], 10, '.', ''),
            'preference_value' => $result['preference_key'], 'rank' => $ranks[$result['registration_id']] ?? $result['rank'], 'is_tied' => $result['is_tied'],
        ], $calculation['results']));

        return $run;
    }
}
