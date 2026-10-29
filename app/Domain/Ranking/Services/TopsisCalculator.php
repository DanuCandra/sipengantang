<?php

namespace App\Domain\Ranking\Services;

use InvalidArgumentException;

class TopsisCalculator
{
    public const ALGORITHM = 'TOPSIS';

    public const VERSION = '1.0.0';

    /**
     * @param  array<int, array<string, float|int>>  $alternatives
     * @param  list<array{code: string, weight: float|string, type: string}>  $criteria
     */
    public function calculate(array $alternatives, array $criteria): array
    {
        if ($criteria === [] || count(array_unique(array_column($criteria, 'code'))) !== count($criteria)) {
            throw new InvalidArgumentException('Kriteria wajib tersedia dan kode harus unik.');
        }
        $totalWeight = 0.0;
        foreach ($criteria as $criterion) {
            $weight = (float) $criterion['weight'];
            if (! is_numeric($criterion['weight']) || ! is_finite($weight) || $weight <= 0 || $criterion['type'] !== 'BENEFIT') {
                throw new InvalidArgumentException('Bobot harus positif dan kriteria MVP harus BENEFIT.');
            }
            $totalWeight += $weight;
        }
        if (abs($totalWeight - 1.0) > 0.0000005) {
            throw new InvalidArgumentException('Jumlah bobot kriteria harus 1.');
        }
        ksort($alternatives, SORT_NUMERIC);
        $matrix = $normalized = $weighted = $positive = $negative = $divisors = $results = [];
        foreach ($alternatives as $id => $values) {
            if (count($values) !== count($criteria)) {
                throw new InvalidArgumentException('Matriks kriteria tidak lengkap.');
            }
            foreach ($criteria as $criterion) {
                $code = $criterion['code'];
                $value = $values[$code] ?? null;
                if (! is_numeric($value) || ! is_finite((float) $value) || $value < 1 || $value > 10) {
                    throw new InvalidArgumentException('Rata-rata kriteria harus berada pada rentang 1–10.');
                }
                $matrix[$id][$code] = (float) $value;
            }
        }
        foreach ($criteria as $criterion) {
            $code = $criterion['code'];
            $divisors[$code] = sqrt(array_sum(array_map(fn ($row) => $row[$code] ** 2, $matrix)));
            foreach ($matrix as $id => $values) {
                $normalized[$id][$code] = $values[$code] / $divisors[$code];
                $weighted[$id][$code] = $normalized[$id][$code] * (float) $criterion['weight'];
            }
            $column = array_column($weighted, $code);
            $positive[$code] = $column === [] ? 0.0 : max($column);
            $negative[$code] = $column === [] ? 0.0 : min($column);
        }
        foreach ($weighted as $id => $values) {
            $plus = $minus = 0.0;
            foreach ($values as $code => $value) {
                $plus += ($value - $positive[$code]) ** 2;
                $minus += ($value - $negative[$code]) ** 2;
            }
            $plus = sqrt($plus);
            $minus = sqrt($minus);
            $preference = $plus + $minus <= PHP_FLOAT_EPSILON ? 0.5 : $minus / ($plus + $minus);
            $results[$id] = ['registration_id' => $id, 'criterion_averages' => $matrix[$id], 'positive_distance' => $plus, 'negative_distance' => $minus, 'preference_value' => $preference, 'preference_key' => number_format($preference, 10, '.', '')];
        }
        // Compare at authoritative storage precision; ID only stabilizes display within a draw.
        uasort($results, fn ($a, $b) => ($b['preference_key'] <=> $a['preference_key']) ?: ($a['registration_id'] <=> $b['registration_id']));
        $groups = [];
        foreach ($results as $id => $result) {
            $groups[$result['preference_key']][] = $id;
        }
        $position = 1;
        foreach ($groups as $ids) {
            foreach ($ids as $id) {
                $results[$id]['rank'] = $position;
                $results[$id]['is_tied'] = count($ids) > 1;
            }
            $position += count($ids);
        }

        return ['matrix' => $matrix, 'normalized_matrix' => $normalized, 'weighted_matrix' => $weighted, 'positive_ideal' => $positive, 'negative_ideal' => $negative, 'results' => array_values($results)];
    }
}
