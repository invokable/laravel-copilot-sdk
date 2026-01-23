<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Model policy state.
 */
readonly class ModelPolicy implements Arrayable
{
    public function __construct(
        /** Policy state: enabled, disabled, or unconfigured */
        public string $state,
        /** Terms */
        public string $terms,
    ) {}

    /**
     * Create from array.
     *
     * @param  array{state: string, terms: string}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            state: $data['state'],
            terms: $data['terms'],
        );
    }

    /**
     * Check if model is enabled.
     */
    public function isEnabled(): bool
    {
        return $this->state === 'enabled';
    }

    /**
     * Convert to array.
     *
     * @return array{state: string, terms: string}
     */
    public function toArray(): array
    {
        return [
            'state' => $this->state,
            'terms' => $this->terms,
        ];
    }
}
