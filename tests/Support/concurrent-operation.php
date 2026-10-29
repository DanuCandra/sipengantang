<?php

use App\Domain\Judging\Actions\RecordViolationAction;
use App\Domain\Payment\Actions\ReviewPaymentAction;
use App\Domain\Ranking\Actions\FinalizeClassResultAction;
use App\Domain\Registration\Actions\ChooseGantanganNumberAction;
use App\Domain\Registration\Actions\CreateRegistrationAction;
use App\Enums\PaymentStatus;
use App\Enums\ViolationAction;
use App\Enums\ViolationReasonCode;
use App\Models\Bird;
use App\Models\CompetitionClass;
use App\Models\PaymentSubmission;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\TestDatabaseGuard;

// This harness is invoked only by the opt-in MySQL concurrency suite.
require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
TestDatabaseGuard::ensureSafe($app);

$operation = $argv[1] ?? '';
$input = json_decode($argv[2] ?? '{}', true, flags: JSON_THROW_ON_ERROR);
$id = DB::selectOne('SELECT CONNECTION_ID() AS id')->id;
fwrite(STDOUT, 'READY:'.$id."\n");
fflush(STDOUT);
try {
    $actor = User::query()->findOrFail($input['actor']);
    match ($operation) {
        'register' => app(CreateRegistrationAction::class)->execute($actor, CompetitionClass::query()->findOrFail($input['class']), Bird::query()->findOrFail($input['bird']), (string) ($input['gantangan_number'] ?? $input['bird'])),
        'choose_number' => app(ChooseGantanganNumberAction::class)->execute($actor, Registration::query()->findOrFail($input['registration']), $input['gantangan_number']),
        'review' => app(ReviewPaymentAction::class)->execute($actor, PaymentSubmission::query()->findOrFail($input['payment']), PaymentStatus::from($input['decision']), 'Bukti ditolak oleh petugas.'),
        'warning' => app(RecordViolationAction::class)->execute($actor, Registration::query()->findOrFail($input['registration']), ViolationAction::Warning, ViolationReasonCode::Shouting, null, 'Peringatan nyata pada peserta.', true),
        'finalize' => app(FinalizeClassResultAction::class)->execute($actor, CompetitionClass::query()->findOrFail($input['class'])),
        default => throw new InvalidArgumentException('Unknown test operation'),
    };
    echo "RESULT:accepted\n";
} catch (ValidationException $exception) {
    echo "RESULT:rejected\n";
} catch (Throwable $exception) {
    // Do not expose credentials, SQL bindings or private exception context in child output.
    echo 'RESULT:error:'.get_class($exception)."\n";
    exit(1);
}
