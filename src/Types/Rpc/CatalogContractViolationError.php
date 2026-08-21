<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\CatalogContractViolationReason;

/**
 * @experimental
 */
readonly class CatalogContractViolationError implements Arrayable
{
    public string $kind;

    public function __construct(
        public CatalogContractViolationReason $reason,
        public string $message,
    ) {
        $this->kind = 'contract-violation';
    }

    public static function fromArray(array $data): static
    {
        return new static(
            reason: CatalogContractViolationReason::from($data['reason']),
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
