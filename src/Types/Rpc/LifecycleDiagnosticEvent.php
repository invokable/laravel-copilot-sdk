<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * One retained session lifecycle diagnostic event.
 *
 * @internal
 */
readonly class LifecycleDiagnosticEvent implements Arrayable
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(
        public array $data,
        public string $timestamp,
        public string $type,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            data: $data['data'] ?? [],
            timestamp: Arr::string($data, 'timestamp', ''),
            type: Arr::string($data, 'type', ''),
        );
    }

    public function toArray(): array
    {
        return [
            'data' => $this->data,
            'timestamp' => $this->timestamp,
            'type' => $this->type,
        ];
    }
}
