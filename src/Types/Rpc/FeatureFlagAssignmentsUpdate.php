<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Replacement or clear operation for delivered feature-flag assignments.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class FeatureFlagAssignmentsUpdate implements Arrayable
{
    public function __construct(
        public mixed $response,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            response: $data['response'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'response' => $this->response,
        ];
    }
}
