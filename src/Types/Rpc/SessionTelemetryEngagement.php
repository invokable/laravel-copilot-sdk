<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Telemetry engagement ID for a session, when available.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SessionTelemetryEngagement implements Arrayable
{
    public function __construct(
        public ?string $engagementId = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            engagementId: isset($data['engagementId']) ? Arr::string($data, 'engagementId') : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'engagementId' => $this->engagementId,
        ], fn ($value) => $value !== null);
    }
}
