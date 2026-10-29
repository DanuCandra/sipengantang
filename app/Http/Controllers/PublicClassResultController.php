<?php

namespace App\Http\Controllers;

use App\Enums\CompetitionClassStatus;
use App\Enums\EventStatus;
use App\Enums\RankingRunStatus;
use App\Enums\RankingRunType;
use App\Models\CompetitionClass;
use App\Models\Event;
use Illuminate\View\View;

class PublicClassResultController extends Controller
{
    public function __invoke(Event $event, CompetitionClass $competitionClass): View
    {
        abort_unless($competitionClass->event_id === $event->id && $event->published_at !== null
            && in_array($event->status, [EventStatus::Ongoing, EventStatus::Completed], true)
            && $competitionClass->status === CompetitionClassStatus::Finalized, 404);
        $run = $competitionClass->rankingRuns()->where('type', RankingRunType::Final)->where('status', RankingRunStatus::Completed)->latest('id')->firstOrFail();
        // Only this explicit public projection reaches the view; no raw scores/draw notes.
        $info = ['class_name' => $run->input_snapshot['class']['name'], 'event_name' => $run->input_snapshot['class']['event_name'],
            'prizes' => $run->input_snapshot['prizes'], 'finalized_at' => $run->finalized_at];
        $results = $run->results()->select('id', 'registration_id', 'entry_snapshot', 'rank', 'preference_value', 'is_tied')->orderBy('rank')->orderBy('registration_id')->paginate(15);

        return view('events.results', compact('event', 'competitionClass', 'info', 'results'));
    }
}
