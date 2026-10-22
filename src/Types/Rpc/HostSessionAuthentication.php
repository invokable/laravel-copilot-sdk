<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\HostAuthenticationMode;

/** Trusted host admission identity passed with an application-owned session handoff. */
readonly class HostSessionAuthentication implements Arrayable
{
    public function __construct(
        public HostAuthenticationMode $mode,
        public ?string $identity = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            mode: HostAuthenticationMode::from($data['mode'] ?? ''),
            identity: $data['identity'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'mode' => $this->mode->value,
            'identity' => $this->identity,
        ], fn ($value) => $value !== null);
    }
}
