<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\HistoryImportAttachmentType;

/** @experimental */
readonly class HistoryImportAttachment implements Arrayable
{
    public function __construct(
        public HistoryImportAttachmentType|string $type,
        public string $mimeType,
        public string $displayName,
        public string $data,
    ) {}

    public static function fromArray(array $data): self
    {
        $type = $data['type'];

        return new self(
            type: is_string($type) ? HistoryImportAttachmentType::tryFrom($type) ?? $type : $type,
            mimeType: Arr::string($data, 'mimeType'),
            displayName: Arr::string($data, 'displayName'),
            data: Arr::string($data, 'data'),
        );
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type instanceof HistoryImportAttachmentType ? $this->type->value : $this->type,
            'mimeType' => $this->mimeType,
            'displayName' => $this->displayName,
            'data' => $this->data,
        ];
    }
}
