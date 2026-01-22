<?php

declare(strict_types=1);

namespace Revolution\Copilot\Facades;

use Illuminate\Support\Facades\Facade;
use Revolution\Copilot\Contracts\CopilotSession;
use Revolution\Copilot\Contracts\Factory;
use Revolution\Copilot\Testing\ResponseSequence;
use Revolution\Copilot\Types\ResumeSessionConfig;
use Revolution\Copilot\Types\SessionConfig;
use Revolution\Copilot\Types\SessionEvent;

/**
 * @method static SessionEvent|null run(string $prompt, ?array $attachments = null, ?string $mode = null)
 * @method static mixed start(callable $callback, SessionConfig|ResumeSessionConfig|array $config = [], ?string $resume = null)
 * @method static CopilotSession createSession(SessionConfig|array $config = [])
 * @method static SessionEvent response(string $content)
 * @method static ResponseSequence sequence()
 * @method static bool preventingStrayRequests()
 * @method static bool isAllowedMethod(string $method)
 * @method static Factory assertPrompt(string $pattern)
 * @method static Factory assertNotPrompt(string $pattern)
 * @method static Factory assertPromptCount(int $count)
 * @method static Factory assertNothingSent()
 *
 * @mixin  \Revolution\Copilot\CopilotManager
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
     * @param  array<string, ResponseSequence|SessionEvent|string>|string|false|null  $responses
     */
    public static function fake(array|string|false|null $responses = null): Factory
    {
        return tap(static::getFacadeRoot(), function ($fake) use ($responses) {
            static::swap($fake->fake($responses));
        });
    }

    /**
     * Indicate that an exception should be thrown if any request is not faked.
     */
    public static function preventStrayRequests(array $allow = []): Factory
    {
        return tap(static::getFacadeRoot(), function ($fake) use ($allow) {
            static::swap($fake->preventStrayRequests($allow));
        });
    }
}
