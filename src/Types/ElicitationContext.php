<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Context for an elicitation handler invocation, combining the request data
 * with session context. Mirrors the single-argument pattern of CommandContext.
 *
 * Contains the data extracted from an `elicitation.requested` session event.
 */
readonly class ElicitationContext implements Arrayable
{
    /**
     * @param  string  $sessionId  Identifier of the session that triggered the elicitation request
     * @param  string  $message  Message describing what information is needed from the user
     * @param  ?array  $requestedSchema  JSON Schema describing the form fields to present
     * @param  ?string  $mode  Elicitation mode: "form" for structured input, "url" for browser redirect
     * @param  ?string  $elicitationSource  The source that initiated the request (e.g. MCP server name)
     * @param  ?string  $url  URL to open in the user's browser (url mode only)
     */
    public function __construct(
        public string $sessionId,
        public string $message,
        public ?array $requestedSchema = null,
        public ?string $mode = null,
        public ?string $elicitationSource = null,
        public ?string $url = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            sessionId: $data['sessionId'] ?? '',
            message: $data['message'] ?? '',
            requestedSchema: $data['requestedSchema'] ?? null,
            mode: $data['mode'] ?? null,
            elicitationSource: $data['elicitationSource'] ?? null,
            url: $data['url'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'sessionId' => $this->sessionId,
            'message' => $this->message,
            'requestedSchema' => $this->requestedSchema,
            'mode' => $this->mode,
            'elicitationSource' => $this->elicitationSource,
            'url' => $this->url,
        ], fn ($v) => $v !== null);
    }
}
