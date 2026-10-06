<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/** Create a stored workspace record before opening the associated session. */
readonly class SessionsCreateWorkspaceRequest implements Arrayable
{
    public function __construct(
        public string $sessionId,
        public string $convention,
        public string $sessionStatePath,
        public SessionWorkingDirectoryContextWithClient|array|null $context = null,
        public ?string $name = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            sessionId: Arr::string($data, 'sessionId'),
            convention: Arr::string($data, 'convention'),
            context: isset($data['context'])
                ? (is_array($data['context'])
                    ? SessionWorkingDirectoryContextWithClient::fromArray($data['context'])
                    : $data['context'])
                : null,
            name: $data['name'] ?? null,
            sessionStatePath: Arr::string($data, 'sessionStatePath'),
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'sessionId' => $this->sessionId,
            'convention' => $this->convention,
            'context' => $this->context instanceof SessionWorkingDirectoryContextWithClient
                ? $this->context->toArray()
                : $this->context,
            'name' => $this->name,
            'sessionStatePath' => $this->sessionStatePath,
        ], static fn ($value) => $value !== null);
    }
}
