<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Custom tool approval stored for a permissions location.
 */
readonly class PermissionsLocationsAddToolApprovalDetailsCustomTool implements Arrayable
{
    public string $kind;

    public function __construct(
        public string $toolName,
    ) {
        $this->kind = 'custom-tool';
    }

    public static function fromArray(array $data): self
    {
        return new self(toolName: Arr::string($data, 'toolName'));
    }

    public function toArray(): array
    {
        return [
            'kind' => $this->kind,
            'toolName' => $this->toolName,
        ];
    }
}
