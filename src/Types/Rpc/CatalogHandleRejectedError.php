<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\CatalogHandleRejectionReason;
use Revolution\Copilot\Enums\CatalogHandleType;

/**
 * A presented handle was not accepted.
 *
 * @experimental
 */
readonly class CatalogHandleRejectedError implements Arrayable
{
    /** @var string */
    public string $kind;

    public function __construct(
        public CatalogHandleType $handleType,
        public CatalogHandleRejectionReason $reason,
        public string $message,
    ) {
        $this->kind = 'handle-rejected';
    }

    public static function fromArray(array $data): static
    {
        return new static(
            handleType: CatalogHandleType::from($data['handleType']),
            reason: CatalogHandleRejectionReason::from($data['reason']),
            message: $data['message'],
        );
    }

    public function toArray(): array
    {
        return [
            'kind' => $this->kind,
            'handleType' => $this->handleType->value,
            'reason' => $this->reason->value,
            'message' => $this->message,
        ];
    }
}
