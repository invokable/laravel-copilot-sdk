<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Indicates whether the calling client was registered as the LLM inference provider.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class LlmInferenceSetProviderResult implements Arrayable
{
    /**
     * @param  bool  $success  Whether the provider was set successfully.
     */
    public function __construct(
        public bool $success,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            success: Arr::boolean($data, 'success'),
        );
    }

    public function toArray(): array
    {
        return [
            'success' => $this->success,
        ];
    }
}
