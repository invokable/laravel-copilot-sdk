<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Configuration for handling large tool outputs.
 *
 * When a tool produces output exceeding the configured size, the output is
 * written to a temp file and a reference is returned to the model instead of
 * the full payload.
 */
readonly class LargeToolOutputConfig implements Arrayable
{
    /**
     * @param  ?bool  $enabled  Whether large output handling is enabled. Defaults to true.
     * @param  ?int  $maxSizeBytes  Maximum size in bytes before output is written to a temp file. Defaults to 51200.
     * @param  ?string  $outputDirectory  Directory to write temp files to. Defaults to the OS temp directory.
     */
    public function __construct(
        public ?bool $enabled = null,
        public ?int $maxSizeBytes = null,
        public ?string $outputDirectory = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            enabled: $data['enabled'] ?? null,
            maxSizeBytes: $data['maxSizeBytes'] ?? null,
            outputDirectory: $data['outputDirectory'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'enabled' => $this->enabled,
            'maxSizeBytes' => $this->maxSizeBytes,
            'outputDirectory' => $this->outputDirectory,
        ], fn ($v) => $v !== null);
    }
}
