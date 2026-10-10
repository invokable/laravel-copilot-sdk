<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

enum ProviderQuotaObservationKind: string
{
    case ACCOUNT_SNAPSHOT = 'account_snapshot';
    case ADMISSION_STATE = 'admission_state';
}
