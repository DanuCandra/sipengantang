@props(['status'])
<span @class([
    'inline-flex rounded-full px-3 py-1 text-xs font-extrabold',
    'bg-gold-50 text-amber-900' => $status === \App\Enums\PaymentStatus::WaitingVerification,
    'bg-forest-50 text-forest-800' => $status === \App\Enums\PaymentStatus::Verified,
    'bg-red-50 text-red-800' => $status === \App\Enums\PaymentStatus::Rejected,
])>{{ $status->label() }}</span>
