<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Capability negotiation snapshot for MCP Apps.
 *
 * Experimental: this type is part of an experimental API and may change or be removed.
 */
readonly class MCPAppsDiagnoseCapability implements Arrayable
{
    /**
     * @param  bool  $advertised  Whether the runtime advertises `extensions.io.modelcontextprotocol/ui` to MCP servers.
     * @param  bool  $featureFlagEnabled  Whether the MCP_APPS feature flag (or COPILOT_MCP_APPS env override) is on.
     * @param  bool  $sessionHasMcpApps  Whether the session has the `mcp-apps` capability.
     */
    public function __construct(
        public bool $advertised,
        public bool $featureFlagEnabled,
        public bool $sessionHasMcpApps,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            advertised: $data['advertised'],
            featureFlagEnabled: $data['featureFlagEnabled'],
            sessionHasMcpApps: $data['sessionHasMcpApps'],
        );
    }

    public function toArray(): array
    {
        return [
            'advertised' => $this->advertised,
            'featureFlagEnabled' => $this->featureFlagEnabled,
            'sessionHasMcpApps' => $this->sessionHasMcpApps,
        ];
    }
}
