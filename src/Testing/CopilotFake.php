<?php

declare(strict_types=1);

namespace Revolution\Copilot\Testing;

use PHPUnit\Framework\Assert as PHPUnit;
use Revolution\Copilot\Contracts\CopilotSession;
use Revolution\Copilot\Contracts\Factory;
use Revolution\Copilot\Types\SessionEvent;
use RuntimeException;

/**
 * Fake Copilot implementation for testing.
 */
class CopilotFake implements Factory
{
    /**
     * Response sequences by pattern.
     *
     * @var array<string, ResponseSequence>
     */
    protected array $stubCallbacks = [];

    /**
     * Recorded prompts across all sessions.
     *
     * @var array<array{prompt: string, attachments: ?array, mode: ?string}>
     */
    protected array $recorded = [];

    /**
     * Whether to prevent stray requests.
     */
    protected bool $preventStrayRequests = false;

    /**
     * Allowed methods when preventing stray requests.
     *
     * @var array<string>
     */
    protected array $allowedMethods = [];

    /**
     * Session counter for generating unique IDs.
     */
    protected int $sessionCounter = 0;

    /**
     * Create a new fake instance.
     *
     * @param  array<string, ResponseSequence|SessionEvent|string>|string|null  $responses
     */
    public function __construct(array|string|null $responses = null)
    {
        if ($responses !== null) {
            $this->fake($responses);
        }
    }

    /**
     * Set up fake responses.
     *
     * @param  array<string, ResponseSequence|SessionEvent|string>|string  $responses
     */
    public function fake(array|string $responses): self
    {
        if (is_string($responses)) {
            // Single string response for all prompts
            $this->stubCallbacks['*'] = (new ResponseSequence)
                ->push(ResponseSequence::responseFromContent($responses))
                ->whenEmpty(ResponseSequence::responseFromContent($responses));

            return $this;
        }

        foreach ($responses as $pattern => $response) {
            $this->stubCallbacks[$pattern] = $this->normalizeResponse($response);
        }

        return $this;
    }

    /**
     * Normalize a response to ResponseSequence.
     */
    protected function normalizeResponse(ResponseSequence|SessionEvent|string $response): ResponseSequence
    {
        if ($response instanceof ResponseSequence) {
            return $response;
        }

        if ($response instanceof SessionEvent) {
            return (new ResponseSequence)->push($response)->whenEmpty($response);
        }

        // String response
        $event = ResponseSequence::responseFromContent($response);

        return (new ResponseSequence)->push($event)->whenEmpty($event);
    }

    /**
     * Run a single prompt and return the response.
     */
    public function run(string $prompt, ?array $attachments = null, ?string $mode = null): ?SessionEvent
    {
        return $this->start(
            fn (CopilotSession $session) => $session->sendAndWait(
                prompt: $prompt,
                attachments: $attachments,
                mode: $mode,
            ),
        );
    }

    /**
     * Start a session and execute a callback.
     *
     * @param  callable(CopilotSession): mixed  $callback
     */
    public function start(callable $callback, array $config = []): mixed
    {
        $sequence = $this->getSequenceFor('*');
        $session = new FakeSession('fake-session-'.++$this->sessionCounter, $sequence);

        try {
            $result = $callback($session);

            // Record all prompts from the session
            foreach ($session->recorded() as $record) {
                $this->recordPrompt($record);
            }

            return $result;
        } finally {
            // No cleanup needed for fake session
        }
    }

    /**
     * Create a new session (caller is responsible for destroying it).
     */
    public function createSession(array $config = []): CopilotSession
    {
        $sequence = $this->getSequenceFor('*');

        return new FakeSession('fake-session-'.++$this->sessionCounter, $sequence);
    }

    /**
     * Create a response helper.
     */
    public function response(string $content): SessionEvent
    {
        return ResponseSequence::responseFromContent($content);
    }

    /**
     * Create a response sequence helper.
     */
    public function sequence(): ResponseSequence
    {
        return new ResponseSequence;
    }

    /**
     * Get the response sequence for a pattern.
     */
    protected function getSequenceFor(string $prompt): ResponseSequence
    {
        // Check for exact match first
        if (isset($this->stubCallbacks[$prompt])) {
            return $this->stubCallbacks[$prompt];
        }

        // Check for pattern matches
        foreach ($this->stubCallbacks as $pattern => $sequence) {
            if ($pattern === '*') {
                continue;
            }

            if ($this->matchesPattern($prompt, $pattern)) {
                return $sequence;
            }
        }

        // Fall back to wildcard
        if (isset($this->stubCallbacks['*'])) {
            return $this->stubCallbacks['*'];
        }

        // No match and preventing stray requests
        if ($this->preventStrayRequests) {
            throw new RuntimeException("Attempted Copilot request without matching fake response: {$prompt}");
        }

        // Return empty sequence
        return new ResponseSequence;
    }

    /**
     * Check if a prompt matches a pattern.
     */
    protected function matchesPattern(string $prompt, string $pattern): bool
    {
        // Convert pattern to regex (simple wildcard matching)
        $regex = '/^'.str_replace(['\*'], ['.*'], preg_quote($pattern, '/')).'$/';

        return preg_match($regex, $prompt) === 1;
    }

    /**
     * Record a prompt.
     *
     * @param  array{prompt: string, attachments: ?array, mode: ?string}  $record
     */
    protected function recordPrompt(array $record): void
    {
        $this->recorded[] = $record;
    }

    /**
     * Prevent stray requests.
     *
     * @param  array<string>  $allow
     */
    public function preventStrayRequests(array $allow = []): self
    {
        $this->preventStrayRequests = true;
        $this->allowedMethods = $allow;

        return $this;
    }

    /**
     * Assert that a prompt was sent.
     */
    public function assertPrompt(string $pattern): self
    {
        $found = false;

        foreach ($this->recorded as $record) {
            if ($this->matchesPattern($record['prompt'], $pattern)) {
                $found = true;
                break;
            }
        }

        PHPUnit::assertTrue($found, "Failed asserting that a prompt matching [{$pattern}] was sent.");

        return $this;
    }

    /**
     * Assert that a prompt was NOT sent.
     */
    public function assertNotPrompt(string $pattern): self
    {
        $found = false;

        foreach ($this->recorded as $record) {
            if ($this->matchesPattern($record['prompt'], $pattern)) {
                $found = true;
                break;
            }
        }

        PHPUnit::assertFalse($found, "Failed asserting that a prompt matching [{$pattern}] was not sent.");

        return $this;
    }

    /**
     * Assert the number of prompts sent.
     */
    public function assertPromptCount(int $count): self
    {
        PHPUnit::assertCount($count, $this->recorded, "Failed asserting that {$count} prompts were sent.");

        return $this;
    }

    /**
     * Assert that no prompts were sent.
     */
    public function assertNothingSent(): self
    {
        PHPUnit::assertEmpty($this->recorded, 'Failed asserting that no prompts were sent.');

        return $this;
    }

    /**
     * Get all recorded prompts.
     *
     * @return array<array{prompt: string, attachments: ?array, mode: ?string}>
     */
    public function recorded(): array
    {
        return $this->recorded;
    }
}
