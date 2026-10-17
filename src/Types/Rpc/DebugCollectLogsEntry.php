<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\DebugCollectLogsEntryKind;
use Revolution\Copilot\Enums\DebugCollectLogsRedaction;

/**
 * An additional file or directory to include in a debug log bundle.
 *
 * @experimental
 */
readonly class DebugCollectLogsEntry implements Arrayable
{
    public function __construct(
        public DebugCollectLogsEntryKind|string $kind,
        public string $path,
        public string $bundlePath,
        public DebugCollectLogsRedaction|string|null $redaction = null,
        public ?bool $required = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $kind = $data['kind'];
        $redaction = $data['redaction'] ?? null;

        return new self(
            kind: is_string($kind) ? DebugCollectLogsEntryKind::tryFrom($kind) ?? $kind : $kind,
            path: $data['path'],
            bundlePath: $data['bundlePath'],
            redaction: is_string($redaction) ? DebugCollectLogsRedaction::tryFrom($redaction) ?? $redaction : $redaction,
            required: $data['required'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'kind' => $this->kind instanceof DebugCollectLogsEntryKind ? $this->kind->value : $this->kind,
            'path' => $this->path,
            'bundlePath' => $this->bundlePath,
            'redaction' => $this->redaction instanceof DebugCollectLogsRedaction ? $this->redaction->value : $this->redaction,
            'required' => $this->required,
        ], fn ($value) => $value !== null);
    }
}
