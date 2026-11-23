<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\MemoryImportStatus;

/** @experimental */
readonly class SessionMemoryImportRecordResult implements Arrayable
{
    public function __construct(
        public bool $ok,
        public MemoryImportStatus|string $status,
        public string $code,
        public string $message,
    ) {}

    public static function fromArray(array $data): self
    {
        $status = Arr::string($data, 'status');

        return new self(
            ok: Arr::boolean($data, 'ok'),
            status: MemoryImportStatus::tryFrom($status) ?? $status,
            code: Arr::string($data, 'code'),
            message: Arr::string($data, 'message'),
        );
    }

    public function toArray(): array
    {
        return [
            'ok' => $this->ok,
            'status' => $this->status instanceof MemoryImportStatus ? $this->status->value : $this->status,
            'code' => $this->code,
            'message' => $this->message,
        ];
    }
}
