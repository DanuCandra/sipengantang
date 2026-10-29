<?php

namespace App\Domain\Certificate\Services;

use Barryvdh\DomPDF\Facade\Pdf;

class CertificateRenderer
{
    public const VERSION = '1.0.0';

    public function render(array $snapshot): string
    {
        // The package facade creates a fresh renderer per PDF, including within one queue job.
        return Pdf::setOption(['isRemoteEnabled' => false, 'isPhpEnabled' => false, 'isJavascriptEnabled' => false, 'defaultFont' => 'DejaVu Sans'])
            ->loadView('pdf.certificate', ['certificate' => $snapshot])->setPaper('a4', 'landscape')->output();
    }
}
