<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Individual tool overrides, applied after the master switch.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SessionManagementToolOverrides implements Arrayable
{
    public function __construct(
        public ?bool $close = null,
        public ?bool $create = null,
        public ?bool $list = null,
        public ?bool $message = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            close: $data['close'] ?? null,
            create: $data['create'] ?? null,
            list: $data['list'] ?? null,
            message: $data['message'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'close' => $this->close,
            'create' => $this->create,
            'list' => $this->list,
            'message' => $this->message,
        ], fn ($value): bool => $value !== null);
    }
}
