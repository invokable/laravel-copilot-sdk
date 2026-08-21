<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\CatalogMalformedCardReason;
use Revolution\Copilot\Enums\CatalogMediaType;

/**
 * A card could not be parsed or did not satisfy its declared media type's schema.
 *
 * @experimental
 */
readonly class CatalogMalformedCardError implements Arrayable
{
    /** @var string */
    public string $kind;

    public function __construct(
        public CatalogMalformedCardReason $reason,
        public ?CatalogMediaType $mediaType,
        public string $message,
    ) {
        $this->kind = 'malformed-card';
    }

    public static function fromArray(array $data): static
    {
        return new static(
            reason: CatalogMalformedCardReason::from($data['reason']),
            mediaType: isset($data['mediaType']) ? CatalogMediaType::from($data['mediaType']) : null,
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
        if ($this->mediaType !== null) {
            $arr['mediaType'] = $this->mediaType->value;
        }

        return $arr;
    }
}
