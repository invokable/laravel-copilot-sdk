<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Inbound request to list skills supplied by a session's provider.
 */
readonly class SkillProviderListRequest implements Arrayable
{
    public function __construct(
        public string $sessionId,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(sessionId: Arr::string($data, 'sessionId'));
    }

    public function toArray(): array
    {
        return ['sessionId' => $this->sessionId];
    }
}
