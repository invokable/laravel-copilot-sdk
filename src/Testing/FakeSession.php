<?php

declare(strict_types=1);

namespace Revolution\Copilot\Testing;

use Closure;
use Revolution\Copilot\Contracts\CopilotSession;
use Revolution\Copilot\Enums\SessionEventType;
use Revolution\Copilot\Types\SessionEvent;

/**
 * Fake session for testing purposes.
 */
class FakeSession implements CopilotSession
{
    /**
     * Recorded prompts.
     *
     * @var array<array{prompt: string, attachments: ?array, mode: ?string}>
     */
    protected array $recorded = [];

    public function __construct(
        protected string $sessionId,
        protected ResponseSequence $sequence,
    ) {}

    public function id(): string
    {
        return $this->sessionId;
    }

    public function send(string $prompt, ?array $attachments = null, ?string $mode = null): string
    {
        $this->recorded[] = [
            'prompt' => $prompt,
            'attachments' => $attachments,
            'mode' => $mode,
        ];

        return 'fake-message-id';
    }

    public function sendAndWait(string $prompt, ?array $attachments = null, ?string $mode = null, ?float $timeout = null): ?SessionEvent
    {
        $this->recorded[] = [
            'prompt' => $prompt,
            'attachments' => $attachments,
            'mode' => $mode,
        ];

        return $this->sequence->pop();
    }

    public function on(string|SessionEventType|Closure|null $type = null, ?Closure $handler = null): Closure
    {
        return fn () => null;
    }

    public function off(Closure $handler): void
    {
        // No-op in fake
    }

    public function sendAndStream(string $prompt, ?array $attachments = null, ?string $mode = null, ?float $timeout = null): iterable
    {
        $this->recorded[] = [
            'prompt' => $prompt,
            'attachments' => $attachments,
            'mode' => $mode,
        ];

        $event = $this->sequence->pop();
        if ($event !== null) {
            yield $event;
        }
    }

    public function stream(?float $timeout = null): iterable
    {
        $event = $this->sequence->pop();
        if ($event !== null) {
            yield $event;
        }
    }

    /**
     * @return array<SessionEvent>
     */
    public function getMessages(): array
    {
        return $this->sequence->all();
    }

    /**
     * Get recorded prompts.
     *
     * @return array<array{prompt: string, attachments: ?array, mode: ?string}>
     */
    public function recorded(): array
    {
        return $this->recorded;
    }

    /**
     * Destroy this session.
     */
    public function destroy(): void
    {
        // No-op in fake
    }
}
