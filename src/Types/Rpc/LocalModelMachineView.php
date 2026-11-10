<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Detected files and resource requirements.
 *
 * @experimental
 */
readonly class LocalModelMachineView implements Arrayable
{
    /**
     * @param  array<LocalModelMachineRow>  $rows
     */
    public function __construct(
        public string $modelsDirectory,
        public array $rows,
        public ?string $llamaServerPath = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            modelsDirectory: $data['modelsDirectory'] ?? '',
            rows: array_map(
                static fn ($row) => $row instanceof LocalModelMachineRow
                    ? $row
                    : LocalModelMachineRow::fromArray(is_array($row) ? $row : []),
                $data['rows'] ?? [],
            ),
            llamaServerPath: $data['llamaServerPath'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            ...array_filter([
                'llamaServerPath' => $this->llamaServerPath,
            ], static fn ($value) => $value !== null),
            'modelsDirectory' => $this->modelsDirectory,
            'rows' => array_map(
                static fn (LocalModelMachineRow $row) => $row->toArray(),
                $this->rows,
            ),
        ];
    }
}
