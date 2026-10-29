<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Event\Queries\DashboardEventContext;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class WorkspaceController extends Controller
{
    public function __invoke(Request $request, string $section, DashboardEventContext $context): View|RedirectResponse
    {
        $request->validate(['event_id' => ['sometimes', 'integer', 'min:1']]);
        $event = $context->resolve($request)['selected'];
        if ($event) {
            Gate::authorize('view', $event);
            $redirect = match ($section) {
                'overview' => 'admin.events.show', 'participants' => 'admin.events.registrations.index',
                'payments' => 'admin.events.payments.index', 'violations' => 'admin.events.violations.index', 'settings' => 'admin.events.edit', default => null,
            };
            if ($redirect) {
                return redirect()->route($redirect, $event);
            }
        }
        $title = match ($section) {
            'classes' => 'Kelas Lomba', 'judges' => 'Juri', 'judging' => 'Monitor Penjurian', 'results' => 'Hasil & Piagam',
            'prizes' => 'Hadiah', 'participants' => 'Peserta', 'payments' => 'Pembayaran', 'violations' => 'Pelanggaran', 'settings' => 'Pengaturan Event', default => 'Ringkasan Event',
        };
        $classes = $event?->competitionClasses()->withCount(['prizes', 'judgeAssignments' => fn ($query) => $query->active()])->orderBy('start_at')->orderBy('id')->paginate(12)->withQueryString();

        return view('admin.workspace', compact('event', 'section', 'title', 'classes'));
    }
}
