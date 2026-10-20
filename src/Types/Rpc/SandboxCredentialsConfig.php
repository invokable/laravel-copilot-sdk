<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Whole-value environment credential masking configuration.
 *
 * @experimental
 */
readonly class SandboxCredentialsConfig implements Arrayable
{
    public function __construct(
        public ?array $envVars = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $envVars = $data['envVars'] ?? null;

        if ($envVars !== null) {
            $envVars = array_map(
                fn (SandboxMaskedEnvVar|array $envVar): SandboxMaskedEnvVar => $envVar instanceof SandboxMaskedEnvVar
                    ? $envVar
                    : SandboxMaskedEnvVar::fromArray($envVar),
                $envVars,
            );
        }

        return new self(envVars: $envVars);
    }

    public function toArray(): array
    {
        return array_filter([
            'envVars' => $this->envVars === null
                ? null
                : array_map(
                    fn (SandboxMaskedEnvVar|array $envVar) => $envVar instanceof SandboxMaskedEnvVar
                        ? $envVar->toArray()
                        : $envVar,
                    $this->envVars,
                ),
        ], fn ($value) => $value !== null);
    }
}
