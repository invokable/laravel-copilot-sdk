<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Terminal outcome details for a completed, cancelled, halted, or errored factory run.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class FactoryRunTerminal implements Arrayable
{
    /**
     * @param  ?string  $reason  Reason for a halted or cancelled run.
     * @param  FactoryRunFailure|array|null  $failure  Machine-readable failure details for an errored run.
     * @param  ?string  $error  Error message for an errored run.
     * @param  ?string  $resultPreview  Short preview of the completed factory result.
     */
    public function __construct(
        public ?string $reason = null,
        public FactoryRunFailure|array|null $failure = null,
        public ?string $error = null,
        public ?string $resultPreview = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $failure = $data['failure'] ?? null;

        return new self(
            reason: $data['reason'] ?? null,
            failure: $failure !== null
                ? ($failure instanceof FactoryRunFailure ? $failure : FactoryRunFailure::fromArray($failure))
                : null,
            error: $data['error'] ?? null,
            resultPreview: $data['resultPreview'] ?? null,
        );
    }

    public function toArray(): array
    {
        $failure = $this->failure instanceof FactoryRunFailure ? $this->failure->toArray() : $this->failure;

        return array_filter([
            'reason' => $this->reason,
            'failure' => $failure,
            'error' => $this->error,
            'resultPreview' => $this->resultPreview,
        ], fn ($v) => $v !== null);
    }
}
