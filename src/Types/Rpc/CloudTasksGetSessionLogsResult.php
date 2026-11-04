<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * The outcome of reading a cloud coding-agent session's logs.
 *
 * @experimental
 */
readonly class CloudTasksGetSessionLogsResult implements Arrayable
{
    /**
     * @param  string[]|null  $debugMessages
     */
    public function __construct(
        public bool $resolved,
        public bool $ok,
        public ?string $logsJson = null,
        public ?array $debugMessages = null,
        public ?string $errorString = null,
        public ?int $statusCode = null,
        public ?string $errorMessage = null,
        public ?string $errorKind = null,
        public ?string $errorPayloadJson = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            resolved: $data['resolved'],
            ok: $data['ok'],
            logsJson: $data['logsJson'] ?? null,
            debugMessages: $data['debugMessages'] ?? null,
            errorString: $data['errorString'] ?? null,
            statusCode: $data['statusCode'] ?? null,
            errorMessage: $data['errorMessage'] ?? null,
            errorKind: $data['errorKind'] ?? null,
            errorPayloadJson: $data['errorPayloadJson'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'resolved' => $this->resolved,
            'ok' => $this->ok,
            'logsJson' => $this->logsJson,
            'debugMessages' => $this->debugMessages,
            'errorString' => $this->errorString,
            'statusCode' => $this->statusCode,
            'errorMessage' => $this->errorMessage,
            'errorKind' => $this->errorKind,
            'errorPayloadJson' => $this->errorPayloadJson,
        ], fn ($value): bool => $value !== null);
    }
}
