<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * MCP tool approval stored for a permissions location.
 */
readonly class PermissionsLocationsAddToolApprovalDetailsMcp implements Arrayable
{
    public string $kind;

    public function __construct(
        public string $serverName,
        public ?string $toolName,
    ) {
        $this->kind = 'mcp';
    }

    public static function fromArray(array $data): self
    {
        return new self(
            serverName: Arr::string($data, 'serverName'),
            toolName: isset($data['toolName']) ? Arr::string($data, 'toolName') : null,
        );
    }

    public function toArray(): array
    {
        return [
            'kind' => $this->kind,
            'serverName' => $this->serverName,
            'toolName' => $this->toolName,
        ];
    }
}
