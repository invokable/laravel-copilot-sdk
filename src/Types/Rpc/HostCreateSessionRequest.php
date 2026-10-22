<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/** Host-directed session handoff request. */
readonly class HostCreateSessionRequest implements Arrayable
{
    public function __construct(
        public string $handoffId,
        public ?bool $resume = null,
        public ?bool $preferResident = null,
        public ?array $config = null,
        public HostSessionAuthentication|array|null $authentication = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            handoffId: $data['handoffId'] ?? '',
            resume: $data['resume'] ?? null,
            preferResident: $data['preferResident'] ?? null,
            config: $data['config'] ?? null,
            authentication: isset($data['authentication'])
                ? ($data['authentication'] instanceof HostSessionAuthentication
                    ? $data['authentication']
                    : HostSessionAuthentication::fromArray($data['authentication']))
                : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'handoffId' => $this->handoffId,
            'resume' => $this->resume,
            'preferResident' => $this->preferResident,
            'config' => $this->config,
            'authentication' => $this->authentication instanceof HostSessionAuthentication
                ? $this->authentication->toArray()
                : $this->authentication,
        ], fn ($value) => $value !== null);
    }
}
