<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Credential-injection capability flags for sandboxed processes.
 *
 * @experimental
 */
readonly class SandboxConfigAuth implements Arrayable
{
    public function __construct(
        public ?bool $git = null,
        public ?bool $gh = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            git: $data['git'] ?? null,
            gh: $data['gh'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'git' => $this->git,
            'gh' => $this->gh,
        ], fn ($value) => $value !== null);
    }
}
