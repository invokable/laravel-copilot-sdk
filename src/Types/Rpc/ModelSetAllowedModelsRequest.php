<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Host-supplied exact model selection IDs to allow for this running session. CAPI IDs are
 * intersected with repository `.github/allowed_models.txt` policy; provider-qualified IDs
 * remain exempt from repository-only policy but are restricted by this host list. Omit or
 * pass null to clear the host restriction; an explicit empty or disjoint list is rejected.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class ModelSetAllowedModelsRequest implements Arrayable
{
    /**
     * @param  ?array  $allowedModels  Exact model IDs to permit, or null to clear the host restriction.
     */
    public function __construct(
        public ?array $allowedModels = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            allowedModels: $data['allowedModels'] ?? null,
        );
    }

    public function toArray(): array
    {
        // allowedModels is always sent, even when null, so the runtime can distinguish
        // "clear the host restriction" from "no value specified".
        return [
            'allowedModels' => $this->allowedModels,
        ];
    }
}
