<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\HistoryImportArgumentOmitted;

/** @experimental */
readonly class HistoryImportToolInvocation implements Arrayable
{
    public function __construct(
        public string $toolCallId,
        public string $toolName,
        public mixed $arguments = HistoryImportArgumentOmitted::Value,
        public ?string $turnId = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            toolCallId: Arr::string($data, 'toolCallId'),
            toolName: Arr::string($data, 'toolName'),
            arguments: array_key_exists('arguments', $data) ? $data['arguments'] : HistoryImportArgumentOmitted::Value,
            turnId: $data['turnId'] ?? null,
        );
    }

    public function toArray(): array
    {
        $data = [
            'toolCallId' => $this->toolCallId,
            'toolName' => $this->toolName,
        ];
        if ($this->arguments !== HistoryImportArgumentOmitted::Value) {
            $data['arguments'] = $this->arguments;
        }
        if ($this->turnId !== null) {
            $data['turnId'] = $this->turnId;
        }

        return $data;
    }
}
