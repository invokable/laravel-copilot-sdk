<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Normalised identity of the MCP server a plan targets.
 *
 * @experimental
 */
readonly class McpPlanResourceIdentity implements Arrayable
{
    public function __construct(
        public string $canonicalName,
        public string $serverName,
        public ?string $version = null,
        public ?string $registryId = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            canonicalName: $data['canonicalName'],
            serverName: $data['serverName'],
            version: $data['version'] ?? null,
            registryId: $data['registryId'] ?? null,
        );
    }

    public function toArray(): array
    {
        $arr = [
            'canonicalName' => $this->canonicalName,
            'serverName' => $this->serverName,
        ];
        if ($this->version !== null) {
            $arr['version'] = $this->version;
        }
        if ($this->registryId !== null) {
            $arr['registryId'] = $this->registryId;
        }

        return $arr;
    }
}
