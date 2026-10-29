<?php

namespace App\Domain\Ranking\Services;

use App\Enums\CompetitionClassStatus;
use App\Models\CompetitionClass;
use Illuminate\Validation\ValidationException;

class FinalizedClassGuard
{
    public function lockMutable(int $id): CompetitionClass
    {
        $class = CompetitionClass::query()->lockForUpdate()->findOrFail($id);
        if ($class->status === CompetitionClassStatus::Finalized) {
            throw ValidationException::withMessages(['ranking' => 'Kelas sudah final. Informasi kelas dan hadiah resmi tidak dapat diubah.']);
        }

        return $class;
    }
}
