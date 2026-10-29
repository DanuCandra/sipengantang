<?php

namespace App\Enums;

enum RankingRunStatus: string
{
    case Completed = 'COMPLETED';
    case Failed = 'FAILED';
}
