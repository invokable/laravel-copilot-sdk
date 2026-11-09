<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types;

use Revolution\Copilot\Support\CancellationToken;

/**
 * Options passed to a memory store callback.
 */
readonly class MemoryStoreCallOptions
{
    public function __construct(
        public CancellationToken $signal,
    ) {}
}
