<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\CatalogNetworkFailureReason;

/**
 * The runtime could not reach the catalog authority or retrieve a card.
 *
 * @experimental
 */
readonly class CatalogNetworkFailureError implements Arrayable
{
    public string $kind;

    public function __construct(
        public CatalogNetworkFailureReason $reason,
        public ?int $statusCode,
        public string $message,
    ) {
        $this->kind = 'network-failure';
    }

    public static function fromArray(array $data): static
    {
        return new static(
            reason: CatalogNetworkFailureReason::from($data['reason']),
            statusCode: isset($data['statusCode']) ? (int) $data['statusCode'] : null,
            message: $data['message'],
        );
    }

    public function toArray(): array
    {
        $arr = [
            'kind' => $this->kind,
            'reason' => $this->reason->value,
            'message' => $this->message,
        ];
        if ($this->statusCode !== null) {
            $arr['statusCode'] = $this->statusCode;
        }

        return $arr;
    }
}
