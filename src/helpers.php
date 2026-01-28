<?php

declare(strict_types=1);

namespace Revolution\Copilot;

use Revolution\Copilot\Facades\Copilot;

if (! function_exists('Revolution\Copilot\copilot')) {
    /**
     * Get the Copilot instance or run/start a session.
     */
    function copilot(string|callable|null $prompt = null): mixed
    {
        if (is_string($prompt)) {
            return Copilot::run($prompt);
        }

        if (is_callable($prompt)) {
            return Copilot::start($prompt);
        }

        return app(Contracts\Factory::class);
    }
}
