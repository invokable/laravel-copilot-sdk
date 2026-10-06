<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\SessionFSErrorCode;

/**
 * Structured filesystem error returned by a SessionFs provider callback.
 */
readonly class SessionFsError implements Arrayable
{
    public function __construct(
        public SessionFSErrorCode|string $code,
        public string $message,
        public ?bool $writeChanged = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $code = $data['code'] ?? SessionFSErrorCode::UNKNOWN->value;
        $code = is_string($code) ? (SessionFSErrorCode::tryFrom($code) ?? $code) : SessionFSErrorCode::UNKNOWN;

        return new self(
            code: $code,
            message: $data['message'] ?? '',
            writeChanged: isset($data['writeChanged']) ? (bool) $data['writeChanged'] : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'code' => $this->code instanceof SessionFSErrorCode ? $this->code->value : $this->code,
            'message' => $this->message,
            'writeChanged' => $this->writeChanged,
        ], static fn ($value) => $value !== null);
    }
}
