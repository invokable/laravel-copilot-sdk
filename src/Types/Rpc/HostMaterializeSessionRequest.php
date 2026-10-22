<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/** Request for the owning application to create or resume a session for an AHP participant. */
readonly class HostMaterializeSessionRequest implements Arrayable
{
    public function __construct(
        public string $hostId,
        public string $handoffId,
        public ?HostSessionAuthentication $authentication = null,
        public ?bool $resume = null,
        public array $config = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            hostId: $data['hostId'] ?? '',
            handoffId: $data['handoffId'] ?? '',
            authentication: isset($data['authentication'])
                ? ($data['authentication'] instanceof HostSessionAuthentication
                    ? $data['authentication']
                    : HostSessionAuthentication::fromArray($data['authentication']))
                : null,
            resume: $data['resume'] ?? null,
            config: $data['config'] ?? [],
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'hostId' => $this->hostId,
            'handoffId' => $this->handoffId,
            'authentication' => $this->authentication?->toArray(),
            'resume' => $this->resume,
            'config' => $this->config,
        ], fn ($value) => $value !== null);
    }
}
