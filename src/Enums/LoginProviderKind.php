<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

enum LoginProviderKind: string
{
    case GITHUB_DOT_COM = 'gitHub';
    case PROXIMA = 'gitHubEnterpriseCloud';
    case ENTRA = 'entra';
}
