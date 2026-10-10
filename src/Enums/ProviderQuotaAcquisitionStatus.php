<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

enum ProviderQuotaAcquisitionStatus: string
{
    case SUCCEEDED = 'succeeded';
    case UNAVAILABLE = 'unavailable';
    case FAILED = 'failed';
}
