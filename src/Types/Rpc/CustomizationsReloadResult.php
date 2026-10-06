<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/** Per-subsystem outcomes and diagnostics from customization reload. */
readonly class CustomizationsReloadResult implements Arrayable
{
    /**
     * @param  string[]  $errors
     * @param  CustomizationReloadOutcome[]  $outcomes
     * @param  string[]  $warnings
     */
    public function __construct(
        public array $errors = [],
        public array $outcomes = [],
        public array $warnings = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            errors: $data['errors'] ?? [],
            outcomes: array_map(
                fn (array $outcome) => CustomizationReloadOutcome::fromArray($outcome),
                $data['outcomes'] ?? [],
            ),
            warnings: $data['warnings'] ?? [],
        );
    }

    public function toArray(): array
    {
        return [
            'errors' => $this->errors,
            'outcomes' => array_map(fn (CustomizationReloadOutcome $outcome) => $outcome->toArray(), $this->outcomes),
            'warnings' => $this->warnings,
        ];
    }
}
