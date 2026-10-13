<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Provider-side canvas open parameters.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class CanvasProviderOpenRequest implements Arrayable
{
    public function __construct(
        public string $sessionId,
        public string $extensionId,
        public string $canvasId,
        public string $instanceId,
        public mixed $input = null,
        public ?array $host = null,
        public ?array $session = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            sessionId: Arr::string($data, 'sessionId'),
            extensionId: Arr::string($data, 'extensionId'),
            canvasId: Arr::string($data, 'canvasId'),
            instanceId: Arr::string($data, 'instanceId'),
            input: $data['input'] ?? null,
            host: $data['host'] ?? null,
            session: $data['session'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'sessionId' => $this->sessionId,
            'extensionId' => $this->extensionId,
            'canvasId' => $this->canvasId,
            'instanceId' => $this->instanceId,
            'input' => $this->input,
            'host' => $this->host,
            'session' => $this->session,
        ], fn ($value) => $value !== null);
    }
}
