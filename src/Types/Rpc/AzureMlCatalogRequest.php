<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\AzureMlCatalogRetryMode;

/**
 * Requests Azure ML local-model catalog discovery.
 *
 * @experimental
 */
readonly class AzureMlCatalogRequest implements Arrayable
{
    public function __construct(
        public bool $includeDev,
        public AzureMlCatalogRetryMode|string|null $retryMode = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $retryMode = $data['retryMode'] ?? null;

        return new self(
            includeDev: $data['includeDev'],
            retryMode: is_string($retryMode)
                ? (AzureMlCatalogRetryMode::tryFrom($retryMode) ?? $retryMode)
                : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'includeDev' => $this->includeDev,
            'retryMode' => $this->retryMode instanceof AzureMlCatalogRetryMode
                ? $this->retryMode->value
                : $this->retryMode,
        ], fn ($value) => $value !== null);
    }
}
