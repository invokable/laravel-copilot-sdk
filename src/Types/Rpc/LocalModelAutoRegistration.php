<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Automatic model registration metadata.
 *
 * @experimental
 */
readonly class LocalModelAutoRegistration implements Arrayable
{
    public function __construct(
        public string $capiId,
        public int $maxConcurrent,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            capiId: $data['capiId'] ?? '',
            maxConcurrent: (int) ($data['maxConcurrent'] ?? 0),
        );
    }

    public function toArray(): array
    {
        return [
            'capiId' => $this->capiId,
            'maxConcurrent' => $this->maxConcurrent,
        ];
    }
}
