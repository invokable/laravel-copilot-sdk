<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * A filesystem error reported by the local agent registry.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class AgentRegistryError implements Arrayable
{
    public function __construct(
        public string $code,
        public ?string $message = null,
        public ?string $path = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            code: Arr::string($data, 'code'),
            message: isset($data['message']) ? Arr::string($data, 'message') : null,
            path: isset($data['path']) ? Arr::string($data, 'path') : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'code' => $this->code,
            'message' => $this->message,
            'path' => $this->path,
        ], fn ($value) => $value !== null);
    }
}
