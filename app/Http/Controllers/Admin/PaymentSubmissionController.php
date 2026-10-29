<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Payment\Actions\ReviewPaymentAction;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReviewPaymentRequest;
use App\Models\Event;
use App\Models\PaymentSubmission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PaymentSubmissionController extends Controller
{
    public function index(Request $request, Event $event): View
    {
        Gate::authorize('viewAny', [PaymentSubmission::class, $event]);
        $validated = $request->validate(['status' => ['nullable', Rule::enum(PaymentStatus::class)]]);
        $status = $validated['status'] ?? PaymentStatus::WaitingVerification->value;
        $payments = PaymentSubmission::query()
            ->whereHas('registration.competitionClass', fn ($query) => $query->where('event_id', $event->id))
            ->where('status', $status)
            ->with(['registration.participant:id,name', 'registration.bird:id,name', 'registration.competitionClass:id,name,event_id'])
            ->oldest('uploaded_at')->oldest('id')->paginate(15)->withQueryString();

        return view('admin.payments.index', compact('event', 'payments', 'status'));
    }

    public function show(Event $event, PaymentSubmission $paymentSubmission): View
    {
        Gate::authorize('viewAny', [PaymentSubmission::class, $event]);
        $paymentSubmission->load(['registration.participant', 'registration.bird', 'registration.competitionClass.event', 'reviewer']);
        abort_unless($paymentSubmission->registration->competitionClass->event_id === $event->id, 404);
        Gate::authorize('review', $paymentSubmission);

        return view('admin.payments.show', ['event' => $event, 'payment' => $paymentSubmission]);
    }

    public function update(ReviewPaymentRequest $request, Event $event, PaymentSubmission $paymentSubmission, ReviewPaymentAction $action): RedirectResponse
    {
        $action->execute($request->user(), $paymentSubmission, PaymentStatus::from($request->validated('decision')), $request->validated('rejection_reason'), $request->ip());

        return redirect()->route('admin.events.payments.show', [$event, $paymentSubmission])->with('success', 'Keputusan pembayaran berhasil disimpan.');
    }
}
