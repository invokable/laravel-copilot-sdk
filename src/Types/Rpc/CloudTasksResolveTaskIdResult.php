<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * The outcome of resolving a cloud coding-agent task to a session.
 *
 * @experimental
 */
readonly class CloudTasksResolveTaskIdResult implements Arrayable
{
    public function __construct(
        public string $kind,
        public ?string $sessionId = null,
        public ?string $metadataJson = null,
        public ?string $diagnostic = null,
        public ?string $errorMessage = null,
        public ?int $errorStatusCode = null,
        public ?string $errorKind = null,
        public ?string $errorPayloadJson = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            kind: $data['kind'],
            sessionId: $data['sessionId'] ?? null,
            metadataJson: $data['metadataJson'] ?? null,
            diagnostic: $data['diagnostic'] ?? null,
            errorMessage: $data['errorMessage'] ?? null,
            errorStatusCode: $data['errorStatusCode'] ?? null,
            errorKind: $data['errorKind'] ?? null,
            errorPayloadJson: $data['errorPayloadJson'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'kind' => $this->kind,
            'sessionId' => $this->sessionId,
            'metadataJson' => $this->metadataJson,
            'diagnostic' => $this->diagnostic,
            'errorMessage' => $this->errorMessage,
            'errorStatusCode' => $this->errorStatusCode,
            'errorKind' => $this->errorKind,
            'errorPayloadJson' => $this->errorPayloadJson,
        ], fn ($value): bool => $value !== null);
    }
}
