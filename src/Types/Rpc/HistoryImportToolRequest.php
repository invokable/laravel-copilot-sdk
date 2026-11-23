<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\HistoryImportArgumentOmitted;

/** @experimental */
readonly class HistoryImportToolRequest implements Arrayable
{
    public function __construct(public string $name, public string $toolCallId, public mixed $arguments = HistoryImportArgumentOmitted::Value) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: Arr::string($data, 'name'),
            toolCallId: Arr::string($data, 'toolCallId'),
            arguments: array_key_exists('arguments', $data) ? $data['arguments'] : HistoryImportArgumentOmitted::Value,
        );
    }

    public function toArray(): array
    {
        $data = [
            'name' => $this->name,
            'toolCallId' => $this->toolCallId,
        ];
        if ($this->arguments !== HistoryImportArgumentOmitted::Value) {
            $data['arguments'] = $this->arguments;
        }

        return $data;
    }
}
