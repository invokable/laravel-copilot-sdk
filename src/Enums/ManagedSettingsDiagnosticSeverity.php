<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/** Severity of a managed-settings validation finding. */
enum ManagedSettingsDiagnosticSeverity: string
{
    case ERROR = 'error';
    case WARNING = 'warning';
}
