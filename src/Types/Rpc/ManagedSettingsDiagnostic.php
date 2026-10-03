<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\ManagedSettingsDiagnosticSeverity;

/** One validation finding for a managed-settings document. */
readonly class ManagedSettingsDiagnostic implements Arrayable
{
    public function __construct(
        public string $path,
        public ManagedSettingsDiagnosticSeverity|string $severity,
        public string $message,
    ) {}

    public static function fromArray(array $data): self
    {
        $severity = $data['severity'] ?? '';

        return new self(
            path: $data['path'] ?? '',
            severity: is_string($severity) ? (ManagedSettingsDiagnosticSeverity::tryFrom($severity) ?? $severity) : $severity,
            message: $data['message'] ?? '',
        );
    }

    public function toArray(): array
    {
        return [
            'path' => $this->path,
            'severity' => $this->severity instanceof ManagedSettingsDiagnosticSeverity ? $this->severity->value : $this->severity,
            'message' => $this->message,
        ];
    }
}
