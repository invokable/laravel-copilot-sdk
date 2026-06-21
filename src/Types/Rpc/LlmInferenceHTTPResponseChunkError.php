<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Transport-level error terminating an HTTP response stream.
 */
readonly class LlmInferenceHTTPResponseChunkError implements Arrayable
{
    /**
     * @param  string  $message  Human-readable failure description.
     * @param  ?string  $code  Optional machine-readable error code.
     */
    public function __construct(
        public string $message,
        public ?string $code = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            message: $data['message'],
            code: $data['code'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'message' => $this->message,
            'code' => $this->code,
        ], fn ($v) => $v !== null);
    }
}
