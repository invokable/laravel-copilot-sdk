<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/** @experimental */
readonly class HistoryImportToolCompletion implements Arrayable
{
    public function __construct(
        public string $toolCallId,
        public bool $success,
        public ?string $turnId = null,
        public HistoryImportToolOutput|array|null $result = null,
        public HistoryImportToolError|array|null $error = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            toolCallId: Arr::string($data, 'toolCallId'),
            success: Arr::boolean($data, 'success'),
            turnId: $data['turnId'] ?? null,
            result: isset($data['result']) ? ($data['result'] instanceof HistoryImportToolOutput
                ? $data['result'] : HistoryImportToolOutput::fromArray($data['result'])) : null,
            error: isset($data['error']) ? ($data['error'] instanceof HistoryImportToolError
                ? $data['error'] : HistoryImportToolError::fromArray($data['error'])) : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'toolCallId' => $this->toolCallId,
            'success' => $this->success,
            'turnId' => $this->turnId,
            'result' => $this->result === null ? null : ($this->result instanceof HistoryImportToolOutput
                ? $this->result : HistoryImportToolOutput::fromArray($this->result))->toArray(),
            'error' => $this->error === null ? null : ($this->error instanceof HistoryImportToolError
                ? $this->error : HistoryImportToolError::fromArray($this->error))->toArray(),
        ], fn ($value) => $value !== null);
    }
}
