<?php

declare(strict_types=1);

namespace Revolution\Copilot\Support;

class Attachment
{
    public static function file(string $path, ?string $displayName = null): array
    {
        return array_filter([
            'type' => 'file',
            'path' => $path,
            'displayName' => $displayName,
        ]);
    }

    public static function directory(string $path, ?string $displayName = null): array
    {
        return array_filter([
            'type' => 'directory',
            'path' => $path,
            'displayName' => $displayName,
        ]);
    }
}
