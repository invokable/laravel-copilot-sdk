<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\CatalogNotInstallableReason;

/**
 * @experimental
 */
readonly class CatalogNotInstallableError implements Arrayable
{
    /** @var string */
    public string $kind;

    public function __construct(
        public CatalogNotInstallableReason $reason,
        public string $message,
    ) {
        $this->kind = 'not-installable';
    }

    public static function fromArray(array $data): static
    {
        return new static(
            reason: CatalogNotInstallableReason::from($data['reason']),
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
