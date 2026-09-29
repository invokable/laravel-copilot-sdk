<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

enum AccountKind: string
{
    case GITHUB_DOT_COM = 'githubDotCom';
    case PROXIMA = 'proxima';
    case ENTRA_EMU = 'entraEmu';
    case ENTRA = 'entra';
    case LOKI = 'loki';
}
