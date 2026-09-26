<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Result of a session mode change.
 *
 * @experimental
 */
readonly class ModeSetResult implements Arrayable
{
    public function __construct(
        public string $status,
        public bool $modelChanged,
        public ?bool $modeApplied = null,
        public ?array $confirmation = null,
        public ?string $warning = null,
        public ?string $message = null,
        public ?array $deprecationWarnings = null,
        public ?bool $deferImplementation = null,
        public ?bool $armInteractiveContinuation = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            status: Arr::string($data, 'status', ''),
            modelChanged: Arr::boolean($data, 'modelChanged', false),
            modeApplied: isset($data['modeApplied']) ? Arr::boolean($data, 'modeApplied') : null,
            confirmation: $data['confirmation'] ?? null,
            warning: $data['warning'] ?? null,
            message: $data['message'] ?? null,
            deprecationWarnings: $data['deprecationWarnings'] ?? null,
            deferImplementation: isset($data['deferImplementation']) ? Arr::boolean($data, 'deferImplementation') : null,
            armInteractiveContinuation: isset($data['armInteractiveContinuation']) ? Arr::boolean($data, 'armInteractiveContinuation') : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'status' => $this->status,
            'modelChanged' => $this->modelChanged,
            'modeApplied' => $this->modeApplied,
            'confirmation' => $this->confirmation,
            'warning' => $this->warning,
            'message' => $this->message,
            'deprecationWarnings' => $this->deprecationWarnings,
            'deferImplementation' => $this->deferImplementation,
            'armInteractiveContinuation' => $this->armInteractiveContinuation,
        ], fn ($value) => $value !== null);
    }
}
