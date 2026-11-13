<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/** Request to read or claim the startup model enablement prompt. */
readonly class ModelStartupAdmissionRequest implements Arrayable
{
    public function __construct(
        public ?bool $claimEnablement = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(claimEnablement: $data['claimEnablement'] ?? null);
    }

    public function toArray(): array
    {
        return array_filter([
            'claimEnablement' => $this->claimEnablement,
        ], fn ($value) => $value !== null);
    }
}
