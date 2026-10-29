<?php

namespace Tests\Concurrency;

use App\Enums\ActivityAction;
use App\Enums\CompetitionClassStatus;
use App\Enums\PaymentStatus;
use App\Enums\RegistrationStatus;
use App\Enums\ViolationAction;
use App\Models\ActivityLog;
use App\Models\Bird;
use App\Models\CompetitionClass;
use App\Models\Event;
use App\Models\EventAdmin;
use App\Models\PaymentSubmission;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;
use Tests\Concerns\CreatesRankingFixtures;
use Tests\TestCase;

class CompetitionConcurrencyTest extends TestCase
{
    use CreatesRankingFixtures, DatabaseMigrations;

    /**
     * Both independent PHP/MySQL processes must actually wait on the class lock
     * before release. This tests contention, not two sequential calls in one transaction.
     */
    private function race(int $classId, string $operation, array $inputs): array
    {
        $connection = config('database.connections.mysql');
        $environment = [
            'APP_ENV' => 'testing', 'DB_CONNECTION' => 'mysql', 'DB_DATABASE' => 'sipengantang_test', 'DB_URL' => '',
            'DB_HOST' => $connection['host'], 'DB_PORT' => (string) $connection['port'], 'DB_USERNAME' => $connection['username'], 'DB_PASSWORD' => $connection['password'],
            'BROADCAST_CONNECTION' => 'null', 'QUEUE_CONNECTION' => 'database', 'CACHE_STORE' => 'array', 'BCRYPT_ROUNDS' => '4',
        ];
        $workers = [];
        DB::beginTransaction();
        try {
            CompetitionClass::query()->whereKey($classId)->lockForUpdate()->firstOrFail();
            $ids = [];
            foreach ($inputs as $input) {
                $process = new Process([PHP_BINARY, base_path('tests/Support/concurrent-operation.php'), $operation, json_encode($input, JSON_THROW_ON_ERROR)], base_path(), $environment, null, 25);
                $workers[] = [$process];
                $process->start();
                $ready = $process->waitUntil(fn () => str_contains($process->getOutput(), 'READY:'));
                $this->assertTrue($ready, 'Child did not reach test barrier: '.$process->getOutput().$process->getErrorOutput());
                preg_match('/READY:(\d+)/', $process->getOutput(), $matches);
                $ids[] = (int) $matches[1];
            }
            $deadline = microtime(true) + 10;
            do {
                foreach ($workers as [$process]) {
                    $process->getOutput();
                }
                // Bounded integration synchronization: real lock waits cannot be faked.
                $waiting = DB::table('information_schema.innodb_trx')->whereIn('trx_mysql_thread_id', $ids)->where('trx_state', 'LOCK WAIT')->count();
                if ($waiting === count($workers)) {
                    break;
                }
                usleep(25000);
            } while (microtime(true) < $deadline);
            $diagnostic = implode(' | ', array_map(fn ($worker) => $worker[0]->getOutput().$worker[0]->getErrorOutput(), $workers));
            $this->assertSame(count($workers), $waiting, 'Both operations must contend on the class row. '.$diagnostic);
            DB::commit();

            $outcomes = [];
            foreach ($workers as [$process]) {
                $process->wait();
                $this->assertSame(0, $process->getExitCode(), $process->getOutput().$process->getErrorOutput());
                preg_match('/RESULT:(accepted|rejected)/', $process->getOutput(), $matches);
                $outcomes[] = $matches[1] ?? 'missing';
            }
            sort($outcomes);

            return $outcomes;
        } finally {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            foreach ($workers as [$process]) {
                if ($process->isRunning()) {
                    $process->stop();
                }
            }
        }
    }

    public function test_two_participants_cannot_take_the_same_last_quota_slot(): void
    {
        $event = Event::factory()->publishedUpcoming()->create();
        $class = CompetitionClass::factory()->for($event)->create(['quota' => 1, 'bird_type' => 'Murai Batu']);
        $inputs = [];
        foreach (range(1, 2) as $unused) {
            $participant = User::factory()->participant()->create();
            $bird = Bird::factory()->for($participant, 'owner')->create(['bird_type' => 'Murai Batu']);
            $inputs[] = ['actor' => $participant->id, 'class' => $class->id, 'bird' => $bird->id];
        }

        $this->assertSame(['accepted', 'rejected'], $this->race($class->id, 'register', $inputs));
        $this->assertDatabaseCount('registrations', 1);
        $this->assertSame(1, ActivityLog::query()->where('action', ActivityAction::RegistrationCreated)->count());
    }

    public function test_conflicting_payment_reviews_preserve_one_decision_and_one_audit(): void
    {
        $registration = Registration::factory()->create(['status' => RegistrationStatus::WaitingVerification]);
        $class = $registration->competitionClass;
        $payment = PaymentSubmission::factory()->for($registration)->create();
        $admin = User::factory()->admin()->create();
        EventAdmin::factory()->create(['event_id' => $class->event_id, 'user_id' => $admin->id]);
        $base = ['actor' => $admin->id, 'payment' => $payment->id];

        $this->assertSame(['accepted', 'rejected'], $this->race($class->id, 'review', [[...$base, 'decision' => 'VERIFIED'], [...$base, 'decision' => 'REJECTED']]));
        $payment->refresh();
        $this->assertContains($payment->status, [PaymentStatus::Verified, PaymentStatus::Rejected]);
        $this->assertSame($payment->status === PaymentStatus::Verified ? RegistrationStatus::Verified : RegistrationStatus::PaymentRejected, $registration->fresh()->status);
        $this->assertSame(1, ActivityLog::query()->whereIn('action', [ActivityAction::PaymentVerified, ActivityAction::PaymentRejected])->count());
        $this->assertDatabaseCount('payment_submissions', 1);
    }

    public function test_two_participants_cannot_reserve_the_same_gantangan_number_even_with_quota_available(): void
    {
        $event = Event::factory()->publishedUpcoming()->create();
        $class = CompetitionClass::factory()->for($event)->create(['quota' => 10, 'bird_type' => 'Murai Batu']);
        $inputs = [];
        foreach (range(1, 2) as $unused) {
            $participant = User::factory()->participant()->create();
            $bird = Bird::factory()->for($participant, 'owner')->create(['bird_type' => 'Murai Batu']);
            $inputs[] = ['actor' => $participant->id, 'class' => $class->id, 'bird' => $bird->id, 'gantangan_number' => '17'];
        }

        $this->assertSame(['accepted', 'rejected'], $this->race($class->id, 'register', $inputs));
        $this->assertDatabaseCount('registrations', 1);
        $this->assertSame('17', Registration::query()->sole()->gantangan_number);
        $this->assertSame(1, ActivityLog::query()->where('action', ActivityAction::RegistrationCreated)->count());
    }

    public function test_existing_participants_cannot_claim_the_same_empty_number_concurrently(): void
    {
        $event = Event::factory()->publishedUpcoming()->create();
        $class = CompetitionClass::factory()->for($event)->create();
        $registrations = Registration::factory()->for($class)->count(2)->create();
        $inputs = $registrations->map(fn ($registration) => ['actor' => $registration->participant_id, 'registration' => $registration->id, 'gantangan_number' => '21'])->all();

        $this->assertSame(['accepted', 'rejected'], $this->race($class->id, 'choose_number', $inputs));
        $this->assertSame(1, $class->registrations()->where('gantangan_number', '21')->count());
        $this->assertSame(1, $class->registrations()->whereNull('gantangan_number')->count());
        $this->assertSame(1, ActivityLog::query()->where('action', ActivityAction::GantanganNumberChosen)->count());
    }

    public function test_concurrent_warnings_escalate_once_without_losing_scores(): void
    {
        $f = $this->rankingFixture();
        $base = ['registration' => $f['first']->id];

        $this->assertSame(['accepted', 'accepted'], $this->race($f['class']->id, 'warning', [[...$base, 'actor' => $f['judges'][0]->id], [...$base, 'actor' => $f['judges'][1]->id]]));
        $this->assertSame(RegistrationStatus::Disqualified, $f['first']->fresh()->status);
        $this->assertSame(1, $f['first']->violations()->where('action', ViolationAction::Warning)->count());
        $this->assertSame(1, $f['first']->violations()->where('action', ViolationAction::Disqualification)->count());
        $this->assertDatabaseCount('judge_scores', 30);
    }

    public function test_concurrent_finalization_creates_only_one_immutable_final_snapshot(): void
    {
        $f = $this->rankingFixture();
        $input = ['actor' => $f['admin']->id, 'class' => $f['class']->id];

        $this->assertSame(['accepted', 'rejected'], $this->race($f['class']->id, 'finalize', [$input, $input]));
        $this->assertSame(CompetitionClassStatus::Finalized, $f['class']->fresh()->status);
        $this->assertDatabaseCount('ranking_runs', 1);
        $this->assertDatabaseCount('ranking_results', 2);
        $this->assertSame(1, ActivityLog::query()->where('action', ActivityAction::ResultFinalized)->count());
    }
}
