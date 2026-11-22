<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/** @experimental */
readonly class SessionsImportResult implements Arrayable
{
    /**
     * @param  array<ImportDiagnostic>  $diagnostics
     */
    public function __construct(
        public bool $ok,
        public string $code,
        public string $message,
        public bool $dryRun,
        public SessionImportCounts $imported,
        public array $diagnostics,
        public ?string $externalId = null,
        public ?string $sessionId = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            ok: Arr::boolean($data, 'ok'),
            code: Arr::string($data, 'code'),
            message: Arr::string($data, 'message'),
            dryRun: Arr::boolean($data, 'dryRun'),
            imported: SessionImportCounts::fromArray(Arr::array($data, 'imported')),
            diagnostics: array_map(
                fn (array $diagnostic): ImportDiagnostic => ImportDiagnostic::fromArray($diagnostic),
                Arr::array($data, 'diagnostics'),
            ),
            externalId: $data['externalId'] ?? null,
            sessionId: $data['sessionId'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'ok' => $this->ok,
            'code' => $this->code,
            'message' => $this->message,
            'externalId' => $this->externalId,
            'sessionId' => $this->sessionId,
            'dryRun' => $this->dryRun,
            'imported' => $this->imported->toArray(),
            'diagnostics' => array_map(
                fn (ImportDiagnostic $diagnostic): array => $diagnostic->toArray(),
                $this->diagnostics,
            ),
        ], fn ($value) => $value !== null);
    }
}
