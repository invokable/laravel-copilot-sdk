<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Tool result object.
 */
readonly class ToolResultObject implements Arrayable
{
    public function __construct(
        /**
         * Text result for the LLM.
         */
        public string $textResultForLlm,
        /**
         * Result type: "success", "failure", "rejected", or "denied".
         */
        public string $resultType = 'success',
        /**
         * Binary results for the LLM.
         */
        public ?array $binaryResultsForLlm = null,
        /**
         * Error message, if any.
         */
        public ?string $error = null,
        /**
         * Session log.
         */
        public ?string $sessionLog = null,
        /**
         * Tool telemetry data.
         */
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
