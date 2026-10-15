<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Request for listing model token price units.
 */
readonly class ModelsListTokenPriceUnitsRequest implements Arrayable
{
    public function __construct(
        public ?string $selectionId = null,
        public ?string $gitHubToken = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            selectionId: $data['selectionId'] ?? null,
            gitHubToken: $data['gitHubToken'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'selectionId' => $this->selectionId,
            'gitHubToken' => $this->gitHubToken,
        ], fn ($value) => $value !== null);
    }
}
