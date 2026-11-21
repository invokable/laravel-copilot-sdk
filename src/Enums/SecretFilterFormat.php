<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/** @experimental */
enum SecretFilterFormat: string
{
    case Text = 'text';
    case Json = 'json';
}
