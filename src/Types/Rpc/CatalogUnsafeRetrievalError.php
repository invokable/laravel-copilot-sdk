<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\CatalogUnsafeRetrievalReason;

/**
 * @experimental
 */
readonly class CatalogUnsafeRetrievalError implements Arrayable
{
    public string $kind;

    public function __construct(
        public CatalogUnsafeRetrievalReason $reason,
        public string $message,
    ) {
        $this->kind = 'unsafe-retrieval';
    }

    public static function fromArray(array $data): static
    {
        return new static(
            reason: CatalogUnsafeRetrievalReason::from($data['reason']),
            message: $data['message'],
        );
    }

    public function toArray(): array
    {
        return [
            'kind' => $this->kind,
            'reason' => $this->reason->value,
            'message' => $this->message,
        ];
    }
}
