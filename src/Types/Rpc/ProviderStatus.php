<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Credential-free provider configuration status for the selected model.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class ProviderStatus implements Arrayable
{
    /**
     * @param  bool  $hasCustomProvider  Whether the selected model uses a custom provider instead of requiring GitHub authentication.
     */
    public function __construct(
        public bool $hasCustomProvider,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            hasCustomProvider: Arr::boolean($data, 'hasCustomProvider'),
        );
    }

    public function toArray(): array
    {
        return [
            'hasCustomProvider' => $this->hasCustomProvider,
        ];
    }
}
