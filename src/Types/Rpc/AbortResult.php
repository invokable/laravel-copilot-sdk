<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Result of aborting the current turn.
 */
readonly class AbortResult implements Arrayable
{
    /**
     * @param  bool  $success  Whether the abort completed successfully
     * @param  ?string  $error  Error message if the abort failed
     */
    public function __construct(
        public bool $success,
        public ?string $error = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            success: $data['success'],
            error: $data['error'] ?? null,
        );
    }

    public function toArray(): array
    {
        $result = [
            'success' => $this->success,
        ];

        if (! is_null($this->error)) {
            $result['error'] = $this->error;
        }

        return $result;
    }
}
