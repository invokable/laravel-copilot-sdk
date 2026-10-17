<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Lightweight persisted metadata for a local session.
 *
 * @see https://github.com/github/copilot-sdk/blob/b96cd2b54f7eb87458fd50f485a751c712e88f62/nodejs/src/generated/rpc.ts
 */
readonly class LocalSessionMetadataValue implements Arrayable
{
    public function __construct(
        public string $sessionId,
        public string $startTime,
        public string $modifiedTime,
        public ?string $summary = null,
        public ?string $name = null,
        public ?string $clientName = null,
        public false $isRemote = false,
        public ?bool $isDetached = null,
        public ?LocalSessionContext $context = null,
        public ?string $mcTaskId = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            sessionId: Arr::string($data, 'sessionId'),
            startTime: Arr::string($data, 'startTime'),
            modifiedTime: Arr::string($data, 'modifiedTime'),
            summary: $data['summary'] ?? null,
            name: $data['name'] ?? null,
            clientName: $data['clientName'] ?? null,
            isRemote: Arr::boolean($data, 'isRemote', false),
            isDetached: isset($data['isDetached']) ? Arr::boolean($data, 'isDetached') : null,
            context: isset($data['context']) && is_array($data['context'])
                ? LocalSessionContext::fromArray($data['context'])
                : null,
            mcTaskId: $data['mcTaskId'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'sessionId' => $this->sessionId,
            'startTime' => $this->startTime,
            'modifiedTime' => $this->modifiedTime,
            'summary' => $this->summary,
            'name' => $this->name,
            'clientName' => $this->clientName,
            'isRemote' => $this->isRemote,
            'isDetached' => $this->isDetached,
            'context' => $this->context?->toArray(),
            'mcTaskId' => $this->mcTaskId,
        ], fn ($value): bool => $value !== null);
    }
}
