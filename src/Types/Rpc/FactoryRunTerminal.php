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
     * @param  FactoryPauseInfo|array|null  $pauseInfo  Pause initiator metadata, or null when the run did not pause.
     */
    public function __construct(
        public ?string $reason = null,
        public FactoryRunFailure|array|null $failure = null,
        public ?string $error = null,
        public ?string $resultPreview = null,
        public FactoryPauseInfo|array|null $pauseInfo = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $failure = $data['failure'] ?? null;
        $pauseInfo = $data['pauseInfo'] ?? null;

        return new self(
            reason: $data['reason'] ?? null,
            failure: $failure !== null
                ? ($failure instanceof FactoryRunFailure ? $failure : FactoryRunFailure::fromArray($failure))
                : null,
            error: $data['error'] ?? null,
            resultPreview: $data['resultPreview'] ?? null,
            pauseInfo: $pauseInfo !== null
                ? ($pauseInfo instanceof FactoryPauseInfo ? $pauseInfo : FactoryPauseInfo::fromArray($pauseInfo))
                : null,
        );
    }

    public function toArray(): array
    {
        $failure = $this->failure instanceof FactoryRunFailure ? $this->failure->toArray() : $this->failure;
        $pauseInfo = $this->pauseInfo instanceof FactoryPauseInfo ? $this->pauseInfo->toArray() : $this->pauseInfo;

        return array_filter([
            'reason' => $this->reason,
            'failure' => $failure,
            'error' => $this->error,
            'resultPreview' => $this->resultPreview,
            'pauseInfo' => $pauseInfo,
        ], fn ($v) => $v !== null);
    }
}
