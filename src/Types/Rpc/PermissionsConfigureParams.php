<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Patch of permission policy fields to apply.
 */
readonly class PermissionsConfigureParams implements Arrayable
{
    public function __construct(
        public ?array $additionalContentExclusionPolicies = null,
        public ?bool $approveAllReadPermissionRequests = null,
        public ?bool $approveAllToolPermissionRequests = null,
        public ?array $paths = null,
        public ?array $rules = null,
        public ?array $urls = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            additionalContentExclusionPolicies: $data['additionalContentExclusionPolicies'] ?? null,
            approveAllReadPermissionRequests: $data['approveAllReadPermissionRequests'] ?? null,
            approveAllToolPermissionRequests: $data['approveAllToolPermissionRequests'] ?? null,
            paths: $data['paths'] ?? null,
            rules: $data['rules'] ?? null,
            urls: $data['urls'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'additionalContentExclusionPolicies' => $this->additionalContentExclusionPolicies,
            'approveAllReadPermissionRequests' => $this->approveAllReadPermissionRequests,
            'approveAllToolPermissionRequests' => $this->approveAllToolPermissionRequests,
            'paths' => $this->paths,
            'rules' => $this->rules,
            'urls' => $this->urls,
        ], fn ($value): bool => $value !== null);
    }
}
