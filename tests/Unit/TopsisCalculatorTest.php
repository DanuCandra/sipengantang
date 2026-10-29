<?php

namespace Tests\Unit;

use App\Domain\Ranking\Services\TopsisCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class TopsisCalculatorTest extends TestCase
{
    private function criteria(): array
    {
        return [['code' => 'C1', 'weight' => '0.600000', 'type' => 'BENEFIT'], ['code' => 'C2', 'weight' => '0.400000', 'type' => 'BENEFIT']];
    }

    public function test_hand_calculated_fixture_covers_every_matrix_ideal_distance_and_rank(): void
    {
        $result = (new TopsisCalculator)->calculate([10 => ['C1' => 3, 'C2' => 4], 20 => ['C1' => 4, 'C2' => 3]], $this->criteria());

        // Each column has norm 5; weighted ideals are (.48,.32) and (.36,.24).
        $this->assertSame(['C1' => 3.0, 'C2' => 4.0], $result['matrix'][10]);
        $this->assertEqualsWithDelta(0.6, $result['normalized_matrix'][10]['C1'], 1e-12);
        $this->assertEqualsWithDelta(0.8, $result['normalized_matrix'][10]['C2'], 1e-12);
        $this->assertEqualsWithDelta(0.36, $result['weighted_matrix'][10]['C1'], 1e-12);
        $this->assertEqualsWithDelta(0.32, $result['positive_ideal']['C2'], 1e-12);
        $this->assertEqualsWithDelta(0.48, $result['positive_ideal']['C1'], 1e-12);
        $this->assertEqualsWithDelta(0.36, $result['negative_ideal']['C1'], 1e-12);
        $this->assertEqualsWithDelta(0.24, $result['negative_ideal']['C2'], 1e-12);
        $this->assertSame([20, 10], array_column($result['results'], 'registration_id'));
        $winner = $result['results'][0];
        $this->assertEqualsWithDelta(0.08, $winner['positive_distance'], 1e-12);
        $this->assertEqualsWithDelta(0.12, $winner['negative_distance'], 1e-12);
        $this->assertSame('0.6000000000', $winner['preference_key']);
        $this->assertSame([1, 2], array_column($result['results'], 'rank'));
        $this->assertSame([false, false], array_column($result['results'], 'is_tied'));
    }

    public function test_single_entry_has_first_rank_and_no_artificial_win_coefficient(): void
    {
        $row = (new TopsisCalculator)->calculate([10 => ['C1' => 8, 'C2' => 8]], $this->criteria())['results'][0];
        $this->assertSame(1, $row['rank']);
        $this->assertFalse($row['is_tied']);
        $this->assertSame(0.5, $row['preference_value']);
        $this->assertSame(0.0, $row['positive_distance']);
    }

    public function test_identical_alternatives_share_rank_and_are_marked_draw(): void
    {
        $result = (new TopsisCalculator)->calculate([20 => ['C1' => 8, 'C2' => 8], 10 => ['C1' => 8, 'C2' => 8]], $this->criteria());
        $this->assertSame([1, 1], array_column($result['results'], 'rank'));
        $this->assertSame([true, true], array_column($result['results'], 'is_tied'));
        $this->assertSame(['0.5000000000', '0.5000000000'], array_column($result['results'], 'preference_key'));
        $this->assertSame([10, 20], array_column($result['results'], 'registration_id'));
    }

    public function test_input_order_does_not_change_calculation_or_tie_display(): void
    {
        $calculator = new TopsisCalculator;
        $a = [10 => ['C1' => 8, 'C2' => 9], 20 => ['C1' => 7, 'C2' => 10], 30 => ['C1' => 6, 'C2' => 8]];
        $this->assertSame($calculator->calculate($a, $this->criteria()), $calculator->calculate(array_reverse($a, true), $this->criteria()));
    }

    #[DataProvider('invalidInputs')]
    public function test_invalid_weights_types_and_matrix_are_rejected(array $alternatives, array $criteria): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new TopsisCalculator)->calculate($alternatives, $criteria);
    }

    public static function invalidInputs(): array
    {
        $valid = [['code' => 'C1', 'weight' => 1.0, 'type' => 'BENEFIT']];

        return [
            'no criteria' => [[], []],
            'weights not one' => [[], [['code' => 'C1', 'weight' => .4, 'type' => 'BENEFIT']]],
            'negative' => [[], [['code' => 'C1', 'weight' => -1, 'type' => 'BENEFIT']]],
            'infinite' => [[], [['code' => 'C1', 'weight' => INF, 'type' => 'BENEFIT']]],
            'cost not MVP' => [[], [['code' => 'C1', 'weight' => 1, 'type' => 'COST']]],
            'duplicate' => [[], [$valid[0], $valid[0]]],
            'missing value' => [[10 => []], $valid],
            'too low' => [[10 => ['C1' => 0]], $valid],
            'too high' => [[10 => ['C1' => 11]], $valid],
            'nan' => [[10 => ['C1' => NAN]], $valid],
        ];
    }

    public function test_no_eligible_alternatives_returns_no_winner(): void
    {
        $result = (new TopsisCalculator)->calculate([], $this->criteria());
        $this->assertSame([], $result['results']);
    }
}
