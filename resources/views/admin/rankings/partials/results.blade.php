@if ($results)
    <x-ranking-results :results="$results" :criteria="$run->criteria_snapshot" :internal="true" :final="$run->type === \App\Enums\RankingRunType::Final" />
    <div class="mt-5">{{ $results->links() }}</div>
@else
    <p class="rounded-2xl border border-dashed border-stone-300 bg-white p-8 text-center text-sm text-stone-500">Belum ada perhitungan ranking. Pilih Hitung ulang atau tunggu evaluasi submitted diproses oleh queue.</p>
@endif
