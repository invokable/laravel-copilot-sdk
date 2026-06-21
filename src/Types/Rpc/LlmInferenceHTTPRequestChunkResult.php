<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Acknowledgement for httpRequestChunk. The SDK is free to treat chunk delivery as fire-and-forget.
 */
readonly class LlmInferenceHTTPRequestChunkResult implements Arrayable
{
    public function __construct() {}

    public static function fromArray(array $data): self
    {
        return new self();
    }

    public function toArray(): array
    {
        return [];
    }
}
