<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

enum AccountKind: string
{
    case GITHUB_DOT_COM = 'gitHub';
    case PROXIMA = 'gitHubEnterpriseCloud';
    case ENTRA_EMU = 'entraEmu';
    case ENTRA = 'entra';
    case LOKI = 'microsoft365Copilot';
}
