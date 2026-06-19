<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Optional model identifier to scope the provider endpoint snapshot to.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class ProviderGetEndpointRequest implements Arrayable
{
    /**
     * @param  ?string  $modelId  Model identifier to scope the endpoint snapshot to.
     *                            Omit to use whichever model the session is currently using.
     */
    public function __construct(
        public ?string $modelId = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            modelId: $data['modelId'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'modelId' => $this->modelId,
        ], fn ($v) => $v !== null);
    }
}
