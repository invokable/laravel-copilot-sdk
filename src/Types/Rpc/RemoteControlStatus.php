<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Current state of the runtime-managed remote-control singleton.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class RemoteControlStatus implements Arrayable
{
    public function __construct(
        public string $state,
        public ?string $attachedSessionId = null,
        public ?string $frontendUrl = null,
        public ?bool $isSteerable = null,
        public ?bool $awaitingFirstMessage = null,
        public ?string $error = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            state: Arr::string($data, 'state'),
            attachedSessionId: $data['attachedSessionId'] ?? null,
            frontendUrl: $data['frontendUrl'] ?? null,
            isSteerable: isset($data['isSteerable']) ? Arr::boolean($data, 'isSteerable') : null,
            awaitingFirstMessage: isset($data['awaitingFirstMessage']) ? Arr::boolean($data, 'awaitingFirstMessage') : null,
            error: $data['error'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'state' => $this->state,
            'attachedSessionId' => $this->attachedSessionId,
            'frontendUrl' => $this->frontendUrl,
            'isSteerable' => $this->isSteerable,
            'awaitingFirstMessage' => $this->awaitingFirstMessage,
            'error' => $this->error,
        ], static fn ($value) => $value !== null);
    }
}
