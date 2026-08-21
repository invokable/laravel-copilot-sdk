<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\CatalogUnavailableTransportReason;

/**
 * @experimental
 */
readonly class CatalogUnavailableTransportError implements Arrayable
{
    /** @var string */
    public string $kind;

    public function __construct(
        public CatalogUnavailableTransportReason $reason,
        public string $message,
    ) {
        $this->kind = 'unavailable-transport';
    }

    public static function fromArray(array $data): static
    {
        return new static(
            reason: CatalogUnavailableTransportReason::from($data['reason']),
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
