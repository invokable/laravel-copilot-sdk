<?php

declare(strict_types=1);

namespace Revolution\Copilot\Facades;

use Illuminate\Support\Facades\Facade;
use Revolution\Copilot\Contracts\CopilotSession;
use Revolution\Copilot\Contracts\Factory;
use Revolution\Copilot\Testing\CopilotFake;
use Revolution\Copilot\Testing\ResponseSequence;
use Revolution\Copilot\Types\SessionEvent;

/**
 * @method static SessionEvent|null run(string $prompt, array $options = [])
 * @method static mixed start(callable $callback, array $config = [])
 * @method static CopilotSession createSession(array $config = [])
 * @method static SessionEvent response(string $content)
 * @method static ResponseSequence sequence()
 * @method static CopilotFake preventStrayRequests(array $allow = [])
 * @method static CopilotFake assertPrompt(string $pattern)
 * @method static CopilotFake assertNotPrompt(string $pattern)
 * @method static CopilotFake assertPromptCount(int $count)
 * @method static CopilotFake assertNothingSent()
 *
 * @mixin  \Revolution\Copilot\CopilotManager
 * @mixin  \Revolution\Copilot\Testing\CopilotFake
 */
class Copilot extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return Factory::class;
    }

    /**
     * Swap the bound instance to a fake for testing.
     *
     * @param  array<string, ResponseSequence|SessionEvent|string>|string|null  $responses
     */
    public static function fake(array|string|null $responses = null): CopilotFake
    {
        $fake = new CopilotFake($responses);

        static::swap($fake);

        return $fake;
    }
}
