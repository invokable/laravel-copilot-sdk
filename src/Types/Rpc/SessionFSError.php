<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\SessionFSErrorCode;

/**
 * Describes a filesystem error.
 */
readonly class SessionFSError implements Arrayable
{
    /**
     * @param  SessionFSErrorCode  $code  Error classification
     * @param  ?string  $message  Free-form detail about the error, for logging/diagnostics
     */
    public function __construct(
        public SessionFSErrorCode $code,
        public ?string $message = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            code: SessionFSErrorCode::from($data['code']),
            message: $data['message'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'code' => $this->code->value,
            'message' => $this->message,
        ], fn ($v) => $v !== null);
    }
}
