<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Service-published informational message about a model.
 */
readonly class ModelMessage implements Arrayable
{
    public function __construct(
        public string $code,
        public string $message,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            code: (string) ($data['code'] ?? ''),
            message: (string) ($data['message'] ?? ''),
        );
    }

    public function toArray(): array
    {
        return ['code' => $this->code, 'message' => $this->message];
    }
}
