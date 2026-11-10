<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Host preferences for its connection-owned manager.
 *
 * @experimental
 */
readonly class LocalModelsConfigureRequest implements Arrayable
{
    public function __construct(
        public bool $enabled,
        public bool $catalogEnabled,
        public bool $includeDev,
        public bool $streamerMode,
        public ?string $sessionId = null,
        public ?string $diagnosticLogPath = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            enabled: $data['enabled'] ?? false,
            catalogEnabled: $data['catalogEnabled'] ?? false,
            includeDev: $data['includeDev'] ?? false,
            streamerMode: $data['streamerMode'] ?? false,
            sessionId: $data['sessionId'] ?? null,
            diagnosticLogPath: $data['diagnosticLogPath'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'enabled' => $this->enabled,
            'catalogEnabled' => $this->catalogEnabled,
            'includeDev' => $this->includeDev,
            'streamerMode' => $this->streamerMode,
            ...array_filter([
                'sessionId' => $this->sessionId,
                'diagnosticLogPath' => $this->diagnosticLogPath,
            ], static fn ($value) => $value !== null),
        ];
    }
}
