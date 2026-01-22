<?php

declare(strict_types=1);

namespace Revolution\Copilot\Testing;

use Revolution\Copilot\Enums\SessionEventType;
use Revolution\Copilot\Types\SessionEvent;

/**
 * Sequence of fake responses for testing.
 */
class ResponseSequence
{
    /**
     * @var array<SessionEvent>
     */
    protected array $responses = [];

    /**
     * Default response when sequence is exhausted.
     */
    protected ?SessionEvent $fallback = null;

    /**
     * Whether this sequence is empty (has been exhausted).
     */
    protected bool $isEmpty = false;

    /**
     * Push a response onto the sequence.
     */
    public function push(SessionEvent $response): self
    {
        $this->responses[] = $response;
        $this->isEmpty = false;

        return $this;
    }

    /**
     * Set the fallback response.
     */
    public function whenEmpty(SessionEvent $response): self
    {
        $this->fallback = $response;

        return $this;
    }

    /**
     * Pop the next response from the sequence.
     */
    public function pop(): ?SessionEvent
    {
        if (count($this->responses) === 0) {
            $this->isEmpty = true;

            return $this->fallback;
        }

        return array_shift($this->responses);
    }

    /**
     * Check if the sequence is empty.
     */
    public function isEmpty(): bool
    {
        return $this->isEmpty;
    }

    /**
     * @return SessionEvent[]
     */
    public function all(): array
    {
        return $this->responses;
    }

    /**
     * Create a response from a content string.
     */
    public static function responseFromContent(string $content): SessionEvent
    {
        return new SessionEvent(
            id: 'fake-'.uniqid(),
            timestamp: date('c'),
            parentId: null,
            type: SessionEventType::ASSISTANT_MESSAGE,
            data: ['content' => $content],
        );
    }
}
