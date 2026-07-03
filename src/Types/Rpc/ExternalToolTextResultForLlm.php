<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Expanded external tool result payload.
 *
 * Use this instead of a plain string when you need to provide structured
 * metadata alongside the LLM-facing text: execution outcome, error details,
 * session log, telemetry, and/or rich content blocks.
 */
readonly class ExternalToolTextResultForLlm implements Arrayable
{
    /**
     * @param  string  $textResultForLlm  Text result returned to the model
     * @param  ?string  $resultType  Execution outcome classification. Normalized to 'success' (or 'failure' when error is present) when missing or unrecognized.
     * @param  ?string  $error  Optional error message for failed executions
     * @param  ?string  $sessionLog  Detailed log content for timeline display
     * @param  ?array  $toolTelemetry  Optional tool-specific telemetry
     * @param  ?array  $contents  Structured content blocks from the tool
     * @param  ?string[]  $toolReferences  Tool references returned by a tool-search override: names of deferred tools to surface to the model.
     */
    public function __construct(
        public string $textResultForLlm,
        public ?string $resultType = null,
        public ?string $error = null,
        public ?string $sessionLog = null,
        public ?array $toolTelemetry = null,
        public ?array $contents = null,
        public ?array $toolReferences = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            textResultForLlm: Arr::string($data, 'textResultForLlm'),
            resultType: $data['resultType'] ?? null,
            error: $data['error'] ?? null,
            sessionLog: $data['sessionLog'] ?? null,
            toolTelemetry: $data['toolTelemetry'] ?? null,
            contents: $data['contents'] ?? null,
            toolReferences: $data['toolReferences'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'textResultForLlm' => $this->textResultForLlm,
            'resultType' => $this->resultType,
            'error' => $this->error,
            'sessionLog' => $this->sessionLog,
            'toolTelemetry' => $this->toolTelemetry,
            'contents' => $this->contents,
            'toolReferences' => $this->toolReferences,
        ], fn ($v) => $v !== null);
    }
}
