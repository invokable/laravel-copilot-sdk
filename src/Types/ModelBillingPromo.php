<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Active server-driven promotion for a model, including its discount and expiry.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class ModelBillingPromo implements Arrayable
{
    /**
     * @param  string  $endsAt  UTC ISO 8601 timestamp marking when the promotion ends.
     * @param  ?float  $discountPercent  Percentage discount (0-100) applied while the promotion is active. May be fractional.
     * @param  ?string  $id  Stable identifier for the promotion campaign.
     * @param  ?string  $message  Human-readable promotion message.
     */
    public function __construct(
        public string $endsAt,
        public ?float $discountPercent = null,
        public ?string $id = null,
        public ?string $message = null,
    ) {}

    /**
     * Create from array.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            endsAt: Arr::string($data, 'endsAt', ''),
            discountPercent: isset($data['discountPercent']) ? (float) $data['discountPercent'] : null,
            id: $data['id'] ?? null,
            message: $data['message'] ?? null,
        );
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return array_filter([
            'endsAt' => $this->endsAt,
            'discountPercent' => $this->discountPercent,
            'id' => $this->id,
            'message' => $this->message,
        ], fn ($v) => $v !== null);
    }
}
