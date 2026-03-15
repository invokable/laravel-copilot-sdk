<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Tool result object.
 */
readonly class ToolResultObject implements Arrayable
{
    /**
     * @param  string  $textResultForLlm  Text result for the LLM
     * @param  string  $resultType  Result type: "success", "failure", "rejected", or "denied"
     * @param  ?array  $binaryResultsForLlm  Binary results for the LLM
     * @param  ?string  $error  Error message, if any
     * @param  ?string  $sessionLog  Session log
     * @param  ?array  $toolTelemetry  Tool telemetry data
     */
    public function __construct(
        public string $textResultForLlm,
        public string $resultType = 'success',
        public ?array $binaryResultsForLlm = null,
        public ?string $error = null,
        public ?string $sessionLog = null,
        public ?array $toolTelemetry = null,
    ) {}

    /**
     * Create from array data.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            textResultForLlm: $data['textResultForLlm'] ?? '',
            resultType: $data['resultType'] ?? 'success',
            binaryResultsForLlm: $data['binaryResultsForLlm'] ?? null,
            error: $data['error'] ?? null,
            sessionLog: $data['sessionLog'] ?? null,
            toolTelemetry: $data['toolTelemetry'] ?? null,
        );
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return array_filter([
            'textResultForLlm' => $this->textResultForLlm,
            'resultType' => $this->resultType,
            'binaryResultsForLlm' => $this->binaryResultsForLlm,
            'error' => $this->error,
            'sessionLog' => $this->sessionLog,
            'toolTelemetry' => $this->toolTelemetry,
        ], fn ($value) => $value !== null);
    }
}
