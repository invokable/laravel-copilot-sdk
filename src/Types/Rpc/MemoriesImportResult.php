<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/** @experimental */
readonly class MemoriesImportResult implements Arrayable
{
    /**
     * @param  array<ImportDiagnostic>  $diagnostics
     * @param  array<MemoryImportItemResult>  $records
     */
    public function __construct(
        public bool $ok,
        public string $code,
        public string $message,
        public bool $dryRun,
        public MemoryImportCounts $imported,
        public array $diagnostics,
        public array $records,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            ok: Arr::boolean($data, 'ok'),
            code: Arr::string($data, 'code'),
            message: Arr::string($data, 'message'),
            dryRun: Arr::boolean($data, 'dryRun'),
            imported: MemoryImportCounts::fromArray(Arr::array($data, 'imported')),
            diagnostics: array_map(
                fn (array $diagnostic): ImportDiagnostic => ImportDiagnostic::fromArray($diagnostic),
                Arr::array($data, 'diagnostics'),
            ),
            records: array_map(
                fn (array $record): MemoryImportItemResult => MemoryImportItemResult::fromArray($record),
                Arr::array($data, 'records'),
            ),
        );
    }

    public function toArray(): array
    {
        return [
            'ok' => $this->ok,
            'code' => $this->code,
            'message' => $this->message,
            'dryRun' => $this->dryRun,
            'imported' => $this->imported->toArray(),
            'diagnostics' => array_map(
                fn (ImportDiagnostic $diagnostic): array => $diagnostic->toArray(),
                $this->diagnostics,
            ),
            'records' => array_map(
                fn (MemoryImportItemResult $record): array => $record->toArray(),
                $this->records,
            ),
        ];
    }
}
