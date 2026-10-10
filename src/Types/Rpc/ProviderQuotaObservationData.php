<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/** Data for a provider quota observation session event. */
readonly class ProviderQuotaObservationData implements Arrayable
{
    public function __construct(
        public ProviderQuotaState|array $observation,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            observation: isset($data['observation'])
                ? ($data['observation'] instanceof ProviderQuotaState
                    ? $data['observation']
                    : ProviderQuotaState::fromArray($data['observation']))
                : ProviderQuotaState::fromArray([]),
        );
    }

    public function toArray(): array
    {
        return [
            'observation' => $this->observation instanceof ProviderQuotaState
                ? $this->observation->toArray()
                : $this->observation,
        ];
    }
}
