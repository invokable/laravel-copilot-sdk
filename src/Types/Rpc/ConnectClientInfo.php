<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Optional identity of the integrating host used for telemetry attribution.
 */
readonly class ConnectClientInfo implements Arrayable
{
    public function __construct(
        public ?string $editorName = null,
        public ?string $editorVersion = null,
        public ?string $extensionName = null,
        public ?string $extensionVersion = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            editorName: $data['editorName'] ?? null,
            editorVersion: $data['editorVersion'] ?? null,
            extensionName: $data['extensionName'] ?? null,
            extensionVersion: $data['extensionVersion'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'editorName' => $this->editorName,
            'editorVersion' => $this->editorVersion,
            'extensionName' => $this->extensionName,
            'extensionVersion' => $this->extensionVersion,
        ], fn ($value) => $value !== null);
    }
}
