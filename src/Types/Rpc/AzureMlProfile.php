<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Validated local runtime requirements for an Azure ML model.
 *
 * @experimental
 */
readonly class AzureMlProfile implements Arrayable
{
    public function __construct(
        public string $displayName,
        public int $maxPromptTokens,
        public int $maxContextWindowTokens,
        public int $maxOutputTokens,
        public bool $supportsToolCalls,
        public mixed $serveOptions,
        public ?int $downloadBytes = null,
        public ?int $peakDiskBytes = null,
        public ?int $recommendedVramBytes = null,
        public ?AzureMlLicense $license = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            displayName: $data['displayName'],
            maxPromptTokens: (int) $data['maxPromptTokens'],
            maxContextWindowTokens: (int) $data['maxContextWindowTokens'],
            maxOutputTokens: (int) $data['maxOutputTokens'],
            supportsToolCalls: $data['supportsToolCalls'],
            serveOptions: $data['serveOptions'],
            downloadBytes: isset($data['downloadBytes']) ? (int) $data['downloadBytes'] : null,
            peakDiskBytes: isset($data['peakDiskBytes']) ? (int) $data['peakDiskBytes'] : null,
            recommendedVramBytes: isset($data['recommendedVramBytes']) ? (int) $data['recommendedVramBytes'] : null,
            license: isset($data['license']) ? AzureMlLicense::fromArray($data['license']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'displayName' => $this->displayName,
            'maxPromptTokens' => $this->maxPromptTokens,
            'maxContextWindowTokens' => $this->maxContextWindowTokens,
            'maxOutputTokens' => $this->maxOutputTokens,
            'supportsToolCalls' => $this->supportsToolCalls,
            'serveOptions' => $this->serveOptions,
            ...array_filter([
                'downloadBytes' => $this->downloadBytes,
                'peakDiskBytes' => $this->peakDiskBytes,
                'recommendedVramBytes' => $this->recommendedVramBytes,
                'license' => $this->license?->toArray(),
            ], fn ($value) => $value !== null),
        ];
    }
}
