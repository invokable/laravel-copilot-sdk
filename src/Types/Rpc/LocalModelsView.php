<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\LocalModelOperation;

/**
 * Connection-owned local model state.
 *
 * @experimental
 */
readonly class LocalModelsView implements Arrayable
{
    /**
     * @param  array<string, LocalModelDownloadProgress>  $downloads
     * @param  array<string, LocalModelOperation|string>  $operations
     * @param  array<string, LocalModelNotice>  $notices
     */
    public function __construct(
        public bool $enabled,
        public LocalModelMachineView $machine,
        public array $downloads,
        public array $operations,
        public array $notices,
        public bool $catalogLoading,
        public bool $hideDevModels,
        public bool $hideWinMlModels,
        public string $prerequisiteNotice,
        public ?string $catalogError = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $machine = $data['machine'] ?? [];
        $downloads = $data['downloads'] ?? [];
        $operations = $data['operations'] ?? [];
        $notices = $data['notices'] ?? [];

        return new self(
            enabled: $data['enabled'] ?? false,
            machine: $machine instanceof LocalModelMachineView
                ? $machine
                : LocalModelMachineView::fromArray(is_array($machine) ? $machine : []),
            downloads: array_map(
                static fn ($progress) => $progress instanceof LocalModelDownloadProgress
                    ? $progress
                    : LocalModelDownloadProgress::fromArray(is_array($progress) ? $progress : []),
                is_array($downloads) ? $downloads : [],
            ),
            operations: array_map(
                static fn ($operation) => is_string($operation)
                    ? (LocalModelOperation::tryFrom($operation) ?? $operation)
                    : '',
                is_array($operations) ? $operations : [],
            ),
            notices: array_map(
                static fn ($notice) => $notice instanceof LocalModelNotice
                    ? $notice
                    : LocalModelNotice::fromArray(is_array($notice) ? $notice : []),
                is_array($notices) ? $notices : [],
            ),
            catalogLoading: $data['catalogLoading'] ?? false,
            catalogError: $data['catalogError'] ?? null,
            hideDevModels: $data['hideDevModels'] ?? false,
            hideWinMlModels: $data['hideWinMlModels'] ?? false,
            prerequisiteNotice: $data['prerequisiteNotice'] ?? '',
        );
    }

    public function toArray(): array
    {
        return [
            'enabled' => $this->enabled,
            'machine' => $this->machine->toArray(),
            'downloads' => array_map(
                static fn (LocalModelDownloadProgress $progress) => $progress->toArray(),
                $this->downloads,
            ),
            'operations' => array_map(
                static fn (LocalModelOperation|string $operation) => $operation instanceof LocalModelOperation
                    ? $operation->value
                    : $operation,
                $this->operations,
            ),
            'notices' => array_map(
                static fn (LocalModelNotice $notice) => $notice->toArray(),
                $this->notices,
            ),
            'catalogLoading' => $this->catalogLoading,
            ...array_filter([
                'catalogError' => $this->catalogError,
            ], static fn ($value) => $value !== null),
            'hideDevModels' => $this->hideDevModels,
            'hideWinMlModels' => $this->hideWinMlModels,
            'prerequisiteNotice' => $this->prerequisiteNotice,
        ];
    }
}
