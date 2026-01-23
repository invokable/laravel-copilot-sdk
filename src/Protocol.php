<?php

declare(strict_types=1);

namespace Revolution\Copilot;

final readonly class Protocol
{
    protected const int SDK_PROTOCOL_VERSION = 2;

    /**
     * Get the SDK protocol version.
     */
    public static function version(): int
    {
        return self::SDK_PROTOCOL_VERSION;
    }
}
