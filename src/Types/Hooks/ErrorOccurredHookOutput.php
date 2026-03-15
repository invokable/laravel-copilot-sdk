<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Hooks;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Output for error-occurred hook.
 */
readonly class ErrorOccurredHookOutput implements Arrayable
{
    /**
     * @param  ?bool  $suppressOutput  Whether to suppress output
     * @param  ?string  $errorHandling  Error handling strategy: "retry", "skip", or "abort"
     * @param  ?int  $retryCount  Number of retries to attempt
     * @param  ?string  $userNotification  User notification message
     */
    public function __construct(
        public ?bool $suppressOutput = null,
        public ?string $errorHandling = null,
        public ?int $retryCount = null,
        public ?string $userNotification = null,
    ) {}

    /**
     * Create from array data.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            suppressOutput: $data['suppressOutput'] ?? null,
            errorHandling: $data['errorHandling'] ?? null,
            retryCount: $data['retryCount'] ?? null,
            userNotification: $data['userNotification'] ?? null,
        );
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return array_filter([
            'suppressOutput' => $this->suppressOutput,
            'errorHandling' => $this->errorHandling,
            'retryCount' => $this->retryCount,
            'userNotification' => $this->userNotification,
        ], fn ($value) => $value !== null);
    }
}
