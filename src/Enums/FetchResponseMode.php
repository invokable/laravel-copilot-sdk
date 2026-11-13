<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/** @experimental */
enum FetchResponseMode: string
{
    case Stream = 'stream';
    case Text = 'text';
    case File = 'file';
}
