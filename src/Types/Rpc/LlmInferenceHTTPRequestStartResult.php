<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Acknowledgement for httpRequestStart. Returning successfully simply means the SDK accepted the start frame.
 */
readonly class LlmInferenceHTTPRequestStartResult implements Arrayable
{
    public function __construct() {}

    public static function fromArray(array $data): self
    {
        return new self;
    }

    public function toArray(): array
    {
        return [];
    }
}
