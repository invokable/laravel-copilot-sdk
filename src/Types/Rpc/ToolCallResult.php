<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Structured tool call result to send back to the LLM.
 */
readonly class ToolCallResult implements Arrayable
{
    /**
     * @param  string  $textResultForLlm  Text result to send back to the LLM
     * @param  ?string  $resultType  Type of the tool result
     * @param  ?string  $error  Error message if the tool call failed
     * @param  ?array  $toolTelemetry  Telemetry data from tool execution
     */
    public function __construct(
        public string $textResultForLlm,
        public ?string $resultType = null,
        public ?string $error = null,
        public ?array $toolTelemetry = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            textResultForLlm: $data['textResultForLlm'],
            resultType: $data['resultType'] ?? null,
            error: $data['error'] ?? null,
            toolTelemetry: $data['toolTelemetry'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'textResultForLlm' => $this->textResultForLlm,
            'resultType' => $this->resultType,
            'error' => $this->error,
            'toolTelemetry' => $this->toolTelemetry,
        ], fn ($v) => $v !== null);
    }
}
