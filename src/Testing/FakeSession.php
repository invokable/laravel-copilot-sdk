<?php

declare(strict_types=1);

namespace Revolution\Copilot\Testing;

use Closure;
use Revolution\Copilot\Contracts\CopilotSession;
use Revolution\Copilot\Enums\ElicitationAction;
use Revolution\Copilot\Enums\LogLevel;
use Revolution\Copilot\Enums\ReasoningEffort;
use Revolution\Copilot\Enums\SessionEventType;
use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\SessionRpc;
use Revolution\Copilot\Transport\StdioTransport;
use Revolution\Copilot\Types\InputOptions;
use Revolution\Copilot\Types\Rpc\SessionUiElicitationResult;
use Revolution\Copilot\Types\SessionCapabilities;
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

    public function rpc(): SessionRpc
    {
        // Return a SessionRpc with a mock client; methods will throw if actually called in tests
        // Since SessionRpc requires a real JsonRpcClient, we create a minimal instance
        return new SessionRpc(
            new JsonRpcClient(
                new StdioTransport(
                    fopen('php://memory', 'r'),
                    fopen('php://memory', 'w'),
                ),
            ),
            $this->sessionId,
        );
    }

    public function capabilities(): SessionCapabilities
    {
        return new SessionCapabilities;
    }

    public function elicitation(string $message, array $requestedSchema): SessionUiElicitationResult
    {
        return new SessionUiElicitationResult(action: ElicitationAction::CANCEL);
    }

    public function confirm(string $message): bool
    {
        return false;
    }

    public function select(string $message, array $options): ?string
    {
        return null;
    }

    public function input(string $message, InputOptions|array|null $options = null): ?string
    {
        return null;
    }

    public function setModel(string $model, ReasoningEffort|string|null $reasoningEffort = null): void
    {
        // No-op in fake
    }

    public function log(string $message, ?LogLevel $level = null, ?bool $ephemeral = null): string
    {
        return '';
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
     * Disconnect this session and release all in-memory resources.
     */
    public function disconnect(): void
    {
        // No-op in fake
    }

    /**
     * @deprecated Use disconnect() instead.
     */
    public function destroy(): void
    {
        $this->disconnect();
    }
}
