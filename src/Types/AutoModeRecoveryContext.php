<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Context for recovering an active request after an eligible failure.
 */
readonly class AutoModeRecoveryContext implements Arrayable
{
    public function __construct(
        public string $requestId,
        public string $originalModel,
        public bool $allowAlways,
        public ?string $interactionId = null,
        public ?string $originatingMessageId = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            requestId: $data['requestId'],
            originalModel: $data['originalModel'],
            allowAlways: $data['allowAlways'],
            interactionId: $data['interactionId'] ?? null,
            originatingMessageId: $data['originatingMessageId'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'requestId' => $this->requestId,
            'originalModel' => $this->originalModel,
            'allowAlways' => $this->allowAlways,
            'interactionId' => $this->interactionId,
            'originatingMessageId' => $this->originatingMessageId,
        ], fn ($value) => $value !== null);
    }
}
