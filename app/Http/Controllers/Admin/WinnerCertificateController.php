<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RankingRunType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RankingOperationRequest;
use App\Jobs\GenerateWinnerCertificates;
use App\Models\CompetitionClass;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;

class WinnerCertificateController extends Controller
{
    public function __invoke(RankingOperationRequest $request, Event $event, CompetitionClass $competitionClass): RedirectResponse
    {
        $run = $competitionClass->rankingRuns()->where('type', RankingRunType::Final)->firstOrFail();
        GenerateWinnerCertificates::dispatch($run->id);

        return back()->with('success', 'Pembuatan/pemulihan piagam pemenang telah diantrekan. Piagam yang sudah tersedia tidak ditimpa.');
    }
}
