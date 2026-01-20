<?php

declare(strict_types=1);

namespace Revolution\Copilot\Facades;

use Illuminate\Support\Facades\Facade;
use Revolution\Copilot\Contracts\Factory;
use Revolution\Copilot\CopilotSession;
use Revolution\Copilot\Types\SessionEvent;

/**
 * @method static SessionEvent|null run(string $prompt, array $options = [])
 * @method static mixed start(callable $callback, array $config = [])
 * @method static CopilotSession createSession(array $config = [])
 * @method static void fake(array $responses = [])
 *
 * @see \Revolution\Copilot\CopilotManager
 */
class Copilot extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return Factory::class;
    }
}
