<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Inbound request to read a skill's Markdown content.
 */
readonly class SkillProviderReadRequest implements Arrayable
{
    public function __construct(
        public string $sessionId,
        public string $name,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            sessionId: Arr::string($data, 'sessionId'),
            name: Arr::string($data, 'name'),
        );
    }

    public function toArray(): array
    {
        return [
            'sessionId' => $this->sessionId,
            'name' => $this->name,
        ];
    }
}
