<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Request to switch to auto mode after an eligible rate limit.
 */
readonly class AutoModeSwitchRequest implements Arrayable
{
    /**
     * @param  ?string  $errorCode  The rate-limit error code that triggered the request.
     * @param  ?int  $retryAfterSeconds  Seconds until the rate limit resets, when known.
     * @param  ?AutoModeRecoveryContext  $autoModeRecovery  Context for recovering an active request.
     */
    public function __construct(
        public ?string $errorCode = null,
        public ?int $retryAfterSeconds = null,
        public ?AutoModeRecoveryContext $autoModeRecovery = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            errorCode: $data['errorCode'] ?? null,
            retryAfterSeconds: isset($data['retryAfterSeconds']) ? (int) $data['retryAfterSeconds'] : null,
            autoModeRecovery: isset($data['autoModeRecovery'])
                ? ($data['autoModeRecovery'] instanceof AutoModeRecoveryContext
                    ? $data['autoModeRecovery']
                    : AutoModeRecoveryContext::fromArray($data['autoModeRecovery']))
                : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'errorCode' => $this->errorCode,
            'retryAfterSeconds' => $this->retryAfterSeconds,
            'autoModeRecovery' => $this->autoModeRecovery?->toArray(),
        ], fn ($v) => $v !== null);
    }
}
