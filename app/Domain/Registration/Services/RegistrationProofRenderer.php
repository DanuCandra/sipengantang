<?php

namespace App\Domain\Registration\Services;

use App\Models\Registration;
use Barryvdh\DomPDF\PDF;

class RegistrationProofRenderer
{
    public function __construct(private PDF $pdf) {}

    public function render(Registration $registration): PDF
    {
        $registration->loadMissing(['participant', 'bird', 'competitionClass.event']);

        return $this->pdf
            ->setOption(['isRemoteEnabled' => false, 'isPhpEnabled' => false, 'isJavascriptEnabled' => false, 'defaultFont' => 'DejaVu Sans'])
            ->loadView('pdf.registration-proof', compact('registration'))
            ->setPaper('a4');
    }
}
