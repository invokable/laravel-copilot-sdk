<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

readonly class SessionsLoadWorkspaceRequest implements Arrayable
{
    public function __construct(
        public string $sessionId,
        public string $sessionsHome,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            sessionId: Arr::string($data, 'sessionId'),
            sessionsHome: Arr::string($data, 'sessionsHome'),
        );
    }

    public function toArray(): array
    {
        return ['sessionId' => $this->sessionId, 'sessionsHome' => $this->sessionsHome];
    }
}
