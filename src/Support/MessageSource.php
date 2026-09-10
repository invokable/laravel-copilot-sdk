<?php

declare(strict_types=1);

namespace Revolution\Copilot\Support;

/**
 * Helpers for building the `source` value for `Session::send()` and related methods.
 *
 * Message provenance, independent of delivery mode. Value is one of "user", "system",
 * or "agent-{id}" for messages originating from an identified agent.
 */
class MessageSource
{
    public static function user(): string
    {
        return 'user';
    }

    public static function system(): string
    {
        return 'system';
    }

    /**
     * Build a source string for a message originating from an identified agent.
     * The agent ID is opaque and sent unchanged after the `agent-` prefix.
     */
    public static function agent(string $agentId): string
    {
        return 'agent-'.$agentId;
    }
}
