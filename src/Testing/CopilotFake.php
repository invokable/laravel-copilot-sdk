<?php

declare(strict_types=1);

namespace Revolution\Copilot\Testing;

use Revolution\Copilot\Contracts\CopilotSession;
use Revolution\Copilot\Contracts\Factory;
use Revolution\Copilot\Facades\Copilot;
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
    public array $recorded = [];

    /**
     * Session counter for generating unique IDs.
     */
    protected int $sessionCounter = 0;

    /**
     * Set up fake responses.
     *
     * @param  array<string, ResponseSequence|SessionEvent|string>|string|false|null  $responses
     */
    public function fake(array|string|false|null $responses): self
    {
        if ($responses === false) {
            $this->stubCallbacks = [];

            return $this;
        }

        if (is_null($responses)) {
            $responses = '';
        }

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
    public function start(callable $callback, array $config = [], ?string $resume = null): mixed
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
        if (Copilot::preventingStrayRequests()) {
            throw new RuntimeException("Attempted Copilot request without matching fake response: {$prompt}");
        }

        // Return empty sequence
        return new ResponseSequence;
    }

    /**
     * Check if a prompt matches a pattern.
     */
    public function matchesPattern(string $prompt, string $pattern): bool
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
}
