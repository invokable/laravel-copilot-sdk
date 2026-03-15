<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Configuration for OpenTelemetry instrumentation of the Copilot CLI process.
 *
 * When provided to the client options, these settings are translated into
 * environment variables on the spawned CLI server process.
 */
readonly class TelemetryConfig implements Arrayable
{
    /**
     * @param  ?string  $otlpEndpoint  OTLP HTTP endpoint URL for trace/metric export. Sets OTEL_EXPORTER_OTLP_ENDPOINT.
     * @param  ?string  $filePath  File path for JSON-lines trace output. Sets COPILOT_OTEL_FILE_EXPORTER_PATH.
     * @param  ?string  $exporterType  Exporter backend type: "otlp-http" or "file". Sets COPILOT_OTEL_EXPORTER_TYPE.
     * @param  ?string  $sourceName  Instrumentation scope name. Sets COPILOT_OTEL_SOURCE_NAME.
     * @param  ?bool  $captureContent  Whether to capture message content (prompts, responses).
     *                                 Sets OTEL_INSTRUMENTATION_GENAI_CAPTURE_MESSAGE_CONTENT.
     */
    public function __construct(
        public ?string $otlpEndpoint = null,
        public ?string $filePath = null,
        public ?string $exporterType = null,
        public ?string $sourceName = null,
        public ?bool $captureContent = null,
    ) {}

    /**
     * Create from array.
     */
    public static function fromArray(array $data): static
    {
        return new static(
            otlpEndpoint: $data['otlpEndpoint'] ?? null,
            filePath: $data['filePath'] ?? null,
            exporterType: $data['exporterType'] ?? null,
            sourceName: $data['sourceName'] ?? null,
            captureContent: isset($data['captureContent']) ? (bool) $data['captureContent'] : null,
        );
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return array_filter([
            'otlpEndpoint' => $this->otlpEndpoint,
            'filePath' => $this->filePath,
            'exporterType' => $this->exporterType,
            'sourceName' => $this->sourceName,
            'captureContent' => $this->captureContent,
        ], fn ($v) => $v !== null);
    }

    /**
     * Build the environment variables to set on the CLI process.
     *
     * @return array<string, string>
     */
    public function toEnv(): array
    {
        $env = ['COPILOT_OTEL_ENABLED' => 'true'];

        if ($this->otlpEndpoint !== null) {
            $env['OTEL_EXPORTER_OTLP_ENDPOINT'] = $this->otlpEndpoint;
        }
        if ($this->filePath !== null) {
            $env['COPILOT_OTEL_FILE_EXPORTER_PATH'] = $this->filePath;
        }
        if ($this->exporterType !== null) {
            $env['COPILOT_OTEL_EXPORTER_TYPE'] = $this->exporterType;
        }
        if ($this->sourceName !== null) {
            $env['COPILOT_OTEL_SOURCE_NAME'] = $this->sourceName;
        }
        if ($this->captureContent !== null) {
            $env['OTEL_INSTRUMENTATION_GENAI_CAPTURE_MESSAGE_CONTENT'] = $this->captureContent ? 'true' : 'false';
        }

        return $env;
    }
}
