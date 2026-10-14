<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/** Normalized content-exclusion policies accepted by the runtime. */
readonly class ContentExclusionValidatePoliciesResult implements Arrayable
{
    /**
     * @param  list<mixed>  $policies
     */
    public function __construct(
        public array $policies,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            policies: $data['policies'] ?? [],
        );
    }

    public function toArray(): array
    {
        return [
            'policies' => $this->policies,
        ];
    }
}
