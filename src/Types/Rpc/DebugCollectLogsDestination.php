<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Destination for a collected debug log bundle.
 *
 * @experimental
 */
readonly class DebugCollectLogsDestination implements Arrayable
{
    private function __construct(
        public string $kind,
        public ?string $outputPath = null,
        public ?bool $noOverwrite = null,
        public ?string $outputDirectory = null,
    ) {}

    public static function archive(string $outputPath, ?bool $noOverwrite = null): self
    {
        return new self(
            kind: 'archive',
            outputPath: $outputPath,
            noOverwrite: $noOverwrite,
        );
    }

    public static function directory(string $outputDirectory): self
    {
        return new self(
            kind: 'directory',
            outputDirectory: $outputDirectory,
        );
    }

    public static function fromArray(array $data): self
    {
        return match ($data['kind']) {
            'archive' => self::archive(
                outputPath: $data['outputPath'],
                noOverwrite: $data['noOverwrite'] ?? null,
            ),
            'directory' => self::directory(
                outputDirectory: $data['outputDirectory'],
            ),
        };
    }

    public function toArray(): array
    {
        if ($this->kind === 'archive') {
            $data = [
                'kind' => $this->kind,
                'outputPath' => $this->outputPath,
            ];

            if ($this->noOverwrite !== null) {
                $data['noOverwrite'] = $this->noOverwrite;
            }

            return $data;
        }

        return [
            'kind' => $this->kind,
            'outputDirectory' => $this->outputDirectory,
        ];
    }
}
