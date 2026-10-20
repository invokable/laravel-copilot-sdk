<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * HTTPS destinations authorized to receive one masked environment credential.
 *
 * @experimental
 */
readonly class SandboxMaskedEnvVar implements Arrayable
{
    public function __construct(
        public ?array $injectHosts = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            injectHosts: $data['injectHosts'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'injectHosts' => $this->injectHosts,
        ], fn ($value) => $value !== null);
    }
}
