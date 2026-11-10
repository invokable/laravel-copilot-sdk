<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

enum LocalModelOperation: string
{
    case Installing = 'installing';
    case Probing = 'probing';
    case Downloading = 'downloading';
    case Registering = 'registering';
    case Deleting = 'deleting';
}
