<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Local validation diagnostics for a recorded trajectory.
 *
 * @experimental
 */
readonly class TaskAnalyticsTrajectoryValidation implements Arrayable
{
    /**
     * @param  array<int, string>  $errors
     */
    public function __construct(
        public bool $valid,
        public int $inputBytes,
        public array $errors,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            valid: Arr::boolean($data, 'valid'),
            inputBytes: Arr::integer($data, 'inputBytes'),
            errors: array_values(Arr::array($data, 'errors', [])),
        );
    }

    public function toArray(): array
    {
        return [
            'valid' => $this->valid,
            'inputBytes' => $this->inputBytes,
            'errors' => $this->errors,
        ];
    }
}
