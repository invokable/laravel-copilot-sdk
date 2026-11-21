<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/** @experimental */
enum FileLogOperation: string
{
    case Log = 'log';
    case Debug = 'debug';
    case Info = 'info';
    case Notice = 'notice';
    case Warning = 'warning';
    case Error = 'error';
    case StartGroup = 'startGroup';
    case EndGroup = 'endGroup';
}
