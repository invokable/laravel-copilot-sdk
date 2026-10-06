<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

readonly class ModelProviderWarning implements Arrayable
{
    public function __construct(
        public string $code,
        public string $message,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            code: $data['code'] ?? '',
            message: $data['message'] ?? '',
        );
    }

    public function toArray(): array
    {
        return ['code' => $this->code, 'message' => $this->message];
    }
}
