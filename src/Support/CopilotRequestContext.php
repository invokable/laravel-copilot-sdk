<?php

declare(strict_types=1);

namespace Revolution\Copilot\Support;

/**
 * Context for an intercepted outbound model-layer request.
 *
 * @experimental
 */
final class CopilotRequestContext
{
    /**
     * @param  array<string, list<string>>  $headers
     */
    public function __construct(
        public readonly string $requestId,
        public readonly ?string $sessionId,
        public readonly ?string $agentId,
        public readonly ?string $agentInvocationId,
        public readonly ?string $parentAgentId,
        public readonly ?string $interactionType,
        public readonly string $transport,
        public string $url,
        public array $headers,
        public readonly CancellationToken $signal,
    ) {}
}
