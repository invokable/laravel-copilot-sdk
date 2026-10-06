<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types;

use Revolution\Copilot\Support\CancellationToken;

/**
 * Options passed to a session-scoped skill provider callback.
 */
readonly class SkillProviderCallOptions
{
    public function __construct(
        public CancellationToken $signal,
    ) {}
}
