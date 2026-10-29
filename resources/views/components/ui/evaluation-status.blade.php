@props(['status' => null])
<span @class(['inline-flex rounded-full px-3 py-1 text-xs font-extrabold', 'bg-forest-50 text-forest-800' => $status === \App\Enums\EvaluationStatus::Submitted, 'bg-gold-50 text-amber-900' => $status === \App\Enums\EvaluationStatus::Draft, 'bg-stone-100 text-stone-700' => $status === null])>{{ $status?->label() ?? 'Belum dinilai' }}</span>
