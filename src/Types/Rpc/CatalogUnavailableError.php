<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\CatalogUnavailableReason;

/**
 * @experimental
 */
readonly class CatalogUnavailableError implements Arrayable
{
    public string $kind;

    public function __construct(
        public CatalogUnavailableReason $reason,
        public string $message,
    ) {
        $this->kind = 'unavailable';
    }

    public static function fromArray(array $data): static
    {
        return new static(
            reason: CatalogUnavailableReason::from($data['reason']),
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
