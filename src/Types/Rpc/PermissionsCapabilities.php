<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Permission modes available under the session's feature and managed permission policy.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class PermissionsCapabilities implements Arrayable
{
    /**
     * @param  bool  $assistedApproval  Whether LLM-assisted approval is enabled for this session.
     * @param  bool  $bypassPermissions  Whether managed policy currently permits unconditional approval. Stays true while managed policy is still resolving.
     */
    public function __construct(
        public bool $assistedApproval,
        public bool $bypassPermissions,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            assistedApproval: Arr::boolean($data, 'assistedApproval'),
            bypassPermissions: Arr::boolean($data, 'bypassPermissions'),
        );
    }

    public function toArray(): array
    {
        return [
            'assistedApproval' => $this->assistedApproval,
            'bypassPermissions' => $this->bypassPermissions,
        ];
    }
}
