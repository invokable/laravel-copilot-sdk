<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * MCP sampling approval stored for a permissions location.
 */
readonly class PermissionsLocationsAddToolApprovalDetailsMcpSampling implements Arrayable
{
    public string $kind;

    public function __construct(
        public string $serverName,
    ) {
        $this->kind = 'mcp-sampling';
    }

    public static function fromArray(array $data): self
    {
        return new self(serverName: Arr::string($data, 'serverName'));
    }

    public function toArray(): array
    {
        return [
            'kind' => $this->kind,
            'serverName' => $this->serverName,
        ];
    }
}
