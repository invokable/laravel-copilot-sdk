<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\PluginInstallationMcpTransport;

/**
 * One MCP server declared by a reviewed plugin.
 *
 * @experimental
 */
readonly class PluginInstallationMcpServerReview implements Arrayable
{
    public function __construct(
        public string $name,
        public PluginInstallationMcpTransport $transport,
        public ?string $host = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            name: $data['name'],
            transport: PluginInstallationMcpTransport::from($data['transport']),
            host: $data['host'] ?? null,
        );
    }

    public function toArray(): array
    {
        $data = [
            'name' => $this->name,
            'transport' => $this->transport->value,
        ];

        if ($this->host !== null) {
            $data['host'] = $this->host;
        }

        return $data;
    }
}
