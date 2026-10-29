@props(['results', 'criteria' => [], 'internal' => false, 'final' => false])
<div class="space-y-4">
    @forelse ($results as $result)
        <article class="min-w-0 rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
            <div class="flex flex-wrap items-center gap-4">
                <span @class(['grid size-12 shrink-0 place-items-center rounded-xl text-xl font-extrabold', 'bg-gold-100 text-amber-900' => $result->rank <= 3, 'bg-stone-100 text-stone-700' => $result->rank > 3])>{{ $result->rank }}</span>
                <div class="min-w-0 flex-1"><h3 class="break-words text-lg font-extrabold">{{ $result->entry_snapshot['bird_name'] }}</h3><p class="mt-1 break-words text-sm text-stone-500">{{ $result->entry_snapshot['participant_name'] }} · Gantangan {{ $result->entry_snapshot['gantangan_number'] ?? 'belum ditentukan' }}</p></div>
                <div><p class="text-xs text-stone-500">Nilai TOPSIS</p><p class="mt-1 text-xl font-extrabold text-forest-800">{{ number_format((float) $result->preference_value, 4, ',', '.') }}</p></div>
            </div>
            @if ($result->is_tied)<p class="mt-4 rounded-lg bg-gold-50 p-3 text-xs font-bold text-amber-900">{{ $final ? 'Draw TOPSIS · Urutan ditentukan melalui lempar dadu di lokasi lomba.' : 'TIE / Draw · Angka urutan bersama ini belum menentukan pemenang.' }}</p>@endif
            @if ($internal)
                <details class="mt-4 rounded-xl bg-stone-50 p-4"><summary class="min-h-11 cursor-pointer py-2 text-sm font-bold text-forest-800">Detail perhitungan tersimpan</summary><dl class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($criteria as $criterion)<div><dt class="text-xs text-stone-500">{{ $criterion['name'] }}</dt><dd class="mt-1 font-extrabold">{{ number_format($result->criterion_averages[$criterion['code']], 4, ',', '.') }}</dd></div>@endforeach
                    <div><dt class="text-xs text-stone-500">Jarak ideal positif</dt><dd class="mt-1 font-bold">{{ $result->positive_distance }}</dd></div><div><dt class="text-xs text-stone-500">Jarak ideal negatif</dt><dd class="mt-1 font-bold">{{ $result->negative_distance }}</dd></div>
                </dl></details>
            @endif
        </article>
    @empty
        <div class="rounded-2xl border border-dashed border-stone-300 bg-white p-8 text-center"><h2 class="font-extrabold">Belum ada peserta yang memenuhi input ranking</h2><p class="mt-2 text-sm leading-6 text-stone-500">Hanya peserta terverifikasi dengan evaluasi submitted lengkap yang dihitung. Peserta DQ tidak disertakan.</p></div>
    @endforelse
</div>
